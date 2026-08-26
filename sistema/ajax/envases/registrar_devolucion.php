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
    (int) (
        $_POST['cliente_id']
        ?? 0
    );


$tipoEnvaseId =
    (int) (
        $_POST['tipo_envase_id']
        ?? 0
    );


$cantidad =
    (float) (
        $_POST['cantidad']
        ?? 0
    );


$descripcion =
    trim(
        $_POST['descripcion']
        ?? ''
    );


if (
    $clienteId <= 0
    ||
    $tipoEnvaseId <= 0
) {

    responder(
        false,
        'Cliente o tipo de envase inválido.'
    );
}


if ($cantidad <= 0) {

    responder(
        false,
        'La cantidad debe ser mayor a cero.'
    );
}


try {

    $conn->begin_transaction();


    // ========================================================
    // OBTENER Y BLOQUEAR ÚLTIMO SALDO
    // ========================================================

    $sqlSaldo = "
        SELECT
            id,
            saldo_nuevo

        FROM movimientos_envases

        WHERE
            cliente_id = ?
            AND tipo_envase_id = ?

        ORDER BY id DESC

        LIMIT 1

        FOR UPDATE
    ";


    $stmtSaldo =
        $conn->prepare(
            $sqlSaldo
        );


    $stmtSaldo->bind_param(
        'ii',
        $clienteId,
        $tipoEnvaseId
    );


    $stmtSaldo->execute();


    $row =
        $stmtSaldo
            ->get_result()
            ->fetch_assoc();


    if (!$row) {

        throw new Exception(
            'No existe saldo de envases para este cliente.'
        );
    }


    $saldoAnterior =
        (float)
        $row['saldo_nuevo'];


    if ($saldoAnterior <= 0) {

        throw new Exception(
            'El cliente no tiene envases pendientes.'
        );
    }


    if ($cantidad > $saldoAnterior) {

        throw new Exception(
            'El cliente solo tiene '
            . number_format(
                $saldoAnterior,
                0
            )
            . ' envases pendientes.'
        );
    }


    $saldoNuevo =
        $saldoAnterior
        - $cantidad;


    // ========================================================
    // INSERT MOVIMIENTO
    // ========================================================

    if ($descripcion === '') {

        $descripcion =
            'Devolución de envases';
    }


    $sqlMovimiento = "
        INSERT INTO movimientos_envases (
            cliente_id,
            tipo_envase_id,

            venta_id,
            usuario_id,

            tipo_movimiento,

            cantidad,

            saldo_anterior,
            saldo_nuevo,

            descripcion,
            fecha
        )
        VALUES (
            ?,
            ?,

            NULL,
            ?,

            'DEVOLUCION',

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
        'iiiddds',

        $clienteId,
        $tipoEnvaseId,

        $usuarioId,

        $cantidad,

        $saldoAnterior,
        $saldoNuevo,

        $descripcion
    );


    if (
        !$stmtMovimiento->execute()
    ) {

        throw new Exception(
            'No se pudo registrar la devolución: '
            . $stmtMovimiento->error
        );
    }


    $conn->commit();


    responder(
        true,
        $saldoNuevo > 0
            ? 'Devolución registrada. Quedan '
                . number_format(
                    $saldoNuevo,
                    0
                )
                . ' envases pendientes.'
            : 'Devolución registrada. El cliente ya no debe envases.',
        [
            'saldo_anterior' =>
                $saldoAnterior,

            'cantidad_devuelta' =>
                $cantidad,

            'saldo_nuevo' =>
                $saldoNuevo
        ]
    );


} catch (Throwable $e) {

    $conn->rollback();


    responder(
        false,
        $e->getMessage()
    );
}