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
        'Compra inválida.'
    );
}


$sqlCompra = "
    SELECT
        co.id,
        co.fecha,

        co.subtotal,
        co.descuento,
        co.total,

        co.observacion,
        co.estado,

        pr.nombre AS proveedor

    FROM compras co

    LEFT JOIN proveedores pr
        ON pr.id = co.proveedor_id

    WHERE co.id = ?

    LIMIT 1
";


$stmtCompra =
    $conn->prepare(
        $sqlCompra
    );


$stmtCompra->bind_param(
    'i',
    $id
);


$stmtCompra->execute();


$compra =
    $stmtCompra
        ->get_result()
        ->fetch_assoc();


if (!$compra) {

    responder(
        false,
        'Compra no encontrada.'
    );
}


$compra['proveedor'] =
    $compra['proveedor']
    ?: 'Sin proveedor';


$compra['fecha_formateada'] =
    date(
        'd/m/Y H:i',
        strtotime(
            $compra['fecha']
        )
    );


$sqlProductos = "
    SELECT
        dc.producto_id,
        dc.cantidad,
        dc.costo_unitario,
        dc.subtotal,

        p.nombre,
        p.presentacion

    FROM detalle_compra dc

    INNER JOIN productos p
        ON p.id = dc.producto_id

    WHERE dc.compra_id = ?

    ORDER BY dc.id ASC
";


$stmtProductos =
    $conn->prepare(
        $sqlProductos
    );


$stmtProductos->bind_param(
    'i',
    $id
);


$stmtProductos->execute();


$resultProductos =
    $stmtProductos
        ->get_result();


$productos = [];


while (
    $row =
        $resultProductos
            ->fetch_assoc()
) {

    $productos[] =
        $row;
}


responder(
    true,
    'Compra encontrada.',
    [
        'compra' =>
            $compra,

        'productos' =>
            $productos
    ]
);