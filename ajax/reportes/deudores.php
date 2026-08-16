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


/* ============================================================
   FECHAS
============================================================ */

$fechaInicio =
    $_POST['fecha_inicio']
    ?? '';

$fechaFin =
    $_POST['fecha_fin']
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

    echo json_encode([
        'draw' => 0,
        'recordsTotal' => 0,
        'recordsFiltered' => 0,
        'data' => []
    ]);

    exit;
}


$desde =
    $fechaInicio
    . ' 00:00:00';


$hasta =
    $fechaFin
    . ' 23:59:59';


/* ============================================================
   DATATABLE
============================================================ */

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
        ?? 4
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

    0 => 'c.nombre',
    1 => 'ventas_pendientes',
    2 => 'total_vendido',
    3 => 'total_pagado',
    4 => 'deuda_total',
    5 => 'ultima_venta'

];


$orderColumn =
    $orderColumns[
        $orderColumnIndex
    ]
    ?? 'deuda_total';


/* ============================================================
   BASE
============================================================ */

$whereSearch = '';


$params = [
    $desde,
    $hasta
];


$types =
    'ss';


if ($search !== '') {

    $whereSearch = "
        AND (
            c.nombre LIKE ?
            OR c.telefono LIKE ?
        )
    ";


    $buscar =
        '%' . $search . '%';


    $params[] =
        $buscar;

    $params[] =
        $buscar;


    $types .=
        'ss';
}


/* ============================================================
   TOTAL GENERAL
============================================================ */

$sqlTotal = "
    SELECT
        COUNT(
            DISTINCT cliente_id
        ) AS total

    FROM ventas

    WHERE
        estado = 'ACTIVA'
        AND cliente_id IS NOT NULL
        AND saldo_pendiente > 0
        AND fecha BETWEEN ? AND ?
";


$stmtTotal =
    $conn->prepare(
        $sqlTotal
    );


$stmtTotal->bind_param(
    'ss',
    $desde,
    $hasta
);


$stmtTotal->execute();


$total =
    (int)
    $stmtTotal
        ->get_result()
        ->fetch_assoc()['total'];


/* ============================================================
   TOTAL FILTRADO
============================================================ */

$sqlFiltered = "
    SELECT
        COUNT(
            DISTINCT c.id
        ) AS total

    FROM clientes c

    INNER JOIN ventas v
        ON v.cliente_id = c.id

    WHERE
        v.estado = 'ACTIVA'
        AND v.saldo_pendiente > 0
        AND v.fecha BETWEEN ? AND ?

        $whereSearch
";


$stmtFiltered =
    $conn->prepare(
        $sqlFiltered
    );


$stmtFiltered->bind_param(
    $types,
    ...$params
);


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
        c.id,
        c.nombre,
        c.telefono,

        COUNT(v.id)
            AS ventas_pendientes,

        SUM(v.total)
            AS total_vendido,

        SUM(v.total_pagado)
            AS total_pagado,

        SUM(v.saldo_pendiente)
            AS deuda_total,

        MAX(v.fecha)
            AS ultima_venta

    FROM clientes c

    INNER JOIN ventas v
        ON v.cliente_id = c.id

    WHERE
        v.estado = 'ACTIVA'
        AND v.saldo_pendiente > 0
        AND v.fecha BETWEEN ? AND ?

        $whereSearch

    GROUP BY
        c.id,
        c.nombre,
        c.telefono

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
    $conn->prepare(
        $sql
    );


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

    $clienteId =
        (int) $row['id'];


    $cliente = '

        <div class="fw-semibold">
            '
            . htmlspecialchars(
                $row['nombre']
            )
            . '
        </div>

    ';


    if (!empty($row['telefono'])) {

        $cliente .= '

            <small class="text-muted">
                '
                . htmlspecialchars(
                    $row['telefono']
                )
                . '
            </small>

        ';
    }


    $data[] = [

        'cliente' =>
            $cliente,

        'ventas_pendientes' => '

            <span class="badge text-bg-warning">
                '
                . (int)
                $row['ventas_pendientes']
                . '
            </span>

        ',

        'total_vendido' =>
            'S/ '
            . number_format(
                (float)
                $row['total_vendido'],
                2
            ),

        'pagado' => '

            <span class="text-success">
                S/
                '
                . number_format(
                    (float)
                    $row['total_pagado'],
                    2
                )
                . '
            </span>

        ',

        'deuda' => '

            <span class="text-danger fw-bold">
                S/
                '
                . number_format(
                    (float)
                    $row['deuda_total'],
                    2
                )
                . '
            </span>

        ',

        'ultima_venta' =>
            date(
                'd/m/Y H:i',
                strtotime(
                    $row['ultima_venta']
                )
            ),

        'acciones' => '

            <a
                href="'
                . BASE_URL
                . 'modules/clientes/index.php?deudas_cliente='
                . $clienteId
                . '"

                class="btn btn-outline-danger btn-sm"
            >

                <i class="fa-solid fa-hand-holding-dollar me-1"></i>
                Ver deudas

            </a>

        '
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