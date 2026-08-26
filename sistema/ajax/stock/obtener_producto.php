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


$id =
    (int) ($_GET['id'] ?? 0);


if ($id <= 0) {

    responder(
        false,
        'Producto inválido.'
    );
}


$sql = "
    SELECT
        id,
        nombre,
        presentacion,
        stock_actual,
        stock_minimo,
        maneja_stock,
        tipo_producto

    FROM productos

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


$producto =
    $stmt
        ->get_result()
        ->fetch_assoc();


if (!$producto) {

    responder(
        false,
        'Producto no encontrado.'
    );
}


if (
    (int) $producto['maneja_stock']
    !== 1
) {

    responder(
        false,
        'Este producto no maneja stock.'
    );
}


if (
    $producto['tipo_producto']
    !== 'SIMPLE'
) {

    responder(
        false,
        'El stock de los combos depende de sus componentes.'
    );
}


responder(
    true,
    'Producto encontrado.',
    $producto
);