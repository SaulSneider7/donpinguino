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


$productoId =
    (int) (
        $_GET['producto_id']
        ?? 0
    );


if ($productoId <= 0) {

    responder(
        false,
        'Combo inválido.'
    );
}


$stmt =
    $conn->prepare(
        "
        SELECT
            id,
            nombre,
            presentacion,
            precio_regular,
            precio_venta,
            activo

        FROM productos

        WHERE
            id = ?
            AND tipo_producto = 'COMBO'

        LIMIT 1
        "
    );


$stmt->bind_param(
    'i',
    $productoId
);


$stmt->execute();


$combo =
    $stmt
        ->get_result()
        ->fetch_assoc();


if (!$combo) {

    responder(
        false,
        'Producto combo no encontrado.'
    );
}


$stmtCombo =
    $conn->prepare(
        "
        SELECT id

        FROM combos

        WHERE producto_id = ?

        LIMIT 1
        "
    );


$stmtCombo->bind_param(
    'i',
    $productoId
);


$stmtCombo->execute();


$rowCombo =
    $stmtCombo
        ->get_result()
        ->fetch_assoc();


$componentes = [];


if ($rowCombo) {

    $comboId =
        (int) $rowCombo['id'];


    $stmtComponentes =
        $conn->prepare(
            "
            SELECT
                cc.producto_id,
                cc.cantidad,

                p.nombre,
                p.presentacion,
                p.costo_referencia,
                p.stock_actual

            FROM combo_componentes cc

            INNER JOIN productos p
                ON p.id = cc.producto_id

            WHERE cc.combo_id = ?

            ORDER BY cc.id ASC
            "
        );


    $stmtComponentes->bind_param(
        'i',
        $comboId
    );


    $stmtComponentes->execute();


    $componentes =
        $stmtComponentes
            ->get_result()
            ->fetch_all(
                MYSQLI_ASSOC
            );
}


responder(
    true,
    'Combo encontrado.',
    [
        'combo' =>
            $combo,

        'componentes' =>
            $componentes
    ]
);