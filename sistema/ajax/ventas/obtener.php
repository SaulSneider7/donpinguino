<?php

session_start();

header(
    'Content-Type: application/json; charset=utf-8'
);

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


/* ============================================================
   SESIÓN
============================================================ */

if (!isset($_SESSION['usuario_id'])) {

    http_response_code(401);

    responder(
        false,
        'Sesión expirada.'
    );
}


/* ============================================================
   INPUT
============================================================ */

$id =
    (int) ($_GET['id'] ?? 0);


if ($id <= 0) {

    responder(
        false,
        'Venta inválida.'
    );
}


/* ============================================================
   VENTA
============================================================ */

$sqlVenta = "
    SELECT
        v.id,
        v.fecha,

        v.subtotal,
        v.descuento_promociones,
        v.descuento_manual,

        v.total,
        v.total_pagado,
        v.saldo_pendiente,

        v.estado_pago,
        v.estado,
        v.observacion,

        c.id AS cliente_id,
        c.nombre AS cliente_nombre,

        u.nombre AS usuario_nombre

    FROM ventas v

    LEFT JOIN clientes c
        ON c.id = v.cliente_id

    INNER JOIN usuarios u
        ON u.id = v.usuario_id

    WHERE v.id = ?

    LIMIT 1
";


$stmtVenta =
    $conn->prepare(
        $sqlVenta
    );


$stmtVenta->bind_param(
    'i',
    $id
);


$stmtVenta->execute();


$venta =
    $stmtVenta
        ->get_result()
        ->fetch_assoc();


if (!$venta) {

    responder(
        false,
        'Venta no encontrada.'
    );
}


/* ============================================================
   NORMALIZAR DATOS DE VENTA
============================================================ */

$venta['cliente'] =
    $venta['cliente_nombre']
    ?: 'Cliente ocasional';


$venta['fecha_formateada'] =
    date(
        'd/m/Y H:i',
        strtotime(
            $venta['fecha']
        )
    );


$clienteId =
    $venta['cliente_id'] !== null
        ? (int) $venta['cliente_id']
        : null;


/* ============================================================
   PRODUCTOS
============================================================ */

$sqlProductos = "
    SELECT
        dv.id,
        dv.producto_id,

        dv.nombre_producto,
        dv.presentacion_producto,

        dv.cantidad,

        dv.costo_unitario,

        dv.precio_regular,
        dv.precio_venta_base,

        dv.subtotal_base,

        dv.descuento_promocion,
        dv.descuento_manual,

        dv.subtotal_final,

        dv.promocion_id,
        dv.promocion_nombre,

        dv.detalle_precio_json

    FROM detalle_venta dv

    WHERE dv.venta_id = ?

    ORDER BY dv.id ASC
";


$stmtProductos =
    $conn->prepare(
        $sqlProductos
    );


$stmtProductos->bind_param(
    'i',
    $id
);


$stmtProductos->execute();


$resultProductos =
    $stmtProductos
        ->get_result();


$productos = [];


while (
    $producto =
        $resultProductos
            ->fetch_assoc()
) {

    if (
        !empty(
            $producto[
                'detalle_precio_json'
            ]
        )
    ) {

        $detallePrecio =
            json_decode(
                $producto[
                    'detalle_precio_json'
                ],
                true
            );


        $producto[
            'detalle_precio'
        ] =
            is_array($detallePrecio)
                ? $detallePrecio
                : [];

    } else {

        $producto[
            'detalle_precio'
        ] = [];
    }


    $productos[] =
        $producto;
}


/* ============================================================
   PAGOS
============================================================ */

$sqlPagos = "
    SELECT
        p.id,
        p.monto,
        p.metodo_pago,
        p.fecha,
        p.observacion,
        p.estado

    FROM pagos p

    WHERE
        p.venta_id = ?
        AND p.estado = 'ACTIVO'

    ORDER BY
        p.fecha ASC,
        p.id ASC
";


$stmtPagos =
    $conn->prepare(
        $sqlPagos
    );


$stmtPagos->bind_param(
    'i',
    $id
);


$stmtPagos->execute();


$resultPagos =
    $stmtPagos
        ->get_result();


$pagos = [];


while (
    $pago =
        $resultPagos
            ->fetch_assoc()
) {

    $pago[
        'fecha_formateada'
    ] =
        date(
            'd/m/Y H:i',
            strtotime(
                $pago['fecha']
            )
        );


    $pagos[] =
        $pago;
}


/* ============================================================
   ENVASES HISTÓRICOS DE LA VENTA
============================================================ */

$sqlEnvases = "
    SELECT
        ve.id,
        ve.tipo_envase_id,

        te.nombre AS tipo_envase,

        ve.cantidad_requerida,
        ve.cantidad_entregada,
        ve.cantidad_pendiente

    FROM venta_envases ve

    INNER JOIN tipos_envase te
        ON te.id = ve.tipo_envase_id

    WHERE ve.venta_id = ?

    ORDER BY te.nombre ASC
";


$stmtEnvases =
    $conn->prepare(
        $sqlEnvases
    );


$stmtEnvases->bind_param(
    'i',
    $id
);


$stmtEnvases->execute();


$resultEnvases =
    $stmtEnvases
        ->get_result();


$envases = [];


/* ============================================================
   PREPARAR CONSULTA PARA SALDO ACTUAL DEL CLIENTE
============================================================ */

/*
 * Importante:
 *
 * venta_envases representa lo que ocurrió
 * AL MOMENTO DE ESTA VENTA.
 *
 * movimientos_envases representa la deuda
 * ACTUAL del cliente.
 *
 * No modificamos cantidad_pendiente de venta_envases
 * cuando el cliente devuelve botellas posteriormente.
 */

$stmtSaldoEnvase = null;


if ($clienteId) {

    $sqlSaldoEnvase = "
        SELECT
            me.saldo_nuevo,
            me.fecha

        FROM movimientos_envases me

        WHERE
            me.cliente_id = ?
            AND me.tipo_envase_id = ?

        ORDER BY me.id DESC

        LIMIT 1
    ";


    $stmtSaldoEnvase =
        $conn->prepare(
            $sqlSaldoEnvase
        );
}


/* ============================================================
   ENVASES + SALDO ACTUAL
============================================================ */

while (
    $envase =
        $resultEnvases
            ->fetch_assoc()
) {

    /*
     * Por defecto, si no hay cliente asociado
     * o no existen movimientos posteriores.
     */
    $envase[
        'saldo_actual_cliente'
    ] = 0;


    $envase[
        'saldo_actual_fecha'
    ] = null;


    if (
        $clienteId
        &&
        $stmtSaldoEnvase
    ) {

        $tipoEnvaseId =
            (int)
            $envase[
                'tipo_envase_id'
            ];


        $stmtSaldoEnvase
            ->bind_param(
                'ii',
                $clienteId,
                $tipoEnvaseId
            );


        $stmtSaldoEnvase
            ->execute();


        $saldoRow =
            $stmtSaldoEnvase
                ->get_result()
                ->fetch_assoc();


        if ($saldoRow) {

            $envase[
                'saldo_actual_cliente'
            ] =
                (float)
                $saldoRow[
                    'saldo_nuevo'
                ];


            $envase[
                'saldo_actual_fecha'
            ] =
                $saldoRow['fecha'];


            $envase[
                'saldo_actual_fecha_formateada'
            ] =
                date(
                    'd/m/Y H:i',
                    strtotime(
                        $saldoRow['fecha']
                    )
                );

        } else {

            $envase[
                'saldo_actual_fecha_formateada'
            ] = null;
        }

    } else {

        $envase[
            'saldo_actual_fecha_formateada'
        ] = null;
    }


    /*
     * Campo útil para frontend.
     *
     * Esto NO significa que la deuda de esta
     * venta específicamente se haya pagado.
     *
     * Solo indica si actualmente el cliente
     * tiene saldo global de ese tipo de envase.
     */
    $envase[
        'tiene_deuda_actual'
    ] =
        (
            (float)
            $envase[
                'saldo_actual_cliente'
            ]
            >
            0
        );


    $envases[] =
        $envase;
}


/* ============================================================
   RESPUESTA
============================================================ */

responder(
    true,
    'Venta encontrada.',
    [
        'venta' =>
            $venta,

        'productos' =>
            $productos,

        'pagos' =>
            $pagos,

        'envases' =>
            $envases
    ]
);