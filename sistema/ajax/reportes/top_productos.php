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
   AÑO ACTUAL
============================================================ */

$anio =
    (int) date('Y');


/* ============================================================
   MES
============================================================ */

$mes =
    (int) (
        $_GET['mes']
        ?? date('n')
    );


if (
    $mes < 1
    ||
    $mes > 12
) {

    responder(
        false,
        'Mes inválido.'
    );
}


/* ============================================================
   TOP PRODUCTOS
============================================================ */

/*
 * Importante:
 *
 * Obtenemos el nombre desde productos.
 *
 * Así evitamos problemas si el nombre de la columna
 * snapshot de detalle_venta es diferente.
 */

$sql = "
    SELECT

        p.id AS producto_id,

        p.nombre AS producto,

        COALESCE(
            SUM(d.cantidad),
            0
        ) AS cantidad_vendida,

        COALESCE(
            SUM(d.subtotal_final),
            0
        ) AS importe_vendido

    FROM detalle_venta d

    INNER JOIN ventas v
        ON v.id = d.venta_id

    INNER JOIN productos p
        ON p.id = d.producto_id

    WHERE
        v.estado = 'ACTIVA'

        AND YEAR(v.fecha) = ?

        AND MONTH(v.fecha) = ?

    GROUP BY
        p.id,
        p.nombre

    ORDER BY
        cantidad_vendida DESC,
        importe_vendido DESC

    LIMIT 5
";


$stmt =
    $conn->prepare(
        $sql
    );


if (!$stmt) {

    responder(
        false,
        'Error preparando consulta: '
        . $conn->error
    );
}


$stmt->bind_param(
    'ii',
    $anio,
    $mes
);


$stmt->execute();


$result =
    $stmt->get_result();


$data = [];


while (
    $row =
        $result->fetch_assoc()
) {

    $data[] = [

        'producto_id' =>
            (int)
            $row['producto_id'],

        'producto' =>
            $row['producto'],

        'cantidad' =>
            round(
                (float)
                $row['cantidad_vendida'],
                2
            ),

        'importe' =>
            round(
                (float)
                $row['importe_vendido'],
                2
            )

    ];
}


responder(
    true,
    'Productos obtenidos correctamente.',
    [

        'anio' =>
            $anio,

        'mes' =>
            $mes,

        'data' =>
            $data

    ]
);