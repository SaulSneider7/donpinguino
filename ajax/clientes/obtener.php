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


$id =
    (int) ($_GET['id'] ?? 0);


if ($id <= 0) {

    responder(
        false,
        'Cliente inválido.'
    );
}


$sql = "
    SELECT
        id,
        nombre,
        telefono,
        direccion,
        observacion,
        activo

    FROM clientes

    WHERE id = ?

    LIMIT 1
";


$stmt =
    $conn->prepare($sql);


$stmt->bind_param(
    'i',
    $id
);


$stmt->execute();


$result =
    $stmt->get_result();


$cliente =
    $result->fetch_assoc();


if (!$cliente) {

    responder(
        false,
        'Cliente no encontrado.'
    );
}


responder(
    true,
    'Cliente encontrado.',
    $cliente
);