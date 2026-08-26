<?php

session_start();

header('Content-Type: application/json; charset=utf-8');

require_once __DIR__ . '/../../config/database.php';


function responder(
    bool $success,
    string $message,
    ?array $data = null
): void {

    echo json_encode([
        'success' => $success,
        'message' => $message,
        'data' => $data
    ]);

    exit;
}


if (!isset($_SESSION['usuario_id'])) {

    http_response_code(401);

    responder(
        false,
        'Sesión expirada.'
    );

}


$id = (int) ($_GET['id'] ?? 0);


if ($id <= 0) {

    responder(
        false,
        'Producto inválido.'
    );

}


$sql = "
    SELECT
        id,
        categoria_id,
        tipo_envase_id,

        nombre,
        descripcion,
        presentacion,
        tipo_producto,

        costo_referencia,
        precio_regular,
        precio_venta,

        maneja_stock,
        stock_actual,
        stock_minimo,

        controla_envase,
        envases_por_unidad,

        imagen_url,

        publicar_catalogo,
        destacado_catalogo,

        activo

    FROM productos

    WHERE id = ?

    LIMIT 1
";


$stmt = $conn->prepare($sql);

$stmt->bind_param(
    'i',
    $id
);

$stmt->execute();


$result = $stmt->get_result();

$producto = $result->fetch_assoc();


if (!$producto) {

    responder(
        false,
        'Producto no encontrado.'
    );

}


responder(
    true,
    'Producto encontrado.',
    $producto
);