<?php

session_start();

header(
    'Content-Type: application/json; charset=utf-8'
);

require_once __DIR__ . '/../../config/database.php';
require_once __DIR__ . '/../../services/PromocionService.php';


function responder(
    bool $success,
    string $message,
    array $extra = []
): void {

    echo json_encode(
        array_merge(
            [
                'success' => $success,
                'message' => $message
            ],
            $extra
        ),
        JSON_UNESCAPED_UNICODE
    );

    exit;
}


/* ============================================================
   SESIÓN
============================================================ */

if (!isset($_SESSION['usuario_id'])) {

    http_response_code(401);

    responder(
        false,
        'Sesión expirada.'
    );
}


/* ============================================================
   INPUT
============================================================ */

$items =
    json_decode(
        $_POST['items'] ?? '[]',
        true
    );


if (
    !is_array($items)
    ||
    count($items) === 0
) {

    responder(
        false,
        'Agregue al menos un producto.'
    );
}


/* ============================================================
   CONSOLIDAR PRODUCTOS REPETIDOS
============================================================ */

/*
 * Aunque el frontend normalmente no manda duplicados,
 * no debemos confiar en eso.
 *
 * Ejemplo:
 *
 * Pilsen x2
 * Pilsen x3
 *
 * se convierte en:
 *
 * Pilsen x5
 */

$itemsConsolidados = [];


foreach ($items as $item) {

    $productoId =
        (int) (
            $item['producto_id']
            ?? 0
        );


    $cantidad =
        (float) (
            $item['cantidad']
            ?? 0
        );


    if (
        $productoId <= 0
        ||
        $cantidad <= 0
    ) {

        responder(
            false,
            'Producto o cantidad inválidos.'
        );
    }


    if (
        !isset(
            $itemsConsolidados[
                $productoId
            ]
        )
    ) {

        $itemsConsolidados[
            $productoId
        ] = 0;
    }


    $itemsConsolidados[
        $productoId
    ] +=
        $cantidad;
}


$items = [];


foreach (
    $itemsConsolidados
    as $productoId => $cantidad
) {

    $items[] = [

        'producto_id' =>
            (int) $productoId,

        'cantidad' =>
            (float) $cantidad
    ];
}


/* ============================================================
   SERVICIOS / ACUMULADORES
============================================================ */

$promocionService =
    new PromocionService($conn);


$detalle = [];

$subtotal = 0;

$totalDescuento = 0;

$total = 0;


/*
 * Envases requeridos por tipo.
 */
$envases = [];


/*
 * Stock requerido REAL por producto físico.
 *
 * Aquí acumularemos:
 *
 * producto individual
 * +
 * componentes de combos
 *
 * Ejemplo:
 *
 * Coca Cola individual x2
 * Combo x3, cada combo usa Coca Cola x1
 *
 * stock requerido Coca Cola = 5
 */
$stockRequerido = [];


/* ============================================================
   FUNCIÓN PARA ACUMULAR STOCK
============================================================ */

function agregarStockRequerido(
    array &$stockRequerido,
    int $productoId,
    string $nombre,
    float $cantidad
): void {

    if (!isset($stockRequerido[$productoId])) {

        $stockRequerido[$productoId] = [

            'producto_id' =>
                $productoId,

            'nombre' =>
                $nombre,

            'cantidad' =>
                0
        ];
    }


    $stockRequerido[
        $productoId
    ]['cantidad'] +=
        $cantidad;
}


/* ============================================================
   FUNCIÓN PARA ACUMULAR ENVASES
============================================================ */

function agregarEnvases(
    array &$envases,
    int $tipoEnvaseId,
    string $nombre,
    float $cantidad
): void {

    if ($cantidad <= 0) {
        return;
    }


    if (!isset($envases[$tipoEnvaseId])) {

        $envases[$tipoEnvaseId] = [

            'tipo_envase_id' =>
                $tipoEnvaseId,

            'nombre' =>
                $nombre,

            'cantidad_requerida' =>
                0
        ];
    }


    $envases[
        $tipoEnvaseId
    ]['cantidad_requerida'] +=
        $cantidad;
}


/* ============================================================
   PROCESAR PRODUCTOS DE LA VENTA
============================================================ */

foreach ($items as $item) {

    $productoId =
        (int)
        $item['producto_id'];


    $cantidad =
        (float)
        $item['cantidad'];


    /* ========================================================
       PRODUCTO
    ======================================================== */

    $sqlProducto = "
        SELECT
            p.id,
            p.nombre,
            p.presentacion,

            p.precio_regular,
            p.precio_venta,
            p.costo_referencia,

            p.maneja_stock,
            p.stock_actual,

            p.tipo_producto,

            p.controla_envase,
            p.tipo_envase_id,
            p.envases_por_unidad,

            te.nombre
                AS tipo_envase_nombre

        FROM productos p

        LEFT JOIN tipos_envase te
            ON te.id = p.tipo_envase_id

        WHERE
            p.id = ?
            AND p.activo = 1

        LIMIT 1
    ";


    $stmtProducto =
        $conn->prepare(
            $sqlProducto
        );


    $stmtProducto->bind_param(
        'i',
        $productoId
    );


    $stmtProducto->execute();


    $producto =
        $stmtProducto
            ->get_result()
            ->fetch_assoc();


    if (!$producto) {

        responder(
            false,
            'Uno de los productos ya no está disponible.'
        );
    }


    /* ========================================================
       STOCK SIMPLE
    ======================================================== */

    if (
        $producto['tipo_producto']
        === 'SIMPLE'
    ) {

        if (
            (int)
            $producto['maneja_stock']
            !== 1
        ) {

            responder(
                false,
                $producto['nombre']
                . ' no maneja inventario.'
            );
        }


        agregarStockRequerido(
            $stockRequerido,
            (int) $producto['id'],
            $producto['nombre'],
            $cantidad
        );


        /*
         * Envases de producto simple.
         */
        if (
            (int)
            $producto['controla_envase']
            === 1
            &&
            !empty(
                $producto['tipo_envase_id']
            )
        ) {

            $cantidadEnvases =
                $cantidad
                *
                (float)
                $producto[
                    'envases_por_unidad'
                ];


            agregarEnvases(
                $envases,
                (int)
                $producto['tipo_envase_id'],
                $producto[
                    'tipo_envase_nombre'
                ],
                $cantidadEnvases
            );
        }

    }


    /* ========================================================
       STOCK DE COMBO
    ======================================================== */

    elseif (
        $producto['tipo_producto']
        === 'COMBO'
    ) {

        /*
         * Obtenemos todos los componentes.
         */
        $sqlComponentes = "
            SELECT
                cb.id AS combo_id,

                cc.producto_id,
                cc.cantidad
                    AS cantidad_por_combo,

                pc.nombre,

                pc.stock_actual,
                pc.maneja_stock,
                pc.activo,

                pc.controla_envase,
                pc.tipo_envase_id,
                pc.envases_por_unidad,

                te.nombre
                    AS tipo_envase_nombre

            FROM combos cb

            INNER JOIN combo_componentes cc
                ON cc.combo_id = cb.id

            INNER JOIN productos pc
                ON pc.id = cc.producto_id

            LEFT JOIN tipos_envase te
                ON te.id = pc.tipo_envase_id

            WHERE
                cb.producto_id = ?
                AND cb.activo = 1

            ORDER BY
                cc.producto_id ASC
        ";


        $stmtComponentes =
            $conn->prepare(
                $sqlComponentes
            );


        $stmtComponentes->bind_param(
            'i',
            $productoId
        );


        $stmtComponentes->execute();


        $resultComponentes =
            $stmtComponentes
                ->get_result();


        if (
            $resultComponentes->num_rows
            === 0
        ) {

            responder(
                false,
                'El combo '
                . $producto['nombre']
                . ' todavía no tiene componentes configurados.'
            );
        }


        while (
            $componente =
                $resultComponentes
                    ->fetch_assoc()
        ) {

            if (
                (int)
                $componente['activo']
                !== 1
            ) {

                responder(
                    false,
                    $componente['nombre']
                    . ' se encuentra desactivado y forma parte de '
                    . $producto['nombre']
                    . '.'
                );
            }


            if (
                (int)
                $componente['maneja_stock']
                !== 1
            ) {

                responder(
                    false,
                    $componente['nombre']
                    . ' no maneja inventario y forma parte de '
                    . $producto['nombre']
                    . '.'
                );
            }


            $cantidadComponente =
                (float)
                $componente[
                    'cantidad_por_combo'
                ]
                *
                $cantidad;


            /*
             * Acumulamos stock.
             */
            agregarStockRequerido(
                $stockRequerido,
                (int)
                $componente['producto_id'],
                $componente['nombre'],
                $cantidadComponente
            );


            /*
             * Si el componente requiere envases,
             * el combo también los genera.
             *
             * Ejemplo:
             *
             * Combo cervecero:
             * 6 Pilsen botella
             *
             * => 6 envases.
             */
            if (
                (int)
                $componente[
                    'controla_envase'
                ]
                === 1
                &&
                !empty(
                    $componente[
                        'tipo_envase_id'
                    ]
                )
            ) {

                $cantidadEnvases =
                    $cantidadComponente
                    *
                    (float)
                    $componente[
                        'envases_por_unidad'
                    ];


                agregarEnvases(
                    $envases,
                    (int)
                    $componente[
                        'tipo_envase_id'
                    ],
                    $componente[
                        'tipo_envase_nombre'
                    ],
                    $cantidadEnvases
                );
            }
        }

    } else {

        responder(
            false,
            'Tipo de producto inválido.'
        );
    }


    /* ========================================================
       PROMOCIÓN / PRECIO
    ======================================================== */

    $calculo =
        $promocionService
            ->calcularProducto(
                $producto,
                $cantidad
            );


    $subtotal +=
        $calculo[
            'subtotal_base'
        ];


    $totalDescuento +=
        $calculo[
            'descuento_promocion'
        ];


    $total +=
        $calculo[
            'subtotal_final'
        ];


    /* ========================================================
       DETALLE PARA FRONTEND
    ======================================================== */

    $detalle[] = [

        'producto_id' =>
            (int)
            $producto['id'],

        'nombre' =>
            $producto['nombre'],

        'presentacion' =>
            $producto['presentacion'],

        'tipo_producto' =>
            $producto[
                'tipo_producto'
            ],

        'cantidad' =>
            $cantidad,

        'precio_regular' =>
            $calculo[
                'precio_regular'
            ],

        'precio_venta_base' =>
            $calculo[
                'precio_venta_base'
            ],

        'subtotal_base' =>
            $calculo[
                'subtotal_base'
            ],

        'descuento_promocion' =>
            $calculo[
                'descuento_promocion'
            ],

        'subtotal_final' =>
            $calculo[
                'subtotal_final'
            ],

        'promocion_id' =>
            $calculo[
                'promocion_id'
            ],

        'promocion_nombre' =>
            $calculo[
                'promocion_nombre'
            ],

        'detalle_precio' =>
            $calculo[
                'detalle_precio'
            ]
    ];
}


/* ============================================================
   VALIDAR STOCK TOTAL DE LA VENTA
============================================================ */

/*
 * Esta validación se hace DESPUÉS de recorrer toda la venta.
 *
 * Es importante porque un mismo producto físico puede
 * utilizarse desde varios lugares.
 *
 * Ejemplo:
 *
 * Coca Cola individual x2
 * +
 * Combo El Clásico x3
 *
 * = 5 Coca Cola necesarias.
 */

foreach (
    $stockRequerido
    as $requerimiento
) {

    $productoStockId =
        (int)
        $requerimiento[
            'producto_id'
        ];


    $cantidadNecesaria =
        (float)
        $requerimiento[
            'cantidad'
        ];


    $stmtStock =
        $conn->prepare(
            "
                SELECT
                    nombre,
                    stock_actual,
                    activo,
                    maneja_stock

                FROM productos

                WHERE id = ?

                LIMIT 1
            "
        );


    $stmtStock->bind_param(
        'i',
        $productoStockId
    );


    $stmtStock->execute();


    $productoStock =
        $stmtStock
            ->get_result()
            ->fetch_assoc();


    if (!$productoStock) {

        responder(
            false,
            'Uno de los productos necesarios ya no existe.'
        );
    }


    if (
        (int)
        $productoStock['activo']
        !== 1
    ) {

        responder(
            false,
            $productoStock['nombre']
            . ' se encuentra desactivado.'
        );
    }


    if (
        (int)
        $productoStock[
            'maneja_stock'
        ]
        !== 1
    ) {

        responder(
            false,
            $productoStock['nombre']
            . ' no maneja inventario.'
        );
    }


    $stockActual =
        (float)
        $productoStock[
            'stock_actual'
        ];


    if (
        $stockActual
        <
        $cantidadNecesaria
    ) {

        responder(
            false,
            'Stock insuficiente de '
            . $productoStock['nombre']
            . '. Necesario: '
            . number_format(
                $cantidadNecesaria,
                3
            )
            . '. Disponible: '
            . number_format(
                $stockActual,
                3
            )
            . '.'
        );
    }
}


/* ============================================================
   NORMALIZAR VALORES
============================================================ */

$subtotal =
    round(
        $subtotal,
        2
    );


$totalDescuento =
    round(
        $totalDescuento,
        2
    );


$total =
    round(
        $total,
        2
    );


/*
 * Redondeamos cantidades de envases.
 */
foreach ($envases as &$envase) {

    $envase[
        'cantidad_requerida'
    ] =
        round(
            (float)
            $envase[
                'cantidad_requerida'
            ],
            3
        );
}

unset($envase);


/* ============================================================
   RESPONSE
============================================================ */

responder(
    true,
    'Venta calculada.',
    [
        'detalle' =>
            $detalle,

        'subtotal' =>
            $subtotal,

        'descuento_promociones' =>
            $totalDescuento,

        'total' =>
            $total,

        'envases' =>
            array_values(
                $envases
            )
    ]
);