<?php

session_start();

header('Content-Type: application/json; charset=utf-8');

require_once __DIR__ . '/../../config/database.php';


function responder(
    bool $success,
    string $message
): void {

    echo json_encode(
        [
            'success' => $success,
            'message' => $message
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
    (int) ($_POST['id'] ?? 0);


if ($id <= 0) {

    responder(
        false,
        'Promoción inválida.'
    );
}


$stmt =
    $conn->prepare(
        "
            UPDATE promociones

            SET activo =
                IF(
                    activo = 1,
                    0,
                    1
                )

            WHERE id = ?

            LIMIT 1
        "
    );


$stmt->bind_param(
    'i',
    $id
);


if (!$stmt->execute()) {

    responder(
        false,
        'No se pudo actualizar la promoción.'
    );
}


if (
    $stmt->affected_rows === 0
) {

    responder(
        false,
        'Promoción no encontrada.'
    );
}


responder(
    true,
    'Estado actualizado correctamente.'
);