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

    0 => 'co.id',
    1 => 'co.fecha',
    2 => 'pr.nombre',
    3 => 'cantidad_productos',
    4 => 'co.total',
    5 => 'co.estado'

];


$orderColumn =
    $orderColumns[
        $orderColumnIndex
    ]
    ?? 'co.id';


$resultTotal =
    $conn->query(
        "
        SELECT COUNT(*) AS total
        FROM compras
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
            CAST(co.id AS CHAR) LIKE ?
            OR pr.nombre LIKE ?
        )
    ";


    $buscar =
        '%' . $search . '%';


    $params = [
        $buscar,
        $buscar
    ];


    $types = 'ss';
}


$sqlFiltered = "
    SELECT COUNT(*) AS total

    FROM compras co

    LEFT JOIN proveedores pr
        ON pr.id = co.proveedor_id

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
        co.id,
        co.fecha,
        co.total,
        co.estado,

        pr.nombre AS proveedor,

        (
            SELECT COUNT(*)

            FROM detalle_compra dc

            WHERE dc.compra_id = co.id
        ) AS cantidad_productos

    FROM compras co

    LEFT JOIN proveedores pr
        ON pr.id = co.proveedor_id

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


    $estado =
        $row['estado']
        === 'ACTIVA'
            ? '
                <span class="badge text-bg-success">
                    Activa
                </span>
            '
            : '
                <span class="badge text-bg-secondary">
                    Anulada
                </span>
            ';


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

        'proveedor' =>
            !empty($row['proveedor'])
                ? htmlspecialchars(
                    $row['proveedor']
                )
                : '<span class="text-muted">Sin proveedor</span>',

        'productos' =>
            (int)
            $row['cantidad_productos'],

        'total' => '
            <span class="fw-semibold">
                S/
                '
                . number_format(
                    (float)
                    $row['total'],
                    2
                )
                . '
            </span>
        ',

        'estado' =>
            $estado,

        'acciones' => '

            <button
                type="button"
                class="btn btn-outline-primary btn-sm btn-ver-compra"
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