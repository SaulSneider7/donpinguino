<?php

session_start();

header(
    'Content-Type: application/json; charset=utf-8'
);

require_once __DIR__ . '/../../config/database.php';


function responder(
    bool $success,
    string $message
): void {

    echo json_encode(
        [
            'success' =>
                $success,

            'message' =>
                $message
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
        $_POST['id']
        ?? 0
    );


if ($id <= 0) {

    responder(
        false,
        'Gasto inválido.'
    );
}


$sql = "
    UPDATE gastos

    SET activo =
        IF(
            activo = 1,
            0,
            1
        )

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


if (
    !$stmt->execute()
) {

    responder(
        false,
        'No se pudo actualizar el gasto.'
    );
}


if (
    $stmt->affected_rows === 0
) {

    responder(
        false,
        'Gasto no encontrado.'
    );
}


responder(
    true,
    'Estado actualizado correctamente.'
);