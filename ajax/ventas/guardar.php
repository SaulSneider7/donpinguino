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
   DESCONTAR STOCK DE PRODUCTO SIMPLE
============================================================ */

function descontarStockSimple(
    mysqli $conn,
    int $productoId,
    float $cantidad,
    int $ventaId,
    int $usuarioId
): float {

    /*
     * Volvemos a obtener el stock EN EL MOMENTO
     * de descontarlo.
     *
     * Esto evita problemas cuando:
     *
     * - el mismo producto también forma parte de un combo;
     * - existen varios movimientos del mismo producto;
     * - dos operaciones intentan modificar stock.
     */
    $sql = "
        SELECT
            id,
            nombre,
            stock_actual,
            costo_referencia,
            maneja_stock,
            tipo_producto

        FROM productos

        WHERE id = ?

        LIMIT 1

        FOR UPDATE
    ";


    $stmt =
        $conn->prepare($sql);


    $stmt->bind_param(
        'i',
        $productoId
    );


    $stmt->execute();


    $producto =
        $stmt
            ->get_result()
            ->fetch_assoc();


    if (!$producto) {

        throw new Exception(
            'Producto no encontrado.'
        );
    }


    if (
        $producto['tipo_producto'] !== 'SIMPLE'
        ||
        (int) $producto['maneja_stock'] !== 1
    ) {

        throw new Exception(
            'El producto '
            . $producto['nombre']
            . ' no maneja stock simple.'
        );
    }


    $stockAnterior =
        (float) $producto['stock_actual'];


    if ($stockAnterior < $cantidad) {

        throw new Exception(
            'Stock insuficiente para '
            . $producto['nombre']
            . '. Disponible: '
            . number_format(
                $stockAnterior,
                3
            )
            . '.'
        );
    }


    $stockNuevo =
        $stockAnterior
        - $cantidad;


    $costoUnitario =
        (float) $producto['costo_referencia'];


    /* ========================================================
       UPDATE STOCK
    ======================================================== */

    $stmtStock =
        $conn->prepare(
            "
                UPDATE productos

                SET stock_actual = ?

                WHERE id = ?

                LIMIT 1
            "
        );


    $stmtStock->bind_param(
        'di',
        $stockNuevo,
        $productoId
    );


    if (!$stmtStock->execute()) {

        throw new Exception(
            'No se pudo actualizar el stock de '
            . $producto['nombre']
            . '.'
        );
    }


    /* ========================================================
       KARDEX
    ======================================================== */

    $cantidadMovimiento =
        -$cantidad;


    $referenciaTipo =
        'VENTA';


    $descripcion =
        'Venta #'
        . $ventaId;


    $stmtMovimiento =
        $conn->prepare(
            "
                INSERT INTO movimientos_stock (
                    producto_id,
                    usuario_id,

                    tipo_movimiento,

                    referencia_tipo,
                    referencia_id,

                    cantidad,

                    stock_anterior,
                    stock_nuevo,

                    costo_unitario,

                    descripcion,
                    fecha
                )
                VALUES (
                    ?,
                    ?,

                    'VENTA',

                    ?,
                    ?,

                    ?,

                    ?,
                    ?,

                    ?,

                    ?,
                    NOW()
                )
            "
        );


    $stmtMovimiento->bind_param(
        'iisidddds',

        $productoId,
        $usuarioId,

        $referenciaTipo,
        $ventaId,

        $cantidadMovimiento,

        $stockAnterior,
        $stockNuevo,

        $costoUnitario,

        $descripcion
    );


    if (!$stmtMovimiento->execute()) {

        throw new Exception(
            'No se pudo registrar el Kardex de '
            . $producto['nombre']
            . '.'
        );
    }


    return $costoUnitario;
}


/* ============================================================
   DESCONTAR COMPONENTES DE COMBO
============================================================ */

function descontarStockCombo(
    mysqli $conn,
    int $productoComboId,
    float $cantidadCombos,
    int $ventaId,
    int $usuarioId
): float {

    /* ========================================================
       OBTENER COMBO
    ======================================================== */

    $sqlCombo = "
        SELECT
            cb.id AS combo_id

        FROM combos cb

        WHERE
            cb.producto_id = ?
            AND cb.activo = 1

        LIMIT 1
    ";


    $stmtCombo =
        $conn->prepare($sqlCombo);


    $stmtCombo->bind_param(
        'i',
        $productoComboId
    );


    $stmtCombo->execute();


    $combo =
        $stmtCombo
            ->get_result()
            ->fetch_assoc();


    if (!$combo) {

        throw new Exception(
            'El combo no está configurado.'
        );
    }


    $comboId =
        (int) $combo['combo_id'];


    /* ========================================================
       OBTENER COMPONENTES EN ORDEN
    ======================================================== */

    $stmtIds =
        $conn->prepare(
            "
                SELECT producto_id

                FROM combo_componentes

                WHERE combo_id = ?

                ORDER BY producto_id ASC
            "
        );


    $stmtIds->bind_param(
        'i',
        $comboId
    );


    $stmtIds->execute();


    $resultIds =
        $stmtIds->get_result();


    $ids = [];


    while (
        $row = $resultIds->fetch_assoc()
    ) {

        $ids[] =
            (int) $row['producto_id'];
    }


    if (count($ids) === 0) {

        throw new Exception(
            'El combo no tiene componentes configurados.'
        );
    }


    $costoUnitarioCombo = 0;


    /* ========================================================
       DESCONTAR CADA COMPONENTE
    ======================================================== */

    foreach ($ids as $componenteId) {

        $stmt =
            $conn->prepare(
                "
                    SELECT
                        p.id,
                        p.nombre,
                        p.stock_actual,
                        p.costo_referencia,
                        p.activo,
                        p.maneja_stock,

                        cc.cantidad
                            AS cantidad_por_combo

                    FROM combo_componentes cc

                    INNER JOIN productos p
                        ON p.id = cc.producto_id

                    WHERE
                        cc.combo_id = ?
                        AND cc.producto_id = ?

                    LIMIT 1

                    FOR UPDATE
                "
            );


        $stmt->bind_param(
            'ii',
            $comboId,
            $componenteId
        );


        $stmt->execute();


        $componente =
            $stmt
                ->get_result()
                ->fetch_assoc();


        if (!$componente) {

            throw new Exception(
                'Componente de combo inválido.'
            );
        }


        if (
            (int) $componente['activo'] !== 1
            ||
            (int) $componente['maneja_stock'] !== 1
        ) {

            throw new Exception(
                $componente['nombre']
                . ' no está disponible para formar el combo.'
            );
        }


        $cantidadPorCombo =
            (float)
            $componente['cantidad_por_combo'];


        $cantidadDescontar =
            $cantidadPorCombo
            *
            $cantidadCombos;


        $stockAnterior =
            (float)
            $componente['stock_actual'];


        if (
            $stockAnterior
            <
            $cantidadDescontar
        ) {

            throw new Exception(
                'Stock insuficiente de '
                . $componente['nombre']
                . ' para vender el combo. '
                . 'Disponible: '
                . number_format(
                    $stockAnterior,
                    3
                )
                . '.'
            );
        }


        $stockNuevo =
            $stockAnterior
            -
            $cantidadDescontar;


        $costoUnitario =
            (float)
            $componente['costo_referencia'];


        /*
         * Costo de UNA unidad del combo.
         */
        $costoUnitarioCombo +=
            $costoUnitario
            *
            $cantidadPorCombo;


        /* ====================================================
           UPDATE STOCK
        ==================================================== */

        $stmtUpdate =
            $conn->prepare(
                "
                    UPDATE productos

                    SET stock_actual = ?

                    WHERE id = ?

                    LIMIT 1
                "
            );


        $stmtUpdate->bind_param(
            'di',

            $stockNuevo,
            $componenteId
        );


        if (!$stmtUpdate->execute()) {

            throw new Exception(
                'No se pudo descontar stock de '
                . $componente['nombre']
                . '.'
            );
        }


        /* ====================================================
           KARDEX
        ==================================================== */

        $cantidadMovimiento =
            -$cantidadDescontar;


        $referenciaTipo =
            'VENTA';


        $descripcion =
            'Venta #'
            . $ventaId
            . ' - componente de combo';


        $stmtMovimiento =
            $conn->prepare(
                "
                    INSERT INTO movimientos_stock (
                        producto_id,
                        usuario_id,

                        tipo_movimiento,

                        referencia_tipo,
                        referencia_id,

                        cantidad,

                        stock_anterior,
                        stock_nuevo,

                        costo_unitario,

                        descripcion,
                        fecha
                    )
                    VALUES (
                        ?,
                        ?,

                        'VENTA',

                        ?,
                        ?,

                        ?,

                        ?,
                        ?,

                        ?,

                        ?,
                        NOW()
                    )
                "
            );


        $stmtMovimiento->bind_param(
            'iisidddds',

            $componenteId,
            $usuarioId,

            $referenciaTipo,
            $ventaId,

            $cantidadMovimiento,

            $stockAnterior,
            $stockNuevo,

            $costoUnitario,

            $descripcion
        );


        if (
            !$stmtMovimiento->execute()
        ) {

            throw new Exception(
                'No se pudo registrar el Kardex de '
                . $componente['nombre']
                . '.'
            );
        }
    }


    return round(
        $costoUnitarioCombo,
        2
    );
}


/* ============================================================
   AGREGAR ENVASES CALCULADOS
============================================================ */

function agregarEnvasesCalculados(
    array &$envasesCalculados,
    int $tipoEnvaseId,
    float $cantidad
): void {

    if (
        $tipoEnvaseId <= 0
        ||
        $cantidad <= 0
    ) {
        return;
    }


    if (
        !isset(
            $envasesCalculados[
                $tipoEnvaseId
            ]
        )
    ) {

        $envasesCalculados[
            $tipoEnvaseId
        ] = 0;
    }


    $envasesCalculados[
        $tipoEnvaseId
    ] +=
        $cantidad;
}


/* ============================================================
   CALCULAR ENVASES COMBO
============================================================ */

function calcularEnvasesCombo(
    mysqli $conn,
    int $productoComboId,
    float $cantidadCombos,
    array &$envasesCalculados
): void {

    $sql = "
        SELECT
            pc.id,
            pc.nombre,

            pc.controla_envase,
            pc.tipo_envase_id,
            pc.envases_por_unidad,

            cc.cantidad
                AS cantidad_por_combo

        FROM combos cb

        INNER JOIN combo_componentes cc
            ON cc.combo_id = cb.id

        INNER JOIN productos pc
            ON pc.id = cc.producto_id

        WHERE
            cb.producto_id = ?
            AND cb.activo = 1
    ";


    $stmt =
        $conn->prepare($sql);


    $stmt->bind_param(
        'i',
        $productoComboId
    );


    $stmt->execute();


    $result =
        $stmt->get_result();


    if ($result->num_rows === 0) {

        throw new Exception(
            'El combo no tiene componentes configurados.'
        );
    }


    while (
        $componente =
            $result->fetch_assoc()
    ) {

        if (
            (int)
            $componente['controla_envase']
            !== 1
        ) {
            continue;
        }


        if (
            empty(
                $componente[
                    'tipo_envase_id'
                ]
            )
        ) {
            continue;
        }


        /*
         * Ejemplo:
         *
         * Combo x2
         *
         * contiene:
         * Pilsen x6
         *
         * cantidad física:
         * 2 × 6 = 12 Pilsen
         */
        $cantidadProducto =
            $cantidadCombos
            *
            (float)
            $componente[
                'cantidad_por_combo'
            ];


        /*
         * Si cada Pilsen requiere un envase:
         *
         * 12 × 1 = 12 envases
         */
        $cantidadEnvases =
            $cantidadProducto
            *
            (float)
            $componente[
                'envases_por_unidad'
            ];


        agregarEnvasesCalculados(
            $envasesCalculados,
            (int)
            $componente[
                'tipo_envase_id'
            ],
            $cantidadEnvases
        );
    }
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


$usuarioId =
    (int) $_SESSION['usuario_id'];


/* ============================================================
   INPUT
============================================================ */

$clienteId =
    isset($_POST['cliente_id'])
    &&
    $_POST['cliente_id'] !== ''
        ? (int) $_POST['cliente_id']
        : null;


$tipoPago =
    $_POST['tipo_pago']
    ?? 'COMPLETO';


$metodoPago =
    $_POST['metodo_pago']
    ?? 'YAPE';


$montoPagadoEnviado =
    (float) (
        $_POST['monto_pagado']
        ?? 0
    );


$items =
    json_decode(
        $_POST['items']
        ?? '[]',
        true
    );


$envasesEnviados =
    json_decode(
        $_POST['envases']
        ?? '[]',
        true
    );


/* ============================================================
   VALIDACIONES BÁSICAS
============================================================ */

if (
    !is_array($items)
    ||
    count($items) === 0
) {

    responder(
        false,
        'La venta no tiene productos.'
    );
}


if (
    !in_array(
        $tipoPago,
        [
            'COMPLETO',
            'PARCIAL',
            'PENDIENTE'
        ],
        true
    )
) {

    responder(
        false,
        'Tipo de pago inválido.'
    );
}


if (
    !in_array(
        $metodoPago,
        [
            'EFECTIVO',
            'YAPE',
            'PLIN',
            'OTRO'
        ],
        true
    )
) {

    responder(
        false,
        'Método de pago inválido.'
    );
}


/*
 * Consolidamos productos repetidos.
 *
 * Si por manipulación del frontend llegan:
 *
 * Pilsen 2
 * Pilsen 3
 *
 * el backend trabajará con:
 *
 * Pilsen 5
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


$promocionService =
    new PromocionService($conn);


/* ============================================================
   TRANSACCIÓN
============================================================ */

try {

    $conn->begin_transaction();


    $detalleCalculado = [];

    $envasesCalculados = [];

    $subtotalVenta = 0;

    $descuentoPromociones = 0;

    $totalVenta = 0;


    /* ========================================================
       LEER PRODUCTOS Y CALCULAR PRECIOS
    ======================================================== */

    foreach ($items as $item) {

        $productoId =
            (int) $item['producto_id'];


        $cantidad =
            (float) $item['cantidad'];


        $sql = "
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
                p.envases_por_unidad

            FROM productos p

            WHERE
                p.id = ?
                AND p.activo = 1

            LIMIT 1

            FOR UPDATE
        ";


        $stmt =
            $conn->prepare($sql);


        $stmt->bind_param(
            'i',
            $productoId
        );


        $stmt->execute();


        $producto =
            $stmt
                ->get_result()
                ->fetch_assoc();


        if (!$producto) {

            throw new Exception(
                'Uno de los productos ya no está disponible.'
            );
        }


        /*
         * Aquí solamente hacemos una validación rápida
         * para productos simples.
         *
         * La comprobación definitiva se vuelve a realizar
         * cuando se descuenta el stock.
         */
        if (
            $producto['tipo_producto']
            === 'SIMPLE'
            &&
            (int)
            $producto['maneja_stock']
            === 1
            &&
            (float)
            $producto['stock_actual']
            <
            $cantidad
        ) {

            throw new Exception(
                'Stock insuficiente para '
                . $producto['nombre']
                . '. Disponible: '
                . $producto['stock_actual']
            );
        }


        /* ====================================================
           PROMOCIÓN
        ==================================================== */

        $calculo =
            $promocionService
                ->calcularProducto(
                    $producto,
                    $cantidad
                );


        $subtotalVenta +=
            $calculo['subtotal_base'];


        $descuentoPromociones +=
            $calculo[
                'descuento_promocion'
            ];


        $totalVenta +=
            $calculo['subtotal_final'];


        $detalleCalculado[] = [

            'producto' =>
                $producto,

            'cantidad' =>
                $cantidad,

            'calculo' =>
                $calculo
        ];


       // ====================================================
        // ENVASES
        // ====================================================

        if (
            $producto['tipo_producto']
            === 'SIMPLE'
        ) {

            /*
            * Producto vendido directamente.
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

                $cantidadRequerida =
                    $cantidad
                    *
                    (float)
                    $producto[
                        'envases_por_unidad'
                    ];


                agregarEnvasesCalculados(
                    $envasesCalculados,
                    (int)
                    $producto[
                        'tipo_envase_id'
                    ],
                    $cantidadRequerida
                );
            }

        } elseif (
            $producto['tipo_producto']
            === 'COMBO'
        ) {

            /*
            * El combo no necesariamente controla envases
            * directamente.
            *
            * Inspeccionamos sus componentes.
            */
            calcularEnvasesCombo(
                $conn,
                (int) $producto['id'],
                $cantidad,
                $envasesCalculados
            );
        }
    }


    $subtotalVenta =
        round(
            $subtotalVenta,
            2
        );


    $descuentoPromociones =
        round(
            $descuentoPromociones,
            2
        );


    $totalVenta =
        round(
            $totalVenta,
            2
        );


    /* ========================================================
       PAGO
    ======================================================== */

    if ($tipoPago === 'COMPLETO') {

        $montoPagado =
            $totalVenta;

    } elseif (
        $tipoPago === 'PENDIENTE'
    ) {

        $montoPagado = 0;

    } else {

        $montoPagado =
            round(
                $montoPagadoEnviado,
                2
            );


        if (
            $montoPagado <= 0
            ||
            $montoPagado
            >=
            $totalVenta
        ) {

            throw new Exception(
                'Monto de pago parcial inválido.'
            );
        }
    }


    $saldoPendiente =
        round(
            $totalVenta
            -
            $montoPagado,
            2
        );


    if ($saldoPendiente <= 0) {

        $estadoPago =
            'PAGADO';

    } elseif ($montoPagado > 0) {

        $estadoPago =
            'PARCIAL';

    } else {

        $estadoPago =
            'PENDIENTE';
    }


    /* ========================================================
       ENVASES ENTREGADOS
    ======================================================== */

    $mapEnvasesEntregados = [];


    if (is_array($envasesEnviados)) {

        foreach (
            $envasesEnviados
            as $envase
        ) {

            $tipoId =
                (int) (
                    $envase[
                        'tipo_envase_id'
                    ]
                    ?? 0
                );


            $entregado =
                (float) (
                    $envase[
                        'cantidad_entregada'
                    ]
                    ?? 0
                );


            if ($tipoId > 0) {

                $mapEnvasesEntregados[
                    $tipoId
                ] =
                    max(
                        0,
                        $entregado
                    );
            }
        }
    }


    $hayEnvasePendiente =
        false;


    foreach (
        $envasesCalculados
        as $tipoId => $requerido
    ) {

        $entregado =
            $mapEnvasesEntregados[
                $tipoId
            ]
            ?? 0;


        $entregado =
            min(
                $entregado,
                $requerido
            );


        if (
            $requerido
            -
            $entregado
            >
            0
        ) {

            $hayEnvasePendiente =
                true;
        }
    }


    /* ========================================================
       CLIENTE OBLIGATORIO SI EXISTE DEUDA
    ======================================================== */

    if (
        (
            $saldoPendiente > 0
            ||
            $hayEnvasePendiente
        )
        &&
        !$clienteId
    ) {

        throw new Exception(
            'Seleccione un cliente para registrar deudas o envases pendientes.'
        );
    }


    /* ========================================================
       CREAR VENTA
    ======================================================== */

    $sqlVenta = "
        INSERT INTO ventas (
            cliente_id,
            usuario_id,
            fecha,

            subtotal,
            descuento_promociones,
            descuento_manual,

            total,

            total_pagado,
            saldo_pendiente,

            estado_pago,
            estado
        )
        VALUES (
            ?,
            ?,
            NOW(),

            ?,
            ?,
            0,

            ?,

            ?,
            ?,

            ?,
            'ACTIVA'
        )
    ";


    $stmtVenta =
        $conn->prepare(
            $sqlVenta
        );


    $stmtVenta->bind_param(
        'iiddddds',

        $clienteId,
        $usuarioId,

        $subtotalVenta,
        $descuentoPromociones,
        $totalVenta,

        $montoPagado,
        $saldoPendiente,

        $estadoPago
    );


    if (!$stmtVenta->execute()) {

        throw new Exception(
            'No se pudo crear la venta: '
            . $stmtVenta->error
        );
    }


    $ventaId =
        $stmtVenta->insert_id;


    /* ========================================================
       DETALLE + STOCK
    ======================================================== */

    foreach (
        $detalleCalculado
        as $linea
    ) {

        $producto =
            $linea['producto'];


        $cantidad =
            (float)
            $linea['cantidad'];


        $calculo =
            $linea['calculo'];


        $productoId =
            (int)
            $producto['id'];


        /*
         * PRIMERO modificamos inventario.
         *
         * Además obtenemos el costo histórico correcto
         * que se guardará en detalle_venta.
         */
        if (
            $producto['tipo_producto']
            === 'COMBO'
        ) {

            $costoUnitario =
                descontarStockCombo(
                    $conn,
                    $productoId,
                    $cantidad,
                    $ventaId,
                    $usuarioId
                );

        } else {

            $costoUnitario =
                descontarStockSimple(
                    $conn,
                    $productoId,
                    $cantidad,
                    $ventaId,
                    $usuarioId
                );
        }


        $nombreProducto =
            $producto['nombre'];


        $presentacionProducto =
            $producto['presentacion'];


        $precioRegular =
            $calculo['precio_regular'];


        $precioVentaBase =
            $calculo[
                'precio_venta_base'
            ];


        $subtotalBase =
            $calculo['subtotal_base'];


        $descuentoPromo =
            $calculo[
                'descuento_promocion'
            ];


        $subtotalFinal =
            $calculo['subtotal_final'];


        $promocionId =
            $calculo['promocion_id'];


        $promocionNombre =
            $calculo[
                'promocion_nombre'
            ];


        $detallePrecioJson =
            json_encode(
                $calculo[
                    'detalle_precio'
                ],
                JSON_UNESCAPED_UNICODE
            );


        /* ====================================================
           INSERT DETALLE
        ==================================================== */

        $sqlDetalle = "
            INSERT INTO detalle_venta (
                venta_id,
                producto_id,

                nombre_producto,
                presentacion_producto,

                cantidad,

                costo_unitario,

                precio_regular,
                precio_venta_base,

                subtotal_base,

                descuento_promocion,
                descuento_manual,

                subtotal_final,

                promocion_id,
                promocion_nombre,

                detalle_precio_json
            )
            VALUES (
                ?,
                ?,

                ?,
                ?,

                ?,

                ?,

                ?,
                ?,

                ?,

                ?,
                0,

                ?,

                ?,
                ?,

                ?
            )
        ";


        $stmtDetalle =
            $conn->prepare(
                $sqlDetalle
            );


        $stmtDetalle->bind_param(
            'iissdddddddiss',

            $ventaId,
            $productoId,

            $nombreProducto,
            $presentacionProducto,

            $cantidad,

            $costoUnitario,

            $precioRegular,
            $precioVentaBase,

            $subtotalBase,

            $descuentoPromo,

            $subtotalFinal,

            $promocionId,
            $promocionNombre,
            $detallePrecioJson
        );


        if (
            !$stmtDetalle->execute()
        ) {

            throw new Exception(
                'No se pudo registrar el detalle: '
                . $stmtDetalle->error
            );
        }
    }


    /* ========================================================
       PAGO
    ======================================================== */

    if ($montoPagado > 0) {

        $sqlPago = "
            INSERT INTO pagos (
                venta_id,
                cliente_id,
                usuario_id,

                monto,
                metodo_pago,

                fecha,
                estado
            )
            VALUES (
                ?,
                ?,
                ?,

                ?,
                ?,

                NOW(),
                'ACTIVO'
            )
        ";


        $stmtPago =
            $conn->prepare(
                $sqlPago
            );


        $stmtPago->bind_param(
            'iiids',

            $ventaId,
            $clienteId,
            $usuarioId,

            $montoPagado,
            $metodoPago
        );


        if (!$stmtPago->execute()) {

            throw new Exception(
                'No se pudo registrar el pago.'
            );
        }
    }


    /* ========================================================
       ENVASES
    ======================================================== */

    foreach (
        $envasesCalculados
        as $tipoId => $requerido
    ) {

        $entregado =
            $mapEnvasesEntregados[
                $tipoId
            ]
            ?? 0;


        $entregado =
            min(
                max(
                    0,
                    $entregado
                ),
                $requerido
            );


        $pendiente =
            max(
                0,
                $requerido
                -
                $entregado
            );


        /* ====================================================
           SNAPSHOT DE VENTA
        ==================================================== */

        $sqlVentaEnvase = "
            INSERT INTO venta_envases (
                venta_id,
                tipo_envase_id,

                cantidad_requerida,
                cantidad_entregada,
                cantidad_pendiente
            )
            VALUES (
                ?,
                ?,
                ?,
                ?,
                ?
            )
        ";


        $stmtVentaEnvase =
            $conn->prepare(
                $sqlVentaEnvase
            );


        $stmtVentaEnvase
            ->bind_param(
                'iiddd',

                $ventaId,
                $tipoId,

                $requerido,
                $entregado,
                $pendiente
            );


        if (
            !$stmtVentaEnvase
                ->execute()
        ) {

            throw new Exception(
                'No se pudo registrar información de envases.'
            );
        }


        /* ====================================================
           DEUDA DE ENVASES
        ==================================================== */

        if ($pendiente > 0) {

            $sqlSaldo = "
                SELECT saldo_nuevo

                FROM movimientos_envases

                WHERE
                    cliente_id = ?
                    AND tipo_envase_id = ?

                ORDER BY id DESC

                LIMIT 1

                FOR UPDATE
            ";


            $stmtSaldo =
                $conn->prepare(
                    $sqlSaldo
                );


            $stmtSaldo->bind_param(
                'ii',
                $clienteId,
                $tipoId
            );


            $stmtSaldo->execute();


            $saldoRow =
                $stmtSaldo
                    ->get_result()
                    ->fetch_assoc();


            $saldoAnterior =
                $saldoRow
                    ? (float)
                        $saldoRow[
                            'saldo_nuevo'
                        ]
                    : 0;


            $saldoNuevo =
                $saldoAnterior
                +
                $pendiente;


            $descripcion =
                'Envases pendientes de venta #'
                . $ventaId;


            $sqlMovEnvase = "
                INSERT INTO movimientos_envases (
                    cliente_id,
                    tipo_envase_id,

                    venta_id,
                    usuario_id,

                    tipo_movimiento,

                    cantidad,

                    saldo_anterior,
                    saldo_nuevo,

                    descripcion,
                    fecha
                )
                VALUES (
                    ?,
                    ?,

                    ?,
                    ?,

                    'DEUDA',

                    ?,

                    ?,
                    ?,

                    ?,
                    NOW()
                )
            ";


            $stmtMovEnvase =
                $conn->prepare(
                    $sqlMovEnvase
                );


            $stmtMovEnvase
                ->bind_param(
                    'iiiiddds',

                    $clienteId,
                    $tipoId,

                    $ventaId,
                    $usuarioId,

                    $pendiente,

                    $saldoAnterior,
                    $saldoNuevo,

                    $descripcion
                );


            if (
                !$stmtMovEnvase
                    ->execute()
            ) {

                throw new Exception(
                    'No se pudo registrar deuda de envases.'
                );
            }
        }
    }


    /* ========================================================
       COMMIT
    ======================================================== */

    $conn->commit();


    responder(
        true,
        'Venta registrada correctamente.',
        [
            'venta_id' =>
                $ventaId,

            'total' =>
                $totalVenta,

            'pagado' =>
                $montoPagado,

            'pendiente' =>
                $saldoPendiente
        ]
    );


} catch (Throwable $e) {

    $conn->rollback();


    responder(
        false,
        $e->getMessage()
    );
}