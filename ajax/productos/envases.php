<?php

session_start();

header('Content-Type: application/json; charset=utf-8');

require_once __DIR__ . '/../../config/database.php';


if (!isset($_SESSION['usuario_id'])) {
    http_response_code(401);
    echo json_encode([]);
    exit;
}


$sql = "
    SELECT
        id,
        nombre
    FROM tipos_envase
    WHERE activo = 1
    ORDER BY nombre ASC
";


$result = $conn->query($sql);

$data = [];


while ($row = $result->fetch_assoc()) {

    $data[] = [
        'id' => (int) $row['id'],
        'text' => $row['nombre']
    ];

}


echo json_encode($data);