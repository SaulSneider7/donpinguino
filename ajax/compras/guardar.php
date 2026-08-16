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


$proveedorId =
    isset($_POST['proveedor_id'])
    && $_POST['proveedor_id'] !== ''
        ? (int) $_POST['proveedor_id']
        : null;


$descuento =
    round(
        max(
            0,
            (float) (
                $_POST['descuento']
                ?? 0
            )
        ),
        2
    );


$observacion =
    trim(
        $_POST['observacion']
        ?? ''
    );


$items =
    json_decode(
        $_POST['items']
        ?? '[]',
        true
    );


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


/*
 * Consolidamos productos duplicados por seguridad.
 */
$itemsConsolidados = [];


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


    $costo =
        round(
            (float) (
                $item['costo_unitario']
                ?? 0
            ),
            2
        );


    if (
        $productoId <= 0
        ||
        $cantidad <= 0
        ||
        $costo < 0
    ) {

        responder(
            false,
            'Hay productos con datos inválidos.'
        );
    }


    /*
     * Normalmente el frontend no manda duplicados.
     * Si ocurre, conservamos la última línea.
     */
    $itemsConsolidados[
        $productoId
    ] = [
        'producto_id' =>
            $productoId,

        'cantidad' =>
            $cantidad,

        'costo_unitario' =>
            $costo
    ];
}


$items =
    array_values(
        $itemsConsolidados
    );


try {

    $conn->begin_transaction();


    $productosPreparados = [];

    $subtotalCompra = 0;


    // ========================================================
    // PRODUCTOS + BLOQUEO
    // ========================================================

    foreach ($items as $item) {

        $productoId =
            $item['producto_id'];


        $cantidad =
            $item['cantidad'];


        $costoCompra =
            $item['costo_unitario'];


        $sqlProducto = "
            SELECT
                id,
                nombre,
                tipo_producto,
                maneja_stock,
                stock_actual,
                costo_referencia

            FROM productos

            WHERE
                id = ?
                AND activo = 1

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
                'Uno de los productos ya no existe o está desactivado.'
            );
        }


        if (
            $producto['tipo_producto']
            !== 'SIMPLE'
        ) {

            throw new Exception(
                $producto['nombre']
                . ' es un combo y no puede ingresarse directamente como compra.'
            );
        }


        if (
            (int)
            $producto['maneja_stock']
            !== 1
        ) {

            throw new Exception(
                $producto['nombre']
                . ' no maneja inventario.'
            );
        }


        $subtotalLinea =
            round(
                $cantidad
                *
                $costoCompra,
                2
            );


        $subtotalCompra +=
            $subtotalLinea;


        $productosPreparados[] = [

            'producto' =>
                $producto,

            'cantidad' =>
                $cantidad,

            'costo_compra' =>
                $costoCompra,

            'subtotal' =>
                $subtotalLinea
        ];
    }


    $subtotalCompra =
        round(
            $subtotalCompra,
            2
        );


    if (
        $descuento
        > $subtotalCompra
    ) {

        throw new Exception(
            'El descuento no puede superar el subtotal.'
        );
    }


    $totalCompra =
        round(
            $subtotalCompra
            - $descuento,
            2
        );


    // ========================================================
    // CABECERA COMPRA
    // ========================================================

    $sqlCompra = "
        INSERT INTO compras (
            proveedor_id,
            usuario_id,
            fecha,

            subtotal,
            descuento,
            total,

            observacion,
            estado
        )
        VALUES (
            ?,
            ?,
            NOW(),

            ?,
            ?,
            ?,

            ?,
            'ACTIVA'
        )
    ";


    $stmtCompra =
        $conn->prepare(
            $sqlCompra
        );


    $stmtCompra->bind_param(
        'iiddds',

        $proveedorId,
        $usuarioId,

        $subtotalCompra,
        $descuento,
        $totalCompra,

        $observacion
    );


    if (!$stmtCompra->execute()) {

        throw new Exception(
            'No se pudo crear la compra: '
            . $stmtCompra->error
        );
    }


    $compraId =
        $stmtCompra->insert_id;


    // ========================================================
    // DETALLE + STOCK
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


        $costoCompra =
            (float)
            $linea['costo_compra'];


        $subtotalLinea =
            (float)
            $linea['subtotal'];


        // ----------------------------------------------------
        // DETALLE COMPRA
        // ----------------------------------------------------

        $sqlDetalle = "
            INSERT INTO detalle_compra (
                compra_id,
                producto_id,

                cantidad,
                costo_unitario,
                subtotal
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

            $compraId,
            $productoId,

            $cantidad,
            $costoCompra,
            $subtotalLinea
        );


        if (!$stmtDetalle->execute()) {

            throw new Exception(
                'No se pudo registrar el detalle de compra: '
                . $stmtDetalle->error
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
            + $cantidad;


        // ----------------------------------------------------
        // COSTO PROMEDIO PONDERADO
        // ----------------------------------------------------

        $costoAnterior =
            (float)
            $producto['costo_referencia'];


        /*
         * Si no había stock, el nuevo costo será
         * directamente el costo de esta compra.
         */
        if (
            $stockNuevo > 0
        ) {

            if (
                $stockAnterior > 0
            ) {

                $nuevoCostoPromedio =
                    (
                        (
                            $stockAnterior
                            *
                            $costoAnterior
                        )
                        +
                        (
                            $cantidad
                            *
                            $costoCompra
                        )
                    )
                    /
                    $stockNuevo;

            } else {

                $nuevoCostoPromedio =
                    $costoCompra;
            }

        } else {

            $nuevoCostoPromedio =
                $costoCompra;
        }


        $nuevoCostoPromedio =
            round(
                $nuevoCostoPromedio,
                2
            );


        // ----------------------------------------------------
        // UPDATE PRODUCTO
        // ----------------------------------------------------

        $sqlUpdate = "
            UPDATE productos

            SET
                stock_actual = ?,
                costo_referencia = ?

            WHERE id = ?

            LIMIT 1
        ";


        $stmtUpdate =
            $conn->prepare(
                $sqlUpdate
            );


        $stmtUpdate->bind_param(
            'ddi',

            $stockNuevo,
            $nuevoCostoPromedio,
            $productoId
        );


        if (!$stmtUpdate->execute()) {

            throw new Exception(
                'No se pudo actualizar el stock de '
                . $producto['nombre']
                . '.'
            );
        }


        // ----------------------------------------------------
        // KARDEX
        // ----------------------------------------------------

        $referenciaTipo =
            'COMPRA';


        $descripcionMovimiento =
            'Compra #'
            . $compraId;


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

                'COMPRA',

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
            $compraId,

            $cantidad,

            $stockAnterior,
            $stockNuevo,

            $costoCompra,

            $descripcionMovimiento
        );


        if (
            !$stmtMovimiento->execute()
        ) {

            throw new Exception(
                'No se pudo registrar el movimiento de stock.'
            );
        }
    }


    $conn->commit();


    responder(
        true,
        'Compra registrada correctamente.',
        [
            'compra_id' =>
                $compraId,

            'subtotal' =>
                $subtotalCompra,

            'descuento' =>
                $descuento,

            'total' =>
                $totalCompra
        ]
    );


} catch (Throwable $e) {

    $conn->rollback();


    responder(
        false,
        $e->getMessage()
    );
}