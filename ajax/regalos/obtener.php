<?php

session_start();

header('Content-Type: application/json; charset=utf-8');

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


$id =
    (int) ($_GET['id'] ?? 0);


if ($id <= 0) {

    responder(
        false,
        'Registro inválido.'
    );
}


$sqlRegistro = "
    SELECT
        r.id,
        r.fecha,
        r.tipo,
        r.descripcion,
        r.estado,

        c.nombre AS cliente,

        (
            SELECT
                COALESCE(
                    SUM(dr.costo_total),
                    0
                )

            FROM detalle_regalo dr

            WHERE dr.regalo_id = r.id

        ) AS costo_total

    FROM regalos r

    LEFT JOIN clientes c
        ON c.id = r.cliente_id

    WHERE r.id = ?

    LIMIT 1
";


$stmtRegistro =
    $conn->prepare(
        $sqlRegistro
    );


$stmtRegistro->bind_param(
    'i',
    $id
);


$stmtRegistro->execute();


$registro =
    $stmtRegistro
        ->get_result()
        ->fetch_assoc();


if (!$registro) {

    responder(
        false,
        'Registro no encontrado.'
    );
}


$registro['cliente'] =
    $registro['cliente']
    ?: 'Sin cliente';


$registro['fecha_formateada'] =
    date(
        'd/m/Y H:i',
        strtotime(
            $registro['fecha']
        )
    );


$sqlProductos = "
    SELECT
        dr.producto_id,
        dr.cantidad,
        dr.costo_unitario,
        dr.costo_total,

        p.nombre,
        p.presentacion

    FROM detalle_regalo dr

    INNER JOIN productos p
        ON p.id = dr.producto_id

    WHERE dr.regalo_id = ?

    ORDER BY dr.id ASC
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
    $row =
        $resultProductos
            ->fetch_assoc()
) {

    $productos[] =
        $row;
}


responder(
    true,
    'Registro encontrado.',
    [
        'registro' =>
            $registro,

        'productos' =>
            $productos
    ]
);