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
                'success' =>
                    $success,

                'message' =>
                    $message
            ],
            $extra
        ),
        JSON_UNESCAPED_UNICODE
    );

    exit;
}


if (
    !isset(
        $_SESSION['usuario_id']
    )
) {

    http_response_code(401);

    responder(
        false,
        'Sesión expirada.'
    );
}


/* ============================================================
   AÑO
============================================================ */

$anio =
    (int) (
        $_GET['anio']
        ?? date('Y')
    );


if (
    $anio < 2000
    ||
    $anio > 2100
) {

    responder(
        false,
        'Año inválido.'
    );
}


/* ============================================================
   MESES
============================================================ */

$nombresMeses = [

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


/*
 * Creamos siempre los 12 meses.
 *
 * Así aunque febrero no tenga ventas,
 * febrero seguirá apareciendo como 0.
 */

$meses = [];


for (
    $i = 1;
    $i <= 12;
    $i++
) {

    $meses[$i] = [

        'mes' =>
            $i,

        'mes_nombre' =>
            $nombresMeses[$i],

        'ventas' =>
            0,

        'utilidad_bruta' =>
            0,

        'gastos' =>
            0,

        'ganancia' =>
            0

    ];
}


/* ============================================================
   1. VENTAS POR MES
============================================================ */

/*
 * Aquí utilizamos ventas.total.
 *
 * Esto es importante porque total ya representa
 * el importe final que tuvo la venta.
 */

$sql = "
    SELECT

        MONTH(fecha) AS mes,

        COALESCE(
            SUM(total),
            0
        ) AS ventas

    FROM ventas

    WHERE
        estado = 'ACTIVA'

        AND YEAR(fecha) = ?

    GROUP BY
        MONTH(fecha)
";


$stmt =
    $conn->prepare(
        $sql
    );


$stmt->bind_param(
    'i',
    $anio
);


$stmt->execute();


$result =
    $stmt->get_result();


while (
    $row =
        $result->fetch_assoc()
) {

    $mes =
        (int)
        $row['mes'];


    if (
        isset(
            $meses[$mes]
        )
    ) {

        $meses[$mes]['ventas'] =
            round(
                (float)
                $row['ventas'],
                2
            );
    }

}


/* ============================================================
   2. UTILIDAD BRUTA POR MES
============================================================ */

/*
 * Utilidad bruta histórica:
 *
 * subtotal_final
 * -
 * costo que tenía el producto al venderlo
 *
 *
 * detalle_venta guarda el costo histórico,
 * por eso NO usamos productos.costo_referencia.
 */

$sql = "
    SELECT

        MONTH(v.fecha) AS mes,

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
        ) AS utilidad_bruta

    FROM ventas v

    INNER JOIN detalle_venta d
        ON d.venta_id = v.id

    WHERE
        v.estado = 'ACTIVA'

        AND YEAR(v.fecha) = ?

    GROUP BY
        MONTH(v.fecha)
";


$stmt =
    $conn->prepare(
        $sql
    );


$stmt->bind_param(
    'i',
    $anio
);


$stmt->execute();


$result =
    $stmt->get_result();


while (
    $row =
        $result->fetch_assoc()
) {

    $mes =
        (int)
        $row['mes'];


    if (
        isset(
            $meses[$mes]
        )
    ) {

        $meses[$mes]['utilidad_bruta'] =
            round(
                (float)
                $row['utilidad_bruta'],
                2
            );
    }

}


/* ============================================================
   3. GASTOS POR MES
============================================================ */

$sql = "
    SELECT

        MONTH(fecha) AS mes,

        COALESCE(
            SUM(monto),
            0
        ) AS gastos

    FROM gastos

    WHERE
        activo = 1

        AND YEAR(fecha) = ?

    GROUP BY
        MONTH(fecha)
";


$stmt =
    $conn->prepare(
        $sql
    );


$stmt->bind_param(
    'i',
    $anio
);


$stmt->execute();


$result =
    $stmt->get_result();


while (
    $row =
        $result->fetch_assoc()
) {

    $mes =
        (int)
        $row['mes'];


    if (
        isset(
            $meses[$mes]
        )
    ) {

        $meses[$mes]['gastos'] =
            round(
                (float)
                $row['gastos'],
                2
            );
    }

}


/* ============================================================
   4. GANANCIA DESPUÉS DE GASTOS
============================================================ */

foreach (
    $meses
    as $numeroMes => &$mes
) {

    $mes['ganancia'] =
        round(
            $mes['utilidad_bruta']
            -
            $mes['gastos'],
            2
        );

}

unset($mes);


/* ============================================================
   5. RESUMEN ANUAL
============================================================ */

$totalVentas =
    0;

$totalUtilidad =
    0;

$totalGastos =
    0;

$totalGanancia =
    0;


foreach (
    $meses
    as $mes
) {

    $totalVentas +=
        $mes['ventas'];


    $totalUtilidad +=
        $mes['utilidad_bruta'];


    $totalGastos +=
        $mes['gastos'];


    $totalGanancia +=
        $mes['ganancia'];

}


/* ============================================================
   RESPONSE
============================================================ */

responder(
    true,
    'Información obtenida correctamente.',
    [

        'anio' =>
            $anio,


        'resumen' => [

            'ventas' =>
                round(
                    $totalVentas,
                    2
                ),

            'utilidad_bruta' =>
                round(
                    $totalUtilidad,
                    2
                ),

            'gastos' =>
                round(
                    $totalGastos,
                    2
                ),

            'ganancia' =>
                round(
                    $totalGanancia,
                    2
                )

        ],


        'data' =>
            array_values(
                $meses
            )

    ]
);