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