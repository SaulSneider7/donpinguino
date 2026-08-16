<?php

session_start();

header(
    'Content-Type: application/json; charset=utf-8'
);

require_once __DIR__ . '/../../config/database.php';


function responder(
    bool $success,
    string $message,
    array $data = []
): void {

    echo json_encode(
        [
            'success' => $success,
            'message' => $message,
            'data' => $data
        ],
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


$fechaInicio =
    $_GET['fecha_inicio']
    ?? '';

$fechaFin =
    $_GET['fecha_fin']
    ?? '';


if (
    !preg_match(
        '/^\d{4}-\d{2}-\d{2}$/',
        $fechaInicio
    )
    ||
    !preg_match(
        '/^\d{4}-\d{2}-\d{2}$/',
        $fechaFin
    )
) {

    responder(
        false,
        'Fechas inválidas.'
    );
}


$desde =
    $fechaInicio
    . ' 00:00:00';

$hasta =
    $fechaFin
    . ' 23:59:59';


$sql = "
    SELECT
        DATE(fecha) AS fecha,

        COUNT(*) AS cantidad,

        SUM(total) AS total

    FROM ventas

    WHERE
        estado = 'ACTIVA'
        AND fecha BETWEEN ? AND ?

    GROUP BY
        DATE(fecha)

    ORDER BY
        fecha ASC
";


$stmt =
    $conn->prepare($sql);


$stmt->bind_param(
    'ss',
    $desde,
    $hasta
);


$stmt->execute();


$result =
    $stmt
        ->get_result();


$data = [];


while (
    $row =
        $result->fetch_assoc()
) {

    $data[] = [

        'fecha' =>
            $row['fecha'],

        'fecha_formateada' =>
            date(
                'd/m/Y',
                strtotime(
                    $row['fecha']
                )
            ),

        'cantidad' =>
            (int)
            $row['cantidad'],

        'total' =>
            round(
                (float)
                $row['total'],
                2
            )
    ];
}


responder(
    true,
    'Ventas diarias obtenidas.',
    $data
);