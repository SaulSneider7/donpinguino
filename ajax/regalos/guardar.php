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
    (int)
    $_SESSION['usuario_id'];


$clienteId =
    isset($_POST['cliente_id'])
    && $_POST['cliente_id'] !== ''
        ? (int) $_POST['cliente_id']
        : null;


$tipo =
    $_POST['tipo']
    ?? 'REGALO';


$descripcion =
    trim(
        $_POST['descripcion']
        ?? ''
    );


$items =
    json_decode(
        $_POST['items']
        ?? '[]',
        true
    );


if (
    !in_array(
        $tipo,
        [
            'REGALO',
            'PREMIO',
            'CORTESIA',
            'OTRO'
        ],
        true
    )
) {

    responder(
        false,
        'Tipo inválido.'
    );
}


if ($descripcion === '') {

    responder(
        false,
        'Ingrese una descripción.'
    );
}


if (
    !is_array($items)
    ||
    count($items) === 0
) {

    responder(
        false,
        'Agregue al menos un producto.'
    );
}


try {

    $conn->begin_transaction();


    $productosPreparados = [];

    $costoTotalGeneral = 0;


    // ========================================================
    // VALIDAR Y BLOQUEAR PRODUCTOS
    // ========================================================

    foreach ($items as $item) {

        $productoId =
            (int) (
                $item['producto_id']
                ?? 0
            );


        $cantidad =
            (float) (
                $item['cantidad']
                ?? 0
            );


        if (
            $productoId <= 0
            ||
            $cantidad <= 0
        ) {

            throw new Exception(
                'Producto o cantidad inválidos.'
            );
        }


        $sql = "
            SELECT
                id,
                nombre,
                stock_actual,
                costo_referencia,
                maneja_stock,
                tipo_producto

            FROM productos

            WHERE
                id = ?
                AND activo = 1

            LIMIT 1

            FOR UPDATE
        ";


        $stmt =
            $conn->prepare($sql);


        $stmt->bind_param(
            'i',
            $productoId
        );


        $stmt->execute();


        $producto =
            $stmt
                ->get_result()
                ->fetch_assoc();


        if (!$producto) {

            throw new Exception(
                'Uno de los productos ya no está disponible.'
            );
        }


        if (
            $producto['tipo_producto']
            !== 'SIMPLE'
        ) {

            throw new Exception(
                $producto['nombre']
                . ' es un combo. Los combos se manejarán mediante sus componentes.'
            );
        }


        if (
            (int)
            $producto['maneja_stock']
            !== 1
        ) {

            throw new Exception(
                $producto['nombre']
                . ' no maneja stock.'
            );
        }


        $stockActual =
            (float)
            $producto['stock_actual'];


        if (
            $stockActual
            < $cantidad
        ) {

            throw new Exception(
                'Stock insuficiente para '
                . $producto['nombre']
                . '. Disponible: '
                . number_format(
                    $stockActual,
                    3
                )
                . '.'
            );
        }


        $costoUnitario =
            (float)
            $producto['costo_referencia'];


        $costoTotal =
            round(
                $cantidad
                *
                $costoUnitario,
                2
            );


        $costoTotalGeneral +=
            $costoTotal;


        $productosPreparados[] = [

            'producto' =>
                $producto,

            'cantidad' =>
                $cantidad,

            'costo_unitario' =>
                $costoUnitario,

            'costo_total' =>
                $costoTotal
        ];
    }


    $costoTotalGeneral =
        round(
            $costoTotalGeneral,
            2
        );


    // ========================================================
    // CABECERA
    // ========================================================

    $sqlRegalo = "
        INSERT INTO regalos (
            usuario_id,
            cliente_id,

            tipo,
            descripcion,

            fecha,
            estado
        )
        VALUES (
            ?,
            ?,

            ?,
            ?,

            NOW(),
            'ACTIVO'
        )
    ";


    $stmtRegalo =
        $conn->prepare(
            $sqlRegalo
        );


    $stmtRegalo->bind_param(
        'iiss',

        $usuarioId,
        $clienteId,

        $tipo,
        $descripcion
    );


    if (!$stmtRegalo->execute()) {

        throw new Exception(
            'No se pudo crear el registro: '
            . $stmtRegalo->error
        );
    }


    $regaloId =
        $stmtRegalo->insert_id;


    // ========================================================
    // DETALLE + STOCK + KARDEX
    // ========================================================

    foreach (
        $productosPreparados
        as $linea
    ) {

        $producto =
            $linea['producto'];


        $productoId =
            (int)
            $producto['id'];


        $cantidad =
            (float)
            $linea['cantidad'];


        $costoUnitario =
            (float)
            $linea['costo_unitario'];


        $costoTotal =
            (float)
            $linea['costo_total'];


        // ----------------------------------------------------
        // DETALLE
        // ----------------------------------------------------

        $sqlDetalle = "
            INSERT INTO detalle_regalo (
                regalo_id,
                producto_id,

                cantidad,

                costo_unitario,
                costo_total
            )
            VALUES (
                ?,
                ?,

                ?,

                ?,
                ?
            )
        ";


        $stmtDetalle =
            $conn->prepare(
                $sqlDetalle
            );


        $stmtDetalle->bind_param(
            'iiddd',

            $regaloId,
            $productoId,

            $cantidad,

            $costoUnitario,
            $costoTotal
        );


        if (!$stmtDetalle->execute()) {

            throw new Exception(
                'No se pudo registrar el detalle.'
            );
        }


        // ----------------------------------------------------
        // STOCK
        // ----------------------------------------------------

        $stockAnterior =
            (float)
            $producto['stock_actual'];


        $stockNuevo =
            $stockAnterior
            - $cantidad;


        $sqlStock = "
            UPDATE productos

            SET stock_actual = ?

            WHERE id = ?

            LIMIT 1
        ";


        $stmtStock =
            $conn->prepare(
                $sqlStock
            );


        $stmtStock->bind_param(
            'di',
            $stockNuevo,
            $productoId
        );


        if (!$stmtStock->execute()) {

            throw new Exception(
                'No se pudo actualizar stock.'
            );
        }


        // ----------------------------------------------------
        // KARDEX
        // ----------------------------------------------------

        $cantidadMovimiento =
            -$cantidad;


        $referenciaTipo =
            'REGALO';


        $descripcionMovimiento =
            $tipo
            . ' #'
            . $regaloId
            . ' - '
            . $descripcion;


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

                'REGALO',

                ?,
                ?,

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
            'iisidddds',

            $productoId,
            $usuarioId,

            $referenciaTipo,
            $regaloId,

            $cantidadMovimiento,

            $stockAnterior,
            $stockNuevo,

            $costoUnitario,

            $descripcionMovimiento
        );


        if (!$stmtMovimiento->execute()) {

            throw new Exception(
                'No se pudo registrar el movimiento de stock.'
            );
        }
    }


    $conn->commit();


    responder(
        true,
        'Registro guardado correctamente.',
        [
            'regalo_id' =>
                $regaloId,

            'costo_total' =>
                $costoTotalGeneral
        ]
    );


} catch (Throwable $e) {

    $conn->rollback();


    responder(
        false,
        $e->getMessage()
    );
}