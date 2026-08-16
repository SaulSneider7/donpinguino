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


$fechaInicio =
    $_POST['fecha_inicio']
    ?? '';

$fechaFin =
    $_POST['fecha_fin']
    ?? '';


$desde =
    $fechaInicio
    . ' 00:00:00';

$hasta =
    $fechaFin
    . ' 23:59:59';


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
        ?? 2
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
    1 => 'cantidad_ventas',
    2 => 'total_consumido',
    3 => 'ticket_promedio',
    4 => 'deuda_actual',
    5 => 'ultima_compra'

];


$orderColumn =
    $orderColumns[
        $orderColumnIndex
    ]
    ?? 'total_consumido';


/* ============================================================
   BASE
============================================================ */

$base = "
    FROM clientes c

    INNER JOIN ventas v
        ON v.cliente_id = c.id

    WHERE
        v.estado = 'ACTIVA'
        AND v.fecha BETWEEN ? AND ?
";


$paramsBase = [
    $desde,
    $hasta
];


$typesBase =
    'ss';


/* ============================================================
   SEARCH
============================================================ */

$whereSearch = '';


if ($search !== '') {

    $whereSearch = "
        AND (
            c.nombre LIKE ?
            OR c.telefono LIKE ?
        )
    ";


    $buscar =
        '%' . $search . '%';


    $paramsBase[] =
        $buscar;

    $paramsBase[] =
        $buscar;


    $typesBase .=
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

    $base

    $whereSearch
";


$stmtFiltered =
    $conn->prepare(
        $sqlFiltered
    );


$stmtFiltered->bind_param(
    $typesBase,
    ...$paramsBase
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
            AS cantidad_ventas,

        SUM(v.total)
            AS total_consumido,

        AVG(v.total)
            AS ticket_promedio,

        MAX(v.fecha)
            AS ultima_compra,

        (
            SELECT
                COALESCE(
                    SUM(vd.saldo_pendiente),
                    0
                )

            FROM ventas vd

            WHERE
                vd.cliente_id = c.id
                AND vd.estado = 'ACTIVA'
                AND vd.saldo_pendiente > 0

        ) AS deuda_actual

    $base

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
    $paramsBase;


$paramsData[] =
    $start;

$paramsData[] =
    $length;


$typesData =
    $typesBase . 'ii';


$stmt =
    $conn->prepare($sql);


$stmt->bind_param(
    $typesData,
    ...$paramsData
);


$stmt->execute();


$result =
    $stmt
        ->get_result();


$data = [];


while (
    $row =
        $result->fetch_assoc()
) {

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


    $deuda =
        (float)
        $row['deuda_actual'];


    $data[] = [

        'cliente' =>
            $cliente,

        'compras' =>
            (int)
            $row['cantidad_ventas'],

        'consumo' => '

            <span class="fw-semibold">
                S/
                '
                . number_format(
                    (float)
                    $row['total_consumido'],
                    2
                )
                . '
            </span>

        ',

        'ticket' =>
            'S/ '
            . number_format(
                (float)
                $row['ticket_promedio'],
                2
            ),

        'deuda' =>
            $deuda > 0

                ? '
                    <span class="text-danger fw-semibold">
                        S/
                        '
                        . number_format(
                            $deuda,
                            2
                        )
                        . '
                    </span>
                '

                : '
                    <span class="text-success">
                        S/ 0.00
                    </span>
                ',

        'ultima_compra' =>
            date(
                'd/m/Y H:i',
                strtotime(
                    $row['ultima_compra']
                )
            )
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