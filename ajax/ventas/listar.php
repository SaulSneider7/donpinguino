<?php

session_start();

header(
    'Content-Type: application/json; charset=utf-8'
);

require_once __DIR__ . '/../../config/database.php';


if (!isset($_SESSION['usuario_id'])) {

    http_response_code(401);

    echo json_encode([
        'draw' => 0,
        'recordsTotal' => 0,
        'recordsFiltered' => 0,
        'data' => []
    ]);

    exit;
}


$draw =
    (int) ($_POST['draw'] ?? 0);


$start =
    max(
        0,
        (int) ($_POST['start'] ?? 0)
    );


$length =
    (int) ($_POST['length'] ?? 10);


if (
    $length <= 0
    ||
    $length > 100
) {
    $length = 10;
}


$search =
    trim(
        $_POST['search']['value']
        ?? ''
    );


$orderColumnIndex =
    (int) (
        $_POST['order'][0]['column']
        ?? 0
    );


$orderDir =
    strtolower(
        $_POST['order'][0]['dir']
        ?? 'desc'
    );


if (
    !in_array(
        $orderDir,
        ['asc', 'desc'],
        true
    )
) {
    $orderDir = 'desc';
}


$orderColumns = [

    0 => 'u.nombre',
    1 => 'v.fecha',
    2 => 'c.nombre',
    3 => 'v.total',
    4 => 'v.total_pagado',
    5 => 'v.saldo_pendiente',
    6 => 'v.estado_pago'

];


$orderColumn =
    $orderColumns[
        $orderColumnIndex
    ]
    ?? 'v.id';


/* ============================================================
   TOTAL
============================================================ */

$resultTotal =
    $conn->query(
        "
        SELECT COUNT(*) AS total
        FROM ventas
        "
    );


$total =
    (int) $resultTotal
        ->fetch_assoc()['total'];


/* ============================================================
   BÚSQUEDA
============================================================ */

$where = '';

$params = [];

$types = '';


if ($search !== '') {

    $where = "
        WHERE (
            u.nombre LIKE ?
            OR c.nombre LIKE ?
            OR v.estado_pago LIKE ?
        )
    ";


    $buscar =
        '%' . $search . '%';


    $params = [
        $buscar,
        $buscar,
        $buscar
    ];


    $types =
        'sss';
}

/* ============================================================
   TOTAL FILTRADO
============================================================ */

$sqlFiltered = "
    SELECT
        COUNT(*) AS total

    FROM ventas v

    LEFT JOIN clientes c
        ON c.id = v.cliente_id

    LEFT JOIN usuarios u
        ON u.id = v.usuario_id

    $where
";


$stmtFiltered =
    $conn->prepare(
        $sqlFiltered
    );


if ($types !== '') {

    $stmtFiltered->bind_param(
        $types,
        ...$params
    );
}


$stmtFiltered->execute();


$filtered =
    (int)
    $stmtFiltered
        ->get_result()
        ->fetch_assoc()['total'];


/* ============================================================
   DATA
============================================================ */

$sql = "
    SELECT
        v.id,
        v.fecha,

        v.total,
        v.total_pagado,
        v.saldo_pendiente,

        v.estado_pago,
        v.estado,

        c.nombre AS cliente,

        u.nombre AS usuario_nombre

    FROM ventas v

    LEFT JOIN clientes c
        ON c.id = v.cliente_id

    LEFT JOIN usuarios u
        ON u.id = v.usuario_id

    $where

    ORDER BY
        $orderColumn
        $orderDir

    LIMIT ?, ?
";


$paramsData =
    $params;


$paramsData[] =
    $start;


$paramsData[] =
    $length;


$typesData =
    $types . 'ii';


$stmt =
    $conn->prepare($sql);


$stmt->bind_param(
    $typesData,
    ...$paramsData
);


$stmt->execute();


$result =
    $stmt->get_result();


$data = [];


while (
    $row =
        $result->fetch_assoc()
) {

    $id =
        (int) $row['id'];


    $cliente =
        !empty($row['cliente'])
            ? htmlspecialchars(
                $row['cliente']
            )
            : '<span class="text-muted">Cliente ocasional</span>';


    $totalHtml = '
        <span class="fw-semibold">
            S/
            '
            . number_format(
                (float) $row['total'],
                2
            )
            . '
        </span>
    ';


    $pagadoHtml = '
        <span class="text-success fw-semibold">
            S/
            '
            . number_format(
                (float) $row['total_pagado'],
                2
            )
            . '
        </span>
    ';


    if (
        (float) $row['saldo_pendiente']
        > 0
    ) {

        $pendienteHtml = '
            <span class="text-danger fw-semibold">
                S/
                '
                . number_format(
                    (float) $row['saldo_pendiente'],
                    2
                )
                . '
            </span>
        ';

    } else {

        $pendienteHtml = '
            <span class="text-muted">
                S/ 0.00
            </span>
        ';
    }


    switch (
        $row['estado_pago']
    ) {

        case 'PAGADO':

            $estadoPago = '
                <span class="badge text-bg-success">
                    Pagado
                </span>
            ';

            break;


        case 'PARCIAL':

            $estadoPago = '
                <span class="badge text-bg-warning">
                    Parcial
                </span>
            ';

            break;


        default:

            $estadoPago = '
                <span class="badge text-bg-danger">
                    Pendiente
                </span>
            ';
    }


    if (
        $row['estado']
        === 'ANULADA'
    ) {

        $estadoPago .= '
            <span class="badge text-bg-secondary ms-1">
                Anulada
            </span>
        ';
    }


    $acciones = '

        <button
            type="button"
            class="btn btn-outline-primary btn-sm btn-ver-venta"
            data-id="' . $id . '"
            title="Ver detalle"
        >
            <i class="fa-solid fa-eye"></i>
        </button>

    ';


    $data[] = [

        'usuario' =>
            htmlspecialchars(
                $row['usuario_nombre']
            ),

        'fecha' =>
            date(
                'd/m/Y H:i',
                strtotime(
                    $row['fecha']
                )
            ),

        'cliente' =>
            $cliente,

        'total' =>
            $totalHtml,

        'pagado' =>
            $pagadoHtml,

        'pendiente' =>
            $pendienteHtml,

        'estado' =>
            $estadoPago,

        'acciones' =>
            $acciones

    ];
}


echo json_encode(
    [
        'draw' =>
            $draw,

        'recordsTotal' =>
            $total,

        'recordsFiltered' =>
            $filtered,

        'data' =>
            $data
    ],
    JSON_UNESCAPED_UNICODE
);