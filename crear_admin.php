<?php

require_once __DIR__ . '/config/database.php';

$nombre = 'Administrador';
$usuario = 'admin';
$password = '123456';

$passwordHash = password_hash($password, PASSWORD_DEFAULT);

$sql = "
    INSERT INTO usuarios (
        nombre,
        usuario,
        password_hash
    )
    VALUES (?, ?, ?)
";

$stmt = $conn->prepare($sql);
$stmt->bind_param(
    'sss',
    $nombre,
    $usuario,
    $passwordHash
);

if ($stmt->execute()) {
    echo 'Administrador creado correctamente.';
} else {
    echo 'Error: ' . $stmt->error;
}