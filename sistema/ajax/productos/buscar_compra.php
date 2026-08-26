<?php

session_start();

header('Content-Type: application/json; charset=utf-8');

require_once __DIR__ . '/../../config/database.php';


if (!isset($_SESSION['usuario_id'])) {

    http_response_code(401);

    echo json_encode([
        'results' => []
    ]);

    exit;
}


$q =
    trim($_GET['q'] ?? '');


$page =
    max(
        1,
        (int) ($_GET['page'] ?? 1)
    );


$limit = 20;

$offset =
    ($page - 1) * $limit;


$where = "
    WHERE
        p.activo = 1
        AND p.tipo_producto = 'SIMPLE'
        AND p.maneja_stock = 1
";


$params = [];

$types = '';


if ($q !== '') {

    $where .= "
        AND (
            p.nombre LIKE ?
            OR p.presentacion LIKE ?
            OR c.nombre LIKE ?
        )
    ";


    $buscar =
        '%' . $q . '%';


    $params = [
        $buscar,
        $buscar,
        $buscar
    ];


    $types = 'sss';
}


$sql = "
    SELECT
        p.id,
        p.nombre,
        p.presentacion,

        p.costo_referencia,
        p.stock_actual,

        c.nombre AS categoria

    FROM productos p

    LEFT JOIN categorias c
        ON c.id = p.categoria_id

    $where

    ORDER BY
        p.nombre ASC

    LIMIT ?, ?
";


$params[] =
    $offset;

$params[] =
    $limit + 1;


$types .= 'ii';


$stmt =
    $conn->prepare($sql);


$stmt->bind_param(
    $types,
    ...$params
);


$stmt->execute();


$result =
    $stmt->get_result();


$rows = [];


while ($row = $result->fetch_assoc()) {
    $rows[] = $row;
}


$more =
    count($rows) > $limit;


if ($more) {
    array_pop($rows);
}


$data = [];


foreach ($rows as $row) {

    $text =
        $row['nombre'];


    if (!empty($row['presentacion'])) {

        $text .=
            ' - '
            . $row['presentacion'];
    }


    $data[] = [

        'id' =>
            (int) $row['id'],

        'text' =>
            $text,

        'nombre' =>
            $row['nombre'],

        'presentacion' =>
            $row['presentacion'],

        'costo_referencia' =>
            (float) $row['costo_referencia'],

        'stock_actual' =>
            (float) $row['stock_actual']
    ];
}


echo json_encode(
    [
        'results' => $data,

        'pagination' => [
            'more' => $more
        ]
    ],
    JSON_UNESCAPED_UNICODE
);