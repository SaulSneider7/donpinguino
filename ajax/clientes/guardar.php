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


$id =
    isset($_POST['id'])
    && $_POST['id'] !== ''
        ? (int) $_POST['id']
        : null;


$nombre =
    trim($_POST['nombre'] ?? '');

$telefono =
    trim($_POST['telefono'] ?? '');

$direccion =
    trim($_POST['direccion'] ?? '');

$observacion =
    trim($_POST['observacion'] ?? '');


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


/* ============================================================
   CREAR
============================================================ */

if ($id === null) {

    $sql = "
        INSERT INTO clientes (
            nombre,
            telefono,
            direccion,
            observacion,
            activo
        )
        VALUES (
            ?, ?, ?, ?, 1
        )
    ";


    $stmt =
        $conn->prepare($sql);


    if (!$stmt) {

        responder(
            false,
            'No se pudo preparar la consulta: '
            . $conn->error
        );
    }


    $stmt->bind_param(
        'ssss',
        $nombre,
        $telefono,
        $direccion,
        $observacion
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
            'id' => $stmt->insert_id
        ]
    );
}


/* ============================================================
   EDITAR
============================================================ */

$sql = "
    UPDATE clientes

    SET
        nombre = ?,
        telefono = ?,
        direccion = ?,
        observacion = ?

    WHERE id = ?

    LIMIT 1
";


$stmt =
    $conn->prepare($sql);


if (!$stmt) {

    responder(
        false,
        'No se pudo preparar la consulta: '
        . $conn->error
    );
}


$stmt->bind_param(
    'ssssi',
    $nombre,
    $telefono,
    $direccion,
    $observacion,
    $id
);


if (!$stmt->execute()) {

    responder(
        false,
        'No se pudo actualizar el cliente: '
        . $stmt->error
    );
}


responder(
    true,
    'Cliente actualizado correctamente.'
);