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


$nombre =
    trim($_POST['nombre'] ?? '');

$ruc =
    trim($_POST['ruc'] ?? '');

$telefono =
    trim($_POST['telefono'] ?? '');


if ($nombre === '') {

    responder(
        false,
        'Ingrese el nombre del proveedor.'
    );
}


$sql = "
    INSERT INTO proveedores (
        nombre,
        ruc,
        telefono,
        activo
    )
    VALUES (
        ?, ?, ?, 1
    )
";


$stmt =
    $conn->prepare($sql);


$stmt->bind_param(
    'sss',
    $nombre,
    $ruc,
    $telefono
);


if (!$stmt->execute()) {

    responder(
        false,
        'No se pudo registrar el proveedor: '
        . $stmt->error
    );
}


responder(
    true,
    'Proveedor registrado correctamente.',
    [
        'id' =>
            $stmt->insert_id,

        'nombre' =>
            $nombre
    ]
);