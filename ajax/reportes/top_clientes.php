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


$anio =
    (int) date('Y');


/* ============================================================
   TOP 5 CLIENTES
============================================================ */

$sql = "
    SELECT

        c.id AS cliente_id,

        c.nombre AS cliente,

        COUNT(v.id) AS compras,

        COALESCE(
            SUM(v.total),
            0
        ) AS consumo

    FROM clientes c

    INNER JOIN ventas v
        ON v.cliente_id = c.id

    WHERE
        v.estado = 'ACTIVA'

        AND YEAR(v.fecha) = ?

    GROUP BY
        c.id,
        c.nombre

    ORDER BY
        consumo DESC,
        compras DESC

    LIMIT 5
";


$stmt =
    $conn->prepare(
        $sql
    );


if (!$stmt) {

    responder(
        false,
        'No se pudo preparar la consulta: '
        . $conn->error
    );
}


$stmt->bind_param(
    'i',
    $anio
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

        'cliente_id' =>
            (int)
            $row['cliente_id'],

        'cliente' =>
            $row['cliente'],

        'compras' =>
            (int)
            $row['compras'],

        'consumo' =>
            round(
                (float)
                $row['consumo'],
                2
            )

    ];
}


responder(
    true,
    'Clientes obtenidos correctamente.',
    [

        'anio' =>
            $anio,

        'data' =>
            $data

    ]
);