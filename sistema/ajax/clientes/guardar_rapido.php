<?php

session_start();

header(
    'Content-Type: application/json; charset=utf-8'
);

require_once __DIR__ . '/../../config/database.php';
require_once __DIR__ . '/validar_duplicados.php';


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

$telefono =
    trim($_POST['telefono'] ?? '');

$direccion =
    trim($_POST['direccion'] ?? '');


if ($nombre === '') {

    responder(
        false,
        'Ingrese el nombre del cliente.'
    );
}


if (mb_strlen($nombre) > 150) {

    responder(
        false,
        'El nombre es demasiado largo.'
    );
}


validarDuplicadosCliente($conn, $nombre, $telefono);


$sql = "
    INSERT INTO clientes (
        nombre,
        telefono,
        direccion,
        observacion,
        activo
    )
    VALUES (
        ?,
        ?,
        ?,
        '',
        1
    )
";


$stmt =
    $conn->prepare($sql);


$stmt->bind_param(
    'sss',
    $nombre,
    $telefono,
    $direccion
);


if (!$stmt->execute()) {

    responder(
        false,
        'No se pudo registrar el cliente: '
        . $stmt->error
    );
}


responder(
    true,
    'Cliente registrado correctamente.',
    [
        'id' =>
            $stmt->insert_id,

        'nombre' =>
            $nombre,

        'telefono' =>
            $telefono
    ]
);