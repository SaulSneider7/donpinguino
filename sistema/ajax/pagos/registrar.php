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


$ventaId =
    (int) ($_POST['venta_id'] ?? 0);


$monto =
    round(
        (float) ($_POST['monto'] ?? 0),
        2
    );


$metodoPago =
    $_POST['metodo_pago']
    ?? 'YAPE';


$observacion =
    trim(
        $_POST['observacion'] ?? ''
    );


if ($ventaId <= 0) {

    responder(
        false,
        'Venta inválida.'
    );
}


if ($monto <= 0) {

    responder(
        false,
        'El monto debe ser mayor a cero.'
    );
}


if (
    !in_array(
        $metodoPago,
        [
            'EFECTIVO',
            'YAPE',
            'PLIN',
            'OTRO'
        ],
        true
    )
) {

    responder(
        false,
        'Método de pago inválido.'
    );
}


try {

    $conn->begin_transaction();


    // ========================================================
    // BLOQUEAR VENTA
    // ========================================================

    $sqlVenta = "
        SELECT
            id,
            cliente_id,
            total,
            total_pagado,
            saldo_pendiente,
            estado_pago,
            estado

        FROM ventas

        WHERE id = ?

        LIMIT 1

        FOR UPDATE
    ";


    $stmtVenta =
        $conn->prepare(
            $sqlVenta
        );


    $stmtVenta->bind_param(
        'i',
        $ventaId
    );


    $stmtVenta->execute();


    $venta =
        $stmtVenta
            ->get_result()
            ->fetch_assoc();


    if (!$venta) {

        throw new Exception(
            'Venta no encontrada.'
        );
    }


    if (
        $venta['estado']
        !== 'ACTIVA'
    ) {

        throw new Exception(
            'La venta se encuentra anulada.'
        );
    }


    $saldoActual =
        round(
            (float)
            $venta['saldo_pendiente'],
            2
        );


    if ($saldoActual <= 0) {

        throw new Exception(
            'La venta ya se encuentra pagada.'
        );
    }


    if ($monto > $saldoActual) {

        throw new Exception(
            'El monto supera la deuda pendiente de S/ '
            . number_format(
                $saldoActual,
                2
            )
            . '.'
        );
    }


    $clienteId =
        $venta['cliente_id']
        !== null
            ? (int) $venta['cliente_id']
            : null;


    if (!$clienteId) {

        throw new Exception(
            'La venta no tiene un cliente asociado.'
        );
    }


    // ========================================================
    // INSERTAR PAGO
    // ========================================================

    $sqlPago = "
        INSERT INTO pagos (
            venta_id,
            cliente_id,
            usuario_id,

            monto,
            metodo_pago,

            fecha,
            observacion,
            estado
        )
        VALUES (
            ?,
            ?,
            ?,

            ?,
            ?,

            NOW(),
            ?,
            'ACTIVO'
        )
    ";


    $stmtPago =
        $conn->prepare(
            $sqlPago
        );


    $stmtPago->bind_param(
        'iiidss',

        $ventaId,
        $clienteId,
        $usuarioId,

        $monto,
        $metodoPago,

        $observacion
    );


    if (!$stmtPago->execute()) {

        throw new Exception(
            'No se pudo registrar el pago: '
            . $stmtPago->error
        );
    }


    // ========================================================
    // ACTUALIZAR VENTA
    // ========================================================

    $nuevoPagado =
        round(
            (float)
            $venta['total_pagado']
            + $monto,
            2
        );


    $nuevoSaldo =
        round(
            $saldoActual
            - $monto,
            2
        );


    if ($nuevoSaldo <= 0) {

        $nuevoSaldo = 0;

        $estadoPago =
            'PAGADO';

    } else {

        $estadoPago =
            'PARCIAL';
    }


    $sqlActualizar = "
        UPDATE ventas

        SET
            total_pagado = ?,
            saldo_pendiente = ?,
            estado_pago = ?

        WHERE id = ?

        LIMIT 1
    ";


    $stmtActualizar =
        $conn->prepare(
            $sqlActualizar
        );


    $stmtActualizar->bind_param(
        'ddsi',

        $nuevoPagado,
        $nuevoSaldo,
        $estadoPago,
        $ventaId
    );


    if (!$stmtActualizar->execute()) {

        throw new Exception(
            'No se pudo actualizar la venta.'
        );
    }


    $conn->commit();


    responder(
        true,
        $nuevoSaldo > 0
            ? 'Pago registrado correctamente.'
            : 'Pago registrado. La venta quedó completamente pagada.',
        [
            'venta_id' =>
                $ventaId,

            'monto' =>
                $monto,

            'total_pagado' =>
                $nuevoPagado,

            'saldo_pendiente' =>
                $nuevoSaldo,

            'estado_pago' =>
                $estadoPago
        ]
    );


} catch (Throwable $e) {

    $conn->rollback();


    responder(
        false,
        $e->getMessage()
    );
}