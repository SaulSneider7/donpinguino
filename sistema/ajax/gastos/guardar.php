<?php

session_start();

header(
    'Content-Type: application/json; charset=utf-8'
);

require_once __DIR__ . '/../../config/database.php';


function responder(
    bool $success,
    string $message,
    array $extra = []
): void {

    echo json_encode(
        array_merge(
            [
                'success' =>
                    $success,

                'message' =>
                    $message
            ],
            $extra
        ),
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


$usuarioId =
    (int)
    $_SESSION['usuario_id'];


$id =
    isset($_POST['id'])
    &&
    $_POST['id'] !== ''
        ? (int)
            $_POST['id']
        : null;


$tipo =
    $_POST['tipo']
    ?? 'OTRO';


$descripcion =
    trim(
        $_POST['descripcion']
        ?? ''
    );


$monto =
    round(
        (float) (
            $_POST['monto']
            ?? 0
        ),
        2
    );


$fecha =
    $_POST['fecha']
    ?? '';


$observacion =
    trim(
        $_POST['observacion']
        ?? ''
    );


$tiposPermitidos = [

    'AGUA',
    'LUZ',
    'COMIDA',
    'INSUMOS',
    'DELIVERY',
    'ALQUILER',
    'OTRO'

];


/* ============================================================
   VALIDACIONES
============================================================ */

if (
    !in_array(
        $tipo,
        $tiposPermitidos,
        true
    )
) {

    responder(
        false,
        'Tipo de gasto inválido.'
    );
}


if ($descripcion === '') {

    responder(
        false,
        'Ingrese la descripción del gasto.'
    );
}


if ($monto <= 0) {

    responder(
        false,
        'El monto debe ser mayor a cero.'
    );
}


if (
    !preg_match(
        '/^\d{4}-\d{2}-\d{2}$/',
        $fecha
    )
) {

    responder(
        false,
        'Fecha inválida.'
    );
}


/* ============================================================
   INSERT
============================================================ */

if ($id === null) {

    $sql = "
        INSERT INTO gastos (
            usuario_id,

            tipo,
            descripcion,

            monto,
            fecha,

            observacion,

            activo
        )
        VALUES (
            ?,

            ?,
            ?,

            ?,
            ?,

            ?,

            1
        )
    ";


    $stmt =
        $conn->prepare(
            $sql
        );


    $stmt->bind_param(
        'issdss',

        $usuarioId,

        $tipo,
        $descripcion,

        $monto,
        $fecha,

        $observacion
    );


    if (
        !$stmt->execute()
    ) {

        responder(
            false,
            'No se pudo registrar el gasto: '
            . $stmt->error
        );
    }


    responder(
        true,
        'Gasto registrado correctamente.',
        [
            'id' =>
                $stmt->insert_id
        ]
    );
}


/* ============================================================
   UPDATE
============================================================ */

$sql = "
    UPDATE gastos

    SET
        tipo = ?,
        descripcion = ?,

        monto = ?,
        fecha = ?,

        observacion = ?

    WHERE id = ?

    LIMIT 1
";


$stmt =
    $conn->prepare(
        $sql
    );


$stmt->bind_param(
    'ssdssi',

    $tipo,
    $descripcion,

    $monto,
    $fecha,

    $observacion,

    $id
);


if (
    !$stmt->execute()
) {

    responder(
        false,
        'No se pudo actualizar el gasto: '
        . $stmt->error
    );
}


responder(
    true,
    'Gasto actualizado correctamente.'
);