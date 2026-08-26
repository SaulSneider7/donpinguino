<?php

session_start();

header(
    'Content-Type: application/json; charset=utf-8'
);

require_once __DIR__ . '/../../config/database.php';


function responder(
    bool $success,
    string $message,
    ?array $data = null
): void {

    echo json_encode(
        [
            'success' =>
                $success,

            'message' =>
                $message,

            'data' =>
                $data
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


$id =
    (int) (
        $_GET['id']
        ?? 0
    );


if ($id <= 0) {

    responder(
        false,
        'Gasto inválido.'
    );
}


$sql = "
    SELECT
        id,
        tipo,
        descripcion,
        monto,
        fecha,
        observacion,
        activo

    FROM gastos

    WHERE id = ?

    LIMIT 1
";


$stmt =
    $conn->prepare(
        $sql
    );


$stmt->bind_param(
    'i',
    $id
);


$stmt->execute();


$gasto =
    $stmt
        ->get_result()
        ->fetch_assoc();


if (!$gasto) {

    responder(
        false,
        'Gasto no encontrado.'
    );
}


responder(
    true,
    'Gasto encontrado.',
    $gasto
);