<?php

session_start();

header('Content-Type: application/json; charset=utf-8');

require_once __DIR__ . '/../../config/database.php';


function responder(
    bool $success,
    string $message,
    ?array $data = null
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


$clienteId =
    (int) (
        $_GET['cliente_id']
        ?? 0
    );


$tipoEnvaseId =
    (int) (
        $_GET['tipo_envase_id']
        ?? 0
    );


if (
    $clienteId <= 0
    ||
    $tipoEnvaseId <= 0
) {

    responder(
        false,
        'Datos inválidos.'
    );
}


$sql = "
    SELECT
        me.saldo_nuevo,

        c.nombre AS cliente,

        te.nombre AS tipo_envase

    FROM movimientos_envases me

    INNER JOIN clientes c
        ON c.id = me.cliente_id

    INNER JOIN tipos_envase te
        ON te.id = me.tipo_envase_id

    WHERE
        me.cliente_id = ?
        AND me.tipo_envase_id = ?

    ORDER BY me.id DESC

    LIMIT 1
";


$stmt =
    $conn->prepare(
        $sql
    );


$stmt->bind_param(
    'ii',
    $clienteId,
    $tipoEnvaseId
);


$stmt->execute();


$row =
    $stmt
        ->get_result()
        ->fetch_assoc();


if (!$row) {

    responder(
        false,
        'No existen movimientos para este envase.'
    );
}


$saldo =
    (float)
    $row['saldo_nuevo'];


if ($saldo <= 0) {

    responder(
        false,
        'Este cliente ya no tiene envases pendientes.'
    );
}


responder(
    true,
    'Saldo obtenido.',
    [
        'cliente_id' =>
            $clienteId,

        'tipo_envase_id' =>
            $tipoEnvaseId,

        'cliente' =>
            $row['cliente'],

        'tipo_envase' =>
            $row['tipo_envase'],

        'saldo' =>
            $saldo
    ]
);