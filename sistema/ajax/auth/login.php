<?php

session_start();

header('Content-Type: application/json; charset=utf-8');

require_once __DIR__ . '/../../config/database.php';


function responseJson(
    bool $success,
    string $message,
    array $extra = []
): void {

    echo json_encode(
        array_merge(
            [
                'success' => $success,
                'message' => $message
            ],
            $extra
        )
    );

    exit;
}


$usuario = trim($_POST['usuario'] ?? '');
$password = $_POST['password'] ?? '';


if ($usuario === '' || $password === '') {

    responseJson(
        false,
        'Ingrese usuario y contraseña.'
    );
}


$sql = "
    SELECT
        id,
        nombre,
        usuario,
        password_hash,
        activo
    FROM usuarios
    WHERE usuario = ?
    LIMIT 1
";

$stmt = $conn->prepare($sql);
$stmt->bind_param('s', $usuario);
$stmt->execute();

$result = $stmt->get_result();

$user = $result->fetch_assoc();


if (!$user) {

    responseJson(
        false,
        'Usuario o contraseña incorrectos.'
    );
}


if (!(bool) $user['activo']) {

    responseJson(
        false,
        'Este usuario se encuentra deshabilitado.'
    );
}


if (!password_verify($password, $user['password_hash'])) {

    responseJson(
        false,
        'Usuario o contraseña incorrectos.'
    );
}


session_regenerate_id(true);

$_SESSION['usuario_id'] = (int) $user['id'];
$_SESSION['usuario_nombre'] = $user['nombre'];
$_SESSION['usuario'] = $user['usuario'];


responseJson(
    true,
    'Sesión iniciada correctamente.'
);