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


$search =
    trim(
        $_GET['q'] ?? ''
    );


$page =
    max(
        1,
        (int) ($_GET['page'] ?? 1)
    );


$limit = 20;

$offset =
    ($page - 1) * $limit;


$where = "
    WHERE activo = 1
";


$params = [];

$types = '';


if ($search !== '') {

    $where .= "
        AND (
            nombre LIKE ?
            OR telefono LIKE ?
        )
    ";

    $buscar =
        '%' . $search . '%';

    $params[] =
        $buscar;

    $params[] =
        $buscar;

    $types .= 'ss';
}


$sql = "
    SELECT
        id,
        nombre,
        telefono

    FROM clientes

    $where

    ORDER BY nombre ASC

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


while (
    $row = $result->fetch_assoc()
) {

    $rows[] = $row;
}


$more =
    count($rows) > $limit;


if ($more) {
    array_pop($rows);
}


$results = [];


foreach ($rows as $row) {

    $text =
        $row['nombre'];


    if (!empty($row['telefono'])) {

        $text .=
            ' - ' . $row['telefono'];
    }


    $results[] = [
        'id' => (int) $row['id'],
        'text' => $text,

        'nombre' =>
            $row['nombre'],

        'telefono' =>
            $row['telefono']
    ];
}


echo json_encode(
    [
        'results' => $results,

        'pagination' => [
            'more' => $more
        ]
    ],
    JSON_UNESCAPED_UNICODE
);