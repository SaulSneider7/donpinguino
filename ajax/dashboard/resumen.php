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


/* ============================================================
   FECHAS
============================================================ */

$hoy =
    date('Y-m-d');

$inicioHoy =
    $hoy . ' 00:00:00';

$finHoy =
    $hoy . ' 23:59:59';

$inicioMes =
    date('Y-m-01') . ' 00:00:00';

$finMes =
    date('Y-m-t') . ' 23:59:59';


/* ============================================================
   VENTAS DE HOY
============================================================ */

$sql = "
    SELECT
        COUNT(*) AS cantidad,
        COALESCE(SUM(total), 0) AS total

    FROM ventas

    WHERE
        estado = 'ACTIVA'
        AND fecha BETWEEN ? AND ?
";


$stmt =
    $conn->prepare($sql);


$stmt->bind_param(
    'ss',
    $inicioHoy,
    $finHoy
);


$stmt->execute();


$rowVentas =
    $stmt
        ->get_result()
        ->fetch_assoc();


$ventasHoyCantidad =
    (int) $rowVentas['cantidad'];

$ventasHoy =
    round(
        (float) $rowVentas['total'],
        2
    );


/* ============================================================
   UTILIDAD BRUTA DE HOY
============================================================ */

/*
 * Utilidad:
 *
 * ingreso final de cada línea
 * -
 * costo histórico × cantidad
 *
 * Esto funciona también para combos porque
 * detalle_venta.costo_unitario contiene el costo
 * de los componentes al momento de vender.
 */

$sql = "
    SELECT
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
    $inicioHoy,
    $finHoy
);


$stmt->execute();


$utilidadHoy =
    round(
        (float)
        $stmt
            ->get_result()
            ->fetch_assoc()['utilidad'],
        2
    );


/* ============================================================
   DEUDA MONETARIA TOTAL
============================================================ */

$sql = "
    SELECT
        COUNT(
            DISTINCT cliente_id
        ) AS clientes,

        COALESCE(
            SUM(saldo_pendiente),
            0
        ) AS deuda

    FROM ventas

    WHERE
        estado = 'ACTIVA'
        AND saldo_pendiente > 0
";


$rowDeuda =
    $conn
        ->query($sql)
        ->fetch_assoc();


$clientesDeudores =
    (int) $rowDeuda['clientes'];

$deudaTotal =
    round(
        (float) $rowDeuda['deuda'],
        2
    );



/* ============================================================
   CLIENTES CON DEUDA
============================================================ */

$sql = "
    SELECT
        c.id AS cliente_id,
        c.nombre,

        COUNT(v.id) AS ventas_pendientes,

        COALESCE(
            SUM(v.saldo_pendiente),
            0
        ) AS deuda_total

    FROM clientes c

    INNER JOIN ventas v
        ON v.cliente_id = c.id

    WHERE
        v.estado = 'ACTIVA'
        AND v.saldo_pendiente > 0

    GROUP BY
        c.id,
        c.nombre

    ORDER BY
        deuda_total DESC,
        c.nombre ASC

    LIMIT 20
";


$result =
    $conn->query($sql);


$listaDeudores = [];


while (
    $row = $result->fetch_assoc()
) {

    $listaDeudores[] = [

        'cliente_id' =>
            (int) $row['cliente_id'],

        'nombre' =>
            $row['nombre'],

        'ventas_pendientes' =>
            (int) $row['ventas_pendientes'],

        'deuda_total' =>
            round(
                (float)
                $row['deuda_total'],
                2
            )
    ];
}


/* ============================================================
   ENVASES PENDIENTES
============================================================ */

/*
 * Tomamos únicamente el ÚLTIMO movimiento
 * de cada cliente + tipo de envase.
 */

$sql = "
    SELECT
        COALESCE(
            SUM(me.saldo_nuevo),
            0
        ) AS total_envases,

        COUNT(*) AS cuentas_pendientes

    FROM movimientos_envases me

    INNER JOIN (
        SELECT
            cliente_id,
            tipo_envase_id,
            MAX(id) AS ultimo_id

        FROM movimientos_envases

        GROUP BY
            cliente_id,
            tipo_envase_id
    ) ult
        ON ult.ultimo_id = me.id

    WHERE me.saldo_nuevo > 0
";


$rowEnvases =
    $conn
        ->query($sql)
        ->fetch_assoc();


$envasesPendientes =
    (float)
    $rowEnvases['total_envases'];


$cuentasEnvases =
    (int)
    $rowEnvases['cuentas_pendientes'];


/* ============================================================
   PRODUCTOS CON STOCK BAJO
============================================================ */

$sql = "
    SELECT COUNT(*) AS total

    FROM productos

    WHERE
        activo = 1
        AND maneja_stock = 1
        AND tipo_producto = 'SIMPLE'

        AND stock_actual <= stock_minimo
";


$stockBajo =
    (int)
    $conn
        ->query($sql)
        ->fetch_assoc()['total'];


/* ============================================================
   COMPRAS DE HOY
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
    $inicioHoy,
    $finHoy
);


$stmt->execute();


$rowCompras =
    $stmt
        ->get_result()
        ->fetch_assoc();


$comprasHoyCantidad =
    (int)
    $rowCompras['cantidad'];


$comprasHoy =
    round(
        (float)
        $rowCompras['total'],
        2
    );


/* ============================================================
   REGALOS / PREMIOS DEL MES
============================================================ */

$sql = "
    SELECT
        COUNT(
            DISTINCT r.id
        ) AS cantidad,

        COALESCE(
            SUM(dr.costo_total),
            0
        ) AS costo

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
    $inicioMes,
    $finMes
);


$stmt->execute();


$rowRegalos =
    $stmt
        ->get_result()
        ->fetch_assoc();


$regalosMesCantidad =
    (int)
    $rowRegalos['cantidad'];


$regalosMesCosto =
    round(
        (float)
        $rowRegalos['costo'],
        2
    );



/* ============================================================
   GASTOS DEL MES
============================================================ */

$sqlGastos = "
    SELECT

        COUNT(*) AS cantidad,

        COALESCE(
            SUM(monto),
            0
        ) AS total

    FROM gastos

    WHERE
        activo = 1

        AND YEAR(fecha) = YEAR(CURDATE())

        AND MONTH(fecha) = MONTH(CURDATE())
";


$resultGastos =
    $conn->query(
        $sqlGastos
    );


$gastosMes =
    $resultGastos
        ->fetch_assoc();


$totalGastosMes =
    round(
        (float)
        $gastosMes['total'],
        2
    );


$cantidadGastosMes =
    (int)
    $gastosMes['cantidad'];




/* ============================================================
   ÚLTIMAS VENTAS
============================================================ */

$sql = "
    SELECT
        v.id,
        v.fecha,
        v.total,
        v.saldo_pendiente,
        v.estado_pago,

        c.nombre AS cliente

    FROM ventas v

    LEFT JOIN clientes c
        ON c.id = v.cliente_id

    WHERE v.estado = 'ACTIVA'

    ORDER BY
        v.fecha DESC,
        v.id DESC

    LIMIT 5
";


$result =
    $conn->query($sql);


$ultimasVentas = [];


while (
    $row = $result->fetch_assoc()
) {

    $ultimasVentas[] = [

        'id' =>
            (int) $row['id'],

        'fecha' =>
            $row['fecha'],

        'fecha_formateada' =>
            date(
                'd/m/Y H:i',
                strtotime(
                    $row['fecha']
                )
            ),

        'cliente' =>
            $row['cliente']
            ?: 'Cliente ocasional',

        'total' =>
            (float) $row['total'],

        'saldo_pendiente' =>
            (float)
            $row['saldo_pendiente'],

        'estado_pago' =>
            $row['estado_pago']
    ];
}


/* ============================================================
   PRODUCTOS MÁS VENDIDOS DEL MES
============================================================ */

$sql = "
    SELECT
        dv.producto_id,
        dv.nombre_producto,

        SUM(
            dv.cantidad
        ) AS cantidad,

        SUM(
            dv.subtotal_final
        ) AS total_vendido

    FROM detalle_venta dv

    INNER JOIN ventas v
        ON v.id = dv.venta_id

    WHERE
        v.estado = 'ACTIVA'
        AND v.fecha BETWEEN ? AND ?

    GROUP BY
        dv.producto_id,
        dv.nombre_producto

    ORDER BY
        cantidad DESC,
        total_vendido DESC

    LIMIT 5
";


$stmt =
    $conn->prepare($sql);


$stmt->bind_param(
    'ss',
    $inicioMes,
    $finMes
);


$stmt->execute();


$result =
    $stmt->get_result();


$productosMasVendidos = [];


while (
    $row = $result->fetch_assoc()
) {

    $productosMasVendidos[] = [

        'producto_id' =>
            $row['producto_id']
                !== null
                    ? (int)
                        $row['producto_id']
                    : null,

        'nombre' =>
            $row['nombre_producto'],

        'cantidad' =>
            (float)
            $row['cantidad'],

        'total_vendido' =>
            round(
                (float)
                $row['total_vendido'],
                2
            )
    ];
}


/* ============================================================
   RESPUESTA
============================================================ */

responder(
    true,
    'Dashboard obtenido.',
    [
        'ventas_hoy' => [
            'cantidad' =>
                $ventasHoyCantidad,

            'total' =>
                $ventasHoy
        ],

        'utilidad_hoy' =>
            $utilidadHoy,

        'deudas' => [
            'clientes' =>
                $clientesDeudores,

            'total' =>
                $deudaTotal,
            
            'lista' =>
                $listaDeudores
        ],

        'envases' => [
            'cantidad' =>
                $envasesPendientes,

            'cuentas' =>
                $cuentasEnvases
        ],

        'stock_bajo' =>
            $stockBajo,

        'compras_hoy' => [
            'cantidad' =>
                $comprasHoyCantidad,

            'total' =>
                $comprasHoy
        ],

        'regalos_mes' => [
            'cantidad' =>
                $regalosMesCantidad,

            'costo' =>
                $regalosMesCosto
        ],

        'gastos_mes' => [

            'total' =>
                $totalGastosMes,

            'cantidad' =>
                $cantidadGastosMes

        ],

        'ultimas_ventas' =>
            $ultimasVentas,

        'productos_mas_vendidos' =>
            $productosMasVendidos
    ]
);