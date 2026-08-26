<?php

session_start();

header(
    'Content-Type: application/json; charset=utf-8'
);

require_once __DIR__ . '/../../config/database.php';


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
   PARÁMETROS
============================================================ */

$periodo =
    strtoupper(
        trim(
            $_GET['periodo']
            ?? 'SEMANA'
        )
    );


$metrica =
    strtoupper(
        trim(
            $_GET['metrica']
            ?? 'VENTAS'
        )
    );


$productoId =
    (int) (
        $_GET['producto_id']
        ?? 0
    );


$clienteId =
    (int) (
        $_GET['cliente_id']
        ?? 0
    );


$fechaDesdeManual =
    trim(
        $_GET['desde']
        ?? ''
    );


$fechaHastaManual =
    trim(
        $_GET['hasta']
        ?? ''
    );


$periodosPermitidos = [
    'HOY',
    'AYER',
    'SEMANA',
    'MES',
    'ANIO',
    'PERSONALIZADO'
];


$metricasPermitidas = [
    'VENTAS',
    'UTILIDAD',
    'UNIDADES',
    'CANTIDAD_VENTAS'
];


if (
    !in_array(
        $periodo,
        $periodosPermitidos,
        true
    )
) {

    responder(
        false,
        'Período inválido.'
    );
}


if (
    !in_array(
        $metrica,
        $metricasPermitidas,
        true
    )
) {

    responder(
        false,
        'Métrica inválida.'
    );
}


/* ============================================================
   FECHAS DEL PERÍODO
============================================================ */

$hoy =
    new DateTime(
        date('Y-m-d')
    );


switch ($periodo) {

    case 'HOY':

        $desde =
            clone $hoy;

        $hasta =
            clone $hoy;

        break;


    case 'AYER':

        $desde =
            clone $hoy;

        $desde->modify(
            '-1 day'
        );

        $hasta =
            clone $desde;

        break;


    case 'SEMANA':

        /*
         * Semana desde lunes hasta domingo.
         */

        $numeroDia =
            (int)
            $hoy->format('N');


        $desde =
            clone $hoy;


        $desde->modify(
            '-'
            .
            (
                $numeroDia - 1
            )
            .
            ' days'
        );


        $finSemana =
            clone $desde;


        $finSemana->modify(
            '+6 days'
        );


        /*
        * Si todavía estamos dentro de esta semana,
        * no mostramos días futuros.
        */

        $hasta =
            $finSemana > $hoy
                ? clone $hoy
                : $finSemana;

        break;


    case 'MES':

        $desde =
            new DateTime(
                date('Y-m-01')
            );


        $hasta =
            clone $desde;


        $desde =
            new DateTime(
                date('Y-m-01')
            );


        $hasta =
            clone $hoy;

        break;


    case 'ANIO':

        $desde =
            new DateTime(
                date('Y-01-01')
            );


        $hasta =
            clone $hoy;

        break;


    case 'PERSONALIZADO':

        if (
            !preg_match(
                '/^\d{4}-\d{2}-\d{2}$/',
                $fechaDesdeManual
            )
            ||
            !preg_match(
                '/^\d{4}-\d{2}-\d{2}$/',
                $fechaHastaManual
            )
        ) {

            responder(
                false,
                'Seleccione las fechas del rango.'
            );
        }


        $desde =
            DateTime::createFromFormat(
                'Y-m-d',
                $fechaDesdeManual
            );


        $hasta =
            DateTime::createFromFormat(
                'Y-m-d',
                $fechaHastaManual
            );


        if (
            !$desde
            ||
            !$hasta
        ) {

            responder(
                false,
                'Fechas inválidas.'
            );
        }


        if ($desde > $hasta) {

            responder(
                false,
                'La fecha inicial no puede ser mayor a la fecha final.'
            );
        }

        break;
}


/* ============================================================
   GRANULARIDAD
============================================================ */

$diasDiferencia =
    (int)
    $desde
        ->diff($hasta)
        ->days
    + 1;


if (
    $diasDiferencia === 1
) {

    $granularidad =
        'HORAS';

} elseif (
    $diasDiferencia <= 45
) {

    $granularidad =
        'DIAS';

} else {

    $granularidad =
        'MESES';
}


$fechaDesde =
    $desde->format(
        'Y-m-d'
    );


$fechaHasta =
    $hasta->format(
        'Y-m-d'
    );


/* ============================================================
   CREAR TODOS LOS PUNTOS DEL GRÁFICO

   Esto hace que incluso los períodos sin ventas aparezcan
   con valor 0.
============================================================ */

$puntos = [];


/* ============================================================
   HORAS
============================================================ */

if (
    $granularidad === 'HORAS'
) {

    for (
        $hora = 0;
        $hora <= 23;
        $hora++
    ) {

        $key =
            str_pad(
                (string)
                $hora,
                2,
                '0',
                STR_PAD_LEFT
            );


        $puntos[$key] = [

            'key' =>
                $key,

            'label' =>
                $key
                .
                ':00',

            'valor' =>
                0

        ];
    }
}


/* ============================================================
   DÍAS
============================================================ */

if (
    $granularidad === 'DIAS'
) {

    $cursor =
        clone $desde;


    while (
        $cursor <= $hasta
    ) {

        $key =
            $cursor->format(
                'Y-m-d'
            );


        /*
         * Para la semana queremos:
         *
         * Lun
         * Mar
         * Mié
         * ...
         *
         * Para rangos más largos:
         *
         * 05 Ago
         * 06 Ago
         */

        if (
            $periodo === 'SEMANA'
        ) {

            $numeroDia =
                (int)
                $cursor->format('N');


            $dias = [

                1 => 'Lun',
                2 => 'Mar',
                3 => 'Mié',
                4 => 'Jue',
                5 => 'Vie',
                6 => 'Sáb',
                7 => 'Dom'

            ];


            $label =
                $dias[
                    $numeroDia
                ];

        } else {

            $meses = [

                1 => 'Ene',
                2 => 'Feb',
                3 => 'Mar',
                4 => 'Abr',
                5 => 'May',
                6 => 'Jun',
                7 => 'Jul',
                8 => 'Ago',
                9 => 'Sep',
                10 => 'Oct',
                11 => 'Nov',
                12 => 'Dic'

            ];


            $label =
                $cursor->format(
                    'd'
                )
                .
                ' '
                .
                $meses[
                    (int)
                    $cursor->format(
                        'n'
                    )
                ];

        }


        $puntos[$key] = [

            'key' =>
                $key,

            'label' =>
                $label,

            'valor' =>
                0

        ];


        $cursor->modify(
            '+1 day'
        );
    }
}


/* ============================================================
   MESES
============================================================ */

if (
    $granularidad === 'MESES'
) {

    $meses = [

        1 => 'Ene',
        2 => 'Feb',
        3 => 'Mar',
        4 => 'Abr',
        5 => 'May',
        6 => 'Jun',
        7 => 'Jul',
        8 => 'Ago',
        9 => 'Sep',
        10 => 'Oct',
        11 => 'Nov',
        12 => 'Dic'

    ];


    $cursor =
        new DateTime(
            $desde->format(
                'Y-m-01'
            )
        );


    $limite =
        new DateTime(
            $hasta->format(
                'Y-m-01'
            )
        );


    while (
        $cursor <= $limite
    ) {

        $key =
            $cursor->format(
                'Y-m'
            );


        /*
         * Si todo está dentro del mismo año,
         * mostramos solo Ene, Feb...
         *
         * Si el rango cruza años:
         * Ene 2026, Feb 2026...
         */

        if (
            $desde->format('Y')
            ===
            $hasta->format('Y')
        ) {

            $label =
                $meses[
                    (int)
                    $cursor->format('n')
                ];

        } else {

            $label =
                $meses[
                    (int)
                    $cursor->format('n')
                ]
                .
                ' '
                .
                $cursor->format(
                    'Y'
                );

        }


        $puntos[$key] = [

            'key' =>
                $key,

            'label' =>
                $label,

            'valor' =>
                0

        ];


        $cursor->modify(
            '+1 month'
        );
    }
}


/* ============================================================
   FILTROS SQL
============================================================ */

$filtroCliente =
    '';


$filtroProducto =
    '';


$paramsExtra = [];

$typesExtra = '';


if (
    $clienteId > 0
) {

    $filtroCliente =
        "
            AND v.cliente_id = ?
        ";


    $paramsExtra[] =
        $clienteId;


    $typesExtra .=
        'i';
}


if (
    $productoId > 0
) {

    $filtroProducto =
        "
            AND d.producto_id = ?
        ";


    $paramsExtra[] =
        $productoId;


    $typesExtra .=
        'i';
}


/* ============================================================
   EXPRESIÓN PARA AGRUPAR
============================================================ */

switch (
    $granularidad
) {

    case 'HORAS':

        $grupoSql =
            "DATE_FORMAT(v.fecha, '%H')";

        break;


    case 'DIAS':

        $grupoSql =
            "DATE_FORMAT(v.fecha, '%Y-%m-%d')";

        break;


    default:

        $grupoSql =
            "DATE_FORMAT(v.fecha, '%Y-%m')";
}


/* ============================================================
   MÉTRICA: VENTAS
============================================================ */

if (
    $metrica === 'VENTAS'
) {

    /*
     * Si NO filtramos por producto podemos usar directamente
     * ventas.total.
     *
     * Si filtramos por producto usamos detalle_venta.subtotal_final
     * porque queremos saber cuánto vendió ese producto.
     */

    if (
        $productoId > 0
    ) {

        $sql = "
            SELECT

                $grupoSql AS grupo,

                COALESCE(
                    SUM(
                        d.subtotal_final
                    ),
                    0
                ) AS valor

            FROM ventas v

            INNER JOIN detalle_venta d
                ON d.venta_id = v.id

            WHERE
                v.estado = 'ACTIVA'

                AND DATE(v.fecha)
                    BETWEEN ? AND ?

                $filtroCliente

                $filtroProducto

            GROUP BY
                $grupoSql

            ORDER BY
                $grupoSql
        ";

    } else {

        /*
         * No hacemos JOIN con detalle_venta para evitar repetir
         * ventas por cada producto.
         */

        $sql = "
            SELECT

                $grupoSql AS grupo,

                COALESCE(
                    SUM(
                        v.total
                    ),
                    0
                ) AS valor

            FROM ventas v

            WHERE
                v.estado = 'ACTIVA'

                AND DATE(v.fecha)
                    BETWEEN ? AND ?

                $filtroCliente

            GROUP BY
                $grupoSql

            ORDER BY
                $grupoSql
        ";
    }


    $metricaNombre =
        $productoId > 0
            ? 'Ventas del producto'
            : 'Ventas';


    $formato =
        'MONEDA';
}


/* ============================================================
   MÉTRICA: UTILIDAD
============================================================ */

elseif (
    $metrica === 'UTILIDAD'
) {

    $sql = "
        SELECT

            $grupoSql AS grupo,

            COALESCE(
                SUM(
                    d.subtotal_final
                    -
                    (
                        d.costo_unitario
                        *
                        d.cantidad
                    )
                ),
                0
            ) AS valor

        FROM ventas v

        INNER JOIN detalle_venta d
            ON d.venta_id = v.id

        WHERE
            v.estado = 'ACTIVA'

            AND DATE(v.fecha)
                BETWEEN ? AND ?

            $filtroCliente

            $filtroProducto

        GROUP BY
            $grupoSql

        ORDER BY
            $grupoSql
    ";


    $metricaNombre =
        'Utilidad bruta';


    $formato =
        'MONEDA';
}


/* ============================================================
   MÉTRICA: UNIDADES
============================================================ */

elseif (
    $metrica === 'UNIDADES'
) {

    $sql = "
        SELECT

            $grupoSql AS grupo,

            COALESCE(
                SUM(
                    d.cantidad
                ),
                0
            ) AS valor

        FROM ventas v

        INNER JOIN detalle_venta d
            ON d.venta_id = v.id

        WHERE
            v.estado = 'ACTIVA'

            AND DATE(v.fecha)
                BETWEEN ? AND ?

            $filtroCliente

            $filtroProducto

        GROUP BY
            $grupoSql

        ORDER BY
            $grupoSql
    ";


    $metricaNombre =
        $productoId > 0
            ? 'Unidades vendidas del producto'
            : 'Unidades vendidas';


    $formato =
        'NUMERO';
}


/* ============================================================
   MÉTRICA: NÚMERO DE VENTAS
============================================================ */

else {

    /*
     * DISTINCT es importante si existe filtro por producto,
     * porque una venta puede tener varios detalles.
     */

    if (
        $productoId > 0
    ) {

        $sql = "
            SELECT

                $grupoSql AS grupo,

                COUNT(
                    DISTINCT v.id
                ) AS valor

            FROM ventas v

            INNER JOIN detalle_venta d
                ON d.venta_id = v.id

            WHERE
                v.estado = 'ACTIVA'

                AND DATE(v.fecha)
                    BETWEEN ? AND ?

                $filtroCliente

                $filtroProducto

            GROUP BY
                $grupoSql

            ORDER BY
                $grupoSql
        ";

    } else {

        $sql = "
            SELECT

                $grupoSql AS grupo,

                COUNT(
                    v.id
                ) AS valor

            FROM ventas v

            WHERE
                v.estado = 'ACTIVA'

                AND DATE(v.fecha)
                    BETWEEN ? AND ?

                $filtroCliente

            GROUP BY
                $grupoSql

            ORDER BY
                $grupoSql
        ";
    }


    $metricaNombre =
        'Número de ventas';


    $formato =
        'NUMERO';
}


/* ============================================================
   EJECUTAR
============================================================ */

$stmt =
    $conn->prepare(
        $sql
    );


if (!$stmt) {

    responder(
        false,
        'No se pudo preparar la consulta: '
        .
        $conn->error
    );
}


$params = [

    $fechaDesde,
    $fechaHasta

];


$types =
    'ss';


foreach (
    $paramsExtra
    as $param
) {

    $params[] =
        $param;
}


$types .=
    $typesExtra;


$stmt->bind_param(
    $types,
    ...$params
);


$stmt->execute();


$result =
    $stmt->get_result();


while (
    $row =
        $result->fetch_assoc()
) {

    $grupo =
        $row['grupo'];


    if (
        isset(
            $puntos[$grupo]
        )
    ) {

        $puntos[$grupo]['valor'] =
            round(
                (float)
                $row['valor'],
                2
            );
    }
}


/* ============================================================
   TOTAL DEL PERÍODO
============================================================ */

$total =
    0;


foreach (
    $puntos
    as $punto
) {

    $total +=
        $punto['valor'];
}


/* ============================================================
   TEXTO DE PERÍODO
============================================================ */

$nombresPeriodo = [

    'HOY' =>
        'Hoy',

    'AYER' =>
        'Ayer',

    'SEMANA' =>
        'Esta semana',

    'MES' =>
        'Este mes',

    'ANIO' =>
        'Este año',

    'PERSONALIZADO' =>
        'Rango personalizado'

];


/* ============================================================
   RESPUESTA
============================================================ */

responder(
    true,
    'Información obtenida correctamente.',
    [

        'periodo' =>
            $periodo,

        'periodo_nombre' =>
            $nombresPeriodo[
                $periodo
            ],

        'desde' =>
            $fechaDesde,

        'hasta' =>
            $fechaHasta,

        'granularidad' =>
            $granularidad,

        'metrica' =>
            $metrica,

        'metrica_nombre' =>
            $metricaNombre,

        'formato' =>
            $formato,

        'producto_id' =>
            $productoId,

        'cliente_id' =>
            $clienteId,

        'total' =>
            round(
                $total,
                2
            ),

        'data' =>
            array_values(
                $puntos
            )

    ]
);