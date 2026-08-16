<?php

session_start();

header('Content-Type: application/json; charset=utf-8');

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
    (int) ($_GET['cliente_id'] ?? 0);


if ($clienteId <= 0) {

    responder(
        false,
        'Cliente inválido.'
    );
}


$sql = "
    SELECT
        id,
        fecha,
        total,
        total_pagado,
        saldo_pendiente,
        estado_pago

    FROM ventas

    WHERE
        cliente_id = ?
        AND estado = 'ACTIVA'
        AND saldo_pendiente > 0

    ORDER BY fecha ASC, id ASC
";


$stmt =
    $conn->prepare($sql);


$stmt->bind_param(
    'i',
    $clienteId
);


$stmt->execute();


$result =
    $stmt->get_result();


$ventas = [];

$totalDeuda = 0;


while ($row = $result->fetch_assoc()) {

    $saldo =
        (float) $row['saldo_pendiente'];


    $totalDeuda +=
        $saldo;


    $ventas[] = [

        'id' =>
            (int) $row['id'],

        'fecha' =>
            $row['fecha'],

        'fecha_formateada' =>
            date(
                'd/m/Y H:i',
                strtotime(
                    $row['fecha']
                )
            ),

        'total' =>
            (float) $row['total'],

        'total_pagado' =>
            (float) $row['total_pagado'],

        'saldo_pendiente' =>
            $saldo,

        'estado_pago' =>
            $row['estado_pago']
    ];
}


responder(
    true,
    'Ventas pendientes obtenidas.',
    [
        'ventas' =>
            $ventas,

        'total_deuda' =>
            round(
                $totalDeuda,
                2
            )
    ]
);