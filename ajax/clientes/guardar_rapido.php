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


/*
 * Evitamos crear el mismo teléfono dos veces
 * cuando sí se proporcionó teléfono.
 */
if ($telefono !== '') {

    $sqlExiste = "
        SELECT
            id,
            nombre,
            telefono

        FROM clientes

        WHERE
            telefono = ?
            AND activo = 1

        LIMIT 1
    ";


    $stmtExiste =
        $conn->prepare(
            $sqlExiste
        );


    $stmtExiste->bind_param(
        's',
        $telefono
    );


    $stmtExiste->execute();


    $existente =
        $stmtExiste
            ->get_result()
            ->fetch_assoc();


    if ($existente) {

        responder(
            false,
            'Ya existe un cliente con ese teléfono.',
            [
                'cliente_existente' => [
                    'id' =>
                        (int) $existente['id'],

                    'nombre' =>
                        $existente['nombre'],

                    'telefono' =>
                        $existente['telefono']
                ]
            ]
        );
    }
}


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