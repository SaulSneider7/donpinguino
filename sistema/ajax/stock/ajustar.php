<?php

session_start();

header('Content-Type: application/json; charset=utf-8');

require_once __DIR__ . '/../../config/database.php';


function responder(
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
    (int) $_SESSION['usuario_id'];


$productoId =
    (int) (
        $_POST['producto_id']
        ?? 0
    );


$tipoAjuste =
    $_POST['tipo_ajuste']
    ?? '';


$cantidad =
    (float) (
        $_POST['cantidad']
        ?? 0
    );


$motivo =
    trim(
        $_POST['motivo']
        ?? ''
    );


$descripcion =
    trim(
        $_POST['descripcion']
        ?? ''
    );


if ($productoId <= 0) {

    responder(
        false,
        'Producto inválido.'
    );
}


if (
    !in_array(
        $tipoAjuste,
        [
            'ENTRADA',
            'SALIDA'
        ],
        true
    )
) {

    responder(
        false,
        'Tipo de ajuste inválido.'
    );
}


if ($cantidad <= 0) {

    responder(
        false,
        'La cantidad debe ser mayor a cero.'
    );
}


if ($motivo === '') {

    responder(
        false,
        'Seleccione el motivo del ajuste.'
    );
}


try {

    $conn->begin_transaction();


    // ========================================================
    // BLOQUEAR PRODUCTO
    // ========================================================

    $sqlProducto = "
        SELECT
            id,
            nombre,
            stock_actual,
            costo_referencia,
            maneja_stock,
            tipo_producto

        FROM productos

        WHERE id = ?

        LIMIT 1

        FOR UPDATE
    ";


    $stmtProducto =
        $conn->prepare(
            $sqlProducto
        );


    $stmtProducto->bind_param(
        'i',
        $productoId
    );


    $stmtProducto->execute();


    $producto =
        $stmtProducto
            ->get_result()
            ->fetch_assoc();


    if (!$producto) {

        throw new Exception(
            'Producto no encontrado.'
        );
    }


    if (
        (int)
        $producto['maneja_stock']
        !== 1
    ) {

        throw new Exception(
            'El producto no maneja stock.'
        );
    }


    if (
        $producto['tipo_producto']
        !== 'SIMPLE'
    ) {

        throw new Exception(
            'El stock de un combo no se ajusta directamente.'
        );
    }


    $stockAnterior =
        (float)
        $producto['stock_actual'];


    // ========================================================
    // CALCULAR NUEVO STOCK
    // ========================================================

    if (
        $tipoAjuste
        === 'ENTRADA'
    ) {

        $cantidadMovimiento =
            $cantidad;


        $stockNuevo =
            $stockAnterior
            + $cantidad;


        $tipoMovimiento =
            'AJUSTE_ENTRADA';

    } else {

        if (
            $cantidad
            > $stockAnterior
        ) {

            throw new Exception(
                'No puede retirar '
                . number_format(
                    $cantidad,
                    3
                )
                . ' unidades. El stock actual es '
                . number_format(
                    $stockAnterior,
                    3
                )
                . '.'
            );
        }


        $cantidadMovimiento =
            -$cantidad;


        $stockNuevo =
            $stockAnterior
            - $cantidad;


        $tipoMovimiento =
            'AJUSTE_SALIDA';
    }


    // ========================================================
    // UPDATE STOCK
    // ========================================================

    $sqlUpdate = "
        UPDATE productos

        SET stock_actual = ?

        WHERE id = ?

        LIMIT 1
    ";


    $stmtUpdate =
        $conn->prepare(
            $sqlUpdate
        );


    $stmtUpdate->bind_param(
        'di',

        $stockNuevo,
        $productoId
    );


    if (!$stmtUpdate->execute()) {

        throw new Exception(
            'No se pudo actualizar el stock.'
        );
    }


    // ========================================================
    // DESCRIPCIÓN
    // ========================================================

    $descripcionMovimiento =
        $motivo;


    if ($descripcion !== '') {

        $descripcionMovimiento .=
            ' - '
            . $descripcion;
    }


    $referenciaTipo =
        'AJUSTE';


    $costoReferencia =
        (float)
        $producto['costo_referencia'];


    // ========================================================
    // KARDEX
    // ========================================================

    $sqlMovimiento = "
        INSERT INTO movimientos_stock (
            producto_id,
            usuario_id,

            tipo_movimiento,

            referencia_tipo,
            referencia_id,

            cantidad,

            stock_anterior,
            stock_nuevo,

            costo_unitario,

            descripcion,
            fecha
        )
        VALUES (
            ?,
            ?,

            ?,

            ?,
            NULL,

            ?,

            ?,
            ?,

            ?,

            ?,
            NOW()
        )
    ";


    $stmtMovimiento =
        $conn->prepare(
            $sqlMovimiento
        );


    $stmtMovimiento->bind_param(
        'iissdddds',

        $productoId,
        $usuarioId,

        $tipoMovimiento,

        $referenciaTipo,

        $cantidadMovimiento,

        $stockAnterior,
        $stockNuevo,

        $costoReferencia,

        $descripcionMovimiento
    );


    if (
        !$stmtMovimiento->execute()
    ) {

        throw new Exception(
            'No se pudo registrar el movimiento de stock: '
            . $stmtMovimiento->error
        );
    }


    $conn->commit();


    responder(
        true,
        'Stock actualizado de '
        . number_format(
            $stockAnterior,
            3
        )
        . ' a '
        . number_format(
            $stockNuevo,
            3
        )
        . '.',
        [
            'stock_anterior' =>
                $stockAnterior,

            'stock_nuevo' =>
                $stockNuevo
        ]
    );


} catch (Throwable $e) {

    $conn->rollback();


    responder(
        false,
        $e->getMessage()
    );
}