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


$anio =
    (int) date('Y');


$mes =
    (int) (
        $_GET['mes']
        ?? date('n')
    );


if (
    $mes < 1
    ||
    $mes > 12
) {

    responder(
        false,
        'Mes inválido.'
    );
}


/* ============================================================
   GASTOS POR CATEGORÍA
============================================================ */

$sql = "
    SELECT

        tipo,

        COUNT(*) AS cantidad,

        COALESCE(
            SUM(monto),
            0
        ) AS total

    FROM gastos

    WHERE
        activo = 1

        AND YEAR(fecha) = ?

        AND MONTH(fecha) = ?

    GROUP BY
        tipo

    ORDER BY
        total DESC
";


$stmt =
    $conn->prepare(
        $sql
    );


$stmt->bind_param(
    'ii',
    $anio,
    $mes
);


$stmt->execute();


$result =
    $stmt->get_result();


$labels = [

    'AGUA' =>
        'Agua',

    'LUZ' =>
        'Luz',

    'COMIDA' =>
        'Comida',

    'INSUMOS' =>
        'Insumos',

    'DELIVERY' =>
        'Delivery',

    'ALQUILER' =>
        'Alquiler',

    'OTRO' =>
        'Otros'

];


$data = [];

$totalGeneral = 0;


while (
    $row =
        $result->fetch_assoc()
) {

    $total =
        round(
            (float)
            $row['total'],
            2
        );


    $totalGeneral +=
        $total;


    $data[] = [

        'tipo' =>
            $row['tipo'],

        'nombre' =>
            $labels[
                $row['tipo']
            ]
            ?? $row['tipo'],

        'cantidad' =>
            (int)
            $row['cantidad'],

        'total' =>
            $total

    ];

}


responder(
    true,
    'Gastos obtenidos correctamente.',
    [

        'anio' =>
            $anio,

        'mes' =>
            $mes,

        'total' =>
            round(
                $totalGeneral,
                2
            ),

        'data' =>
            $data

    ]
);