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


$fechaNacimiento =
    trim(
        $_POST['fecha_nacimiento']
        ?? ''
    );


if ($fechaNacimiento === '') {

    $fechaNacimiento = null;

}

if (
    $fechaNacimiento !== null
    &&
    $fechaNacimiento > date('Y-m-d')
) {

    responder(
        false,
        'La fecha de nacimiento no puede ser futura.'
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
            fecha_nacimiento,
            direccion,
            activo
        )
        VALUES (
            ?,
            ?,
            ?,
            ?,
            1
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
        $fechaNacimiento,
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
        fecha_nacimiento = ?,
        direccion = ?
    WHERE id = ?
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
    $fechaNacimiento,
    $direccion,
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