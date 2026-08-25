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


if (!isset($_SESSION['usuario_id'])) {

    http_response_code(401);

    responder(
        false,
        'Sesión expirada.'
    );
}


$clienteId =
    (int) (
        $_GET['cliente_id']
        ?? 0
    );


if ($clienteId <= 0) {

    responder(
        false,
        'Cliente inválido.'
    );
}


$anio =
    (int) date('Y');


/* ============================================================
   CLIENTE
============================================================ */

$stmtCliente =
    $conn->prepare(
        "
            SELECT
                id,
                nombre

            FROM clientes

            WHERE id = ?

            LIMIT 1
        "
    );


$stmtCliente->bind_param(
    'i',
    $clienteId
);


$stmtCliente->execute();


$cliente =
    $stmtCliente
        ->get_result()
        ->fetch_assoc();


if (!$cliente) {

    responder(
        false,
        'Cliente no encontrado.'
    );
}


/* ============================================================
   MESES
============================================================ */

$nombres = [

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


$meses = [];


for (
    $i = 1;
    $i <= 12;
    $i++
) {

    $meses[$i] = [

        'mes' =>
            $i,

        'nombre' =>
            $nombres[$i],

        'total' =>
            0,

        'compras' =>
            0

    ];

}


/* ============================================================
   CONSUMO
============================================================ */

$sql = "
    SELECT

        MONTH(fecha) AS mes,

        COUNT(*) AS compras,

        COALESCE(
            SUM(total),
            0
        ) AS total

    FROM ventas

    WHERE
        estado = 'ACTIVA'

        AND cliente_id = ?

        AND YEAR(fecha) = ?

    GROUP BY
        MONTH(fecha)
";


$stmt =
    $conn->prepare(
        $sql
    );


$stmt->bind_param(
    'ii',
    $clienteId,
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


    $meses[$mes]['total'] =
        round(
            (float)
            $row['total'],
            2
        );


    $meses[$mes]['compras'] =
        (int)
        $row['compras'];

}


/* ============================================================
   TOTAL AÑO
============================================================ */

$totalAnio = 0;

$totalCompras = 0;


foreach (
    $meses
    as $mes
) {

    $totalAnio +=
        $mes['total'];

    $totalCompras +=
        $mes['compras'];

}


responder(
    true,
    'Información obtenida correctamente.',
    [

        'cliente' => [

            'id' =>
                (int)
                $cliente['id'],

            'nombre' =>
                $cliente['nombre']

        ],

        'anio' =>
            $anio,

        'total' =>
            round(
                $totalAnio,
                2
            ),

        'compras' =>
            $totalCompras,

        'data' =>
            array_values(
                $meses
            )

    ]
);