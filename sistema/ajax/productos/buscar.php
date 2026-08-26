<?php

session_start();

header(
    'Content-Type: application/json; charset=utf-8'
);

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

        AND (
            p.tipo_producto = 'SIMPLE'

            OR EXISTS (
                SELECT 1

                FROM combos cb

                INNER JOIN combo_componentes cc
                    ON cc.combo_id = cb.id

                WHERE
                    cb.producto_id = p.id
                    AND cb.activo = 1
            )
        )
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


    $types =
        'sss';
}


$sql = "
    SELECT
        p.id,
        p.nombre,
        p.presentacion,
        p.precio_regular,
        p.precio_venta,
        p.stock_actual,
        p.stock_minimo,
        p.maneja_stock,
        p.tipo_producto,
        p.controla_envase,

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


    $text .=
        ' - S/'
        . number_format(
            (float) $row['precio_venta'],
            2
        );


    $data[] = [

        'id' =>
            (int) $row['id'],

        'text' =>
            $text,

        'nombre' =>
            $row['nombre'],

        'presentacion' =>
            $row['presentacion'],

        'precio_venta' =>
            (float) $row['precio_venta'],

        'precio_regular' =>
            (float) $row['precio_regular'],

        'stock_actual' =>
            (float) $row['stock_actual'],

        'maneja_stock' =>
            (int) $row['maneja_stock'],

        'tipo_producto' =>
            $row['tipo_producto'],

        'controla_envase' =>
            (int) $row['controla_envase']
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