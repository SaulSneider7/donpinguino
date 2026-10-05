<?php

require_once __DIR__ . '/app.php';

// usuario emesmaco_don_pinguino
// password: vJ+3oI-qcIMFVB+-

$DB_HOST = '127.0.0.1';
$DB_USER = 'root';
$DB_PASS = '';
$DB_NAME = 'don_pinguino';

$conn = new mysqli(
    $DB_HOST,
    $DB_USER,
    $DB_PASS,
    $DB_NAME
);

if ($conn->connect_error) {
    die('Error de conexion a la base de datos.');
}

$conn->set_charset('utf8mb4');

// Zona horaria de Per煤 para esta conexi贸n MySQL
if (!$conn->query("SET time_zone = '-05:00'")) {
    die('No se pudo configurar la zona horaria de MySQL.');
}