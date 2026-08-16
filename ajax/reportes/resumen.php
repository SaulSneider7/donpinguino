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


if (!isset($_SESSION['usuario_id'])) {

    http_response_code(401);

    responder(
        false,
        'Sesión expirada.'
    );
}


$fechaInicio =
    $_GET['fecha_inicio']
    ?? '';


$fechaFin =
    $_GET['fecha_fin']
    ?? '';


if (
    !preg_match(
        '/^\d{4}-\d{2}-\d{2}$/',
        $fechaInicio
    )
    ||
    !preg_match(
        '/^\d{4}-\d{2}-\d{2}$/',
        $fechaFin
    )
) {

    responder(
        false,
        'Fechas inválidas.'
    );
}


if (
    $fechaFin
    <
    $fechaInicio
) {

    responder(
        false,
        'Rango de fechas inválido.'
    );
}


$desde =
    $fechaInicio
    . ' 00:00:00';


$hasta =
    $fechaFin
    . ' 23:59:59';


/* ============================================================
   VENTAS DEL PERÍODO
============================================================ */

$sql = "
    SELECT
        COUNT(*) AS cantidad,

        COALESCE(
            SUM(total),
            0
        ) AS total,

        COALESCE(
            SUM(saldo_pendiente),
            0
        ) AS pendiente,

        COALESCE(
            AVG(total),
            0
        ) AS ticket

    FROM ventas

    WHERE
        estado = 'ACTIVA'
        AND fecha BETWEEN ? AND ?
";


$stmt =
    $conn->prepare($sql);


$stmt->bind_param(
    'ss',
    $desde,
    $hasta
);


$stmt->execute();


$ventas =
    $stmt
        ->get_result()
        ->fetch_assoc();


/* ============================================================
   COSTO + UTILIDAD
============================================================ */

$sql = "
    SELECT
        COALESCE(
            SUM(
                dv.costo_unitario
                *
                dv.cantidad
            ),
            0
        ) AS costo,

        COALESCE(
            SUM(
                dv.subtotal_final
                -
                (
                    dv.costo_unitario
                    *
                    dv.cantidad
                )
            ),
            0
        ) AS utilidad

    FROM detalle_venta dv

    INNER JOIN ventas v
        ON v.id = dv.venta_id

    WHERE
        v.estado = 'ACTIVA'
        AND v.fecha BETWEEN ? AND ?
";


$stmt =
    $conn->prepare($sql);


$stmt->bind_param(
    'ss',
    $desde,
    $hasta
);


$stmt->execute();


$rentabilidad =
    $stmt
        ->get_result()
        ->fetch_assoc();


/* ============================================================
   DINERO COBRADO EN EL PERÍODO
============================================================ */

/*
 * Importante:
 *
 * Esto usa fecha del PAGO.
 *
 * Si una venta fue hace una semana pero el cliente
 * paga hoy, ese dinero aparece como cobrado hoy.
 */

$sql = "
    SELECT
        COALESCE(
            SUM(monto),
            0
        ) AS total

    FROM pagos

    WHERE
        estado = 'ACTIVO'
        AND fecha BETWEEN ? AND ?
";


$stmt =
    $conn->prepare($sql);


$stmt->bind_param(
    'ss',
    $desde,
    $hasta
);


$stmt->execute();


$cobrado =
    (float)
    $stmt
        ->get_result()
        ->fetch_assoc()['total'];


/* ============================================================
   COMPRAS
============================================================ */

$sql = "
    SELECT
        COUNT(*) AS cantidad,

        COALESCE(
            SUM(total),
            0
        ) AS total

    FROM compras

    WHERE
        estado = 'ACTIVA'
        AND fecha BETWEEN ? AND ?
";


$stmt =
    $conn->prepare($sql);


$stmt->bind_param(
    'ss',
    $desde,
    $hasta
);


$stmt->execute();


$compras =
    $stmt
        ->get_result()
        ->fetch_assoc();


/* ============================================================
   REGALOS
============================================================ */

$sql = "
    SELECT
        COALESCE(
            SUM(dr.costo_total),
            0
        ) AS total

    FROM regalos r

    INNER JOIN detalle_regalo dr
        ON dr.regalo_id = r.id

    WHERE
        r.estado = 'ACTIVO'
        AND r.fecha BETWEEN ? AND ?
";


$stmt =
    $conn->prepare($sql);


$stmt->bind_param(
    'ss',
    $desde,
    $hasta
);


$stmt->execute();


$regalos =
    (float)
    $stmt
        ->get_result()
        ->fetch_assoc()['total'];


/* ============================================================
   RESPONSE
============================================================ */

responder(
    true,
    'Reporte generado.',
    [
        'ventas' =>
            round(
                (float)
                $ventas['total'],
                2
            ),

        'cantidad_ventas' =>
            (int)
            $ventas['cantidad'],

        'cobrado' =>
            round(
                $cobrado,
                2
            ),

        'pendiente' =>
            round(
                (float)
                $ventas['pendiente'],
                2
            ),

        'ticket_promedio' =>
            round(
                (float)
                $ventas['ticket'],
                2
            ),

        'costo_vendido' =>
            round(
                (float)
                $rentabilidad['costo'],
                2
            ),

        'utilidad' =>
            round(
                (float)
                $rentabilidad['utilidad'],
                2
            ),

        'compras' =>
            round(
                (float)
                $compras['total'],
                2
            ),

        'cantidad_compras' =>
            (int)
            $compras['cantidad'],

        'regalos' =>
            round(
                $regalos,
                2
            )
    ]
);