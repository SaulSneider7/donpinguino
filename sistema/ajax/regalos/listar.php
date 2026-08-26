<?php

session_start();

header('Content-Type: application/json; charset=utf-8');

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

    0 => 'r.id',
    1 => 'r.fecha',
    2 => 'r.tipo',
    3 => 'c.nombre',
    4 => 'r.descripcion',
    5 => 'cantidad_productos',
    6 => 'costo_total'

];


$orderColumn =
    $orderColumns[
        $orderColumnIndex
    ]
    ?? 'r.id';


$resultTotal =
    $conn->query(
        "
        SELECT COUNT(*) AS total
        FROM regalos
        "
    );


$total =
    (int)
    $resultTotal
        ->fetch_assoc()['total'];


$where = '';


$params = [];

$types = '';


if ($search !== '') {

    $where = "
        WHERE (
            r.tipo LIKE ?
            OR r.descripcion LIKE ?
            OR c.nombre LIKE ?
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


$sqlFiltered = "
    SELECT COUNT(*) AS total

    FROM regalos r

    LEFT JOIN clientes c
        ON c.id = r.cliente_id

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


$sql = "
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
                    SUM(dr.cantidad),
                    0
                )

            FROM detalle_regalo dr

            WHERE dr.regalo_id = r.id

        ) AS cantidad_productos,

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


while ($row = $result->fetch_assoc()) {

    $id =
        (int)
        $row['id'];


    switch ($row['tipo']) {

        case 'PREMIO':

            $tipoHtml = '
                <span class="badge text-bg-warning">
                    Premio
                </span>
            ';

            break;


        case 'CORTESIA':

            $tipoHtml = '
                <span class="badge text-bg-info">
                    Cortesía
                </span>
            ';

            break;


        case 'REGALO':

            $tipoHtml = '
                <span class="badge text-bg-success">
                    Regalo
                </span>
            ';

            break;


        default:

            $tipoHtml = '
                <span class="badge text-bg-secondary">
                    Otro
                </span>
            ';
    }


    $descripcion =
        htmlspecialchars(
            mb_strimwidth(
                $row['descripcion'],
                0,
                70,
                '...'
            )
        );


    $data[] = [

        'id' =>
            $id,

        'fecha' =>
            date(
                'd/m/Y H:i',
                strtotime(
                    $row['fecha']
                )
            ),

        'tipo' =>
            $tipoHtml,

        'cliente' =>
            !empty($row['cliente'])
                ? htmlspecialchars(
                    $row['cliente']
                )
                : '<span class="text-muted">Sin cliente</span>',

        'descripcion' =>
            $descripcion,

        'productos' =>
            number_format(
                (float)
                $row['cantidad_productos'],
                0
            ),

        'costo' => '
            <span class="fw-semibold">
                S/
                '
                . number_format(
                    (float)
                    $row['costo_total'],
                    2
                )
                . '
            </span>
        ',

        'acciones' => '

            <button
                type="button"
                class="btn btn-outline-primary btn-sm btn-ver-regalo"
                data-id="' . $id . '"
            >
                <i class="fa-solid fa-eye"></i>
            </button>

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