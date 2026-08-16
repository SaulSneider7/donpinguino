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

    0 => 'p.id',
    1 => 'p.nombre',
    2 => 'p.precio_venta',
    3 => 'cantidad_componentes',
    4 => 'p.id',
    5 => 'p.activo'
];


$orderColumn =
    $orderColumns[$orderColumnIndex]
    ?? 'p.id';


$total =
    (int)
    $conn->query(
        "
        SELECT COUNT(*) AS total
        FROM productos
        WHERE tipo_producto = 'COMBO'
        "
    )
    ->fetch_assoc()['total'];


$where = "
    WHERE p.tipo_producto = 'COMBO'
";


$params = [];
$types = '';


if ($search !== '') {

    $where .= "
        AND (
            p.nombre LIKE ?
            OR p.presentacion LIKE ?
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

    FROM productos p

    $where
";


$stmtFiltered =
    $conn->prepare($sqlFiltered);


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
        p.id,
        p.nombre,
        p.presentacion,

        p.precio_regular,
        p.precio_venta,

        p.activo,

        cb.id AS combo_id,

        (
            SELECT COUNT(*)

            FROM combo_componentes cc

            WHERE cc.combo_id = cb.id

        ) AS cantidad_componentes,

        (
            SELECT
                FLOOR(
                    MIN(
                        pc.stock_actual
                        /
                        cc.cantidad
                    )
                )

            FROM combo_componentes cc

            INNER JOIN productos pc
                ON pc.id = cc.producto_id

            WHERE cc.combo_id = cb.id

        ) AS combos_disponibles

    FROM productos p

    LEFT JOIN combos cb
        ON cb.producto_id = p.id

    $where

    ORDER BY
        $orderColumn
        $orderDir

    LIMIT ?, ?
";


$paramsData = $params;

$paramsData[] = $start;
$paramsData[] = $length;

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
        (int) $row['id'];


    $componentes =
        (int)
        $row['cantidad_componentes'];


    $combo = '

        <div class="fw-semibold">
            '
            . htmlspecialchars(
                $row['nombre']
            )
            . '
        </div>

    ';


    if (!empty($row['presentacion'])) {

        $combo .= '

            <small class="text-muted">
                '
                . htmlspecialchars(
                    $row['presentacion']
                )
                . '
            </small>

        ';
    }


    $precio = '

        <span class="fw-semibold">
            S/
            '
            . number_format(
                (float)
                $row['precio_venta'],
                2
            )
            . '
        </span>

    ';


    if ($componentes === 0) {

        $disponibles = '

            <span class="badge text-bg-warning">
                Sin configurar
            </span>

        ';

    } else {

        $cantidadDisponible =
            max(
                0,
                (int)
                $row['combos_disponibles']
            );


        if ($cantidadDisponible <= 0) {

            $disponibles = '

                <span class="badge text-bg-danger">
                    Agotado
                </span>

            ';

        } else {

            $disponibles = '

                <span class="fw-bold">
                    '
                    . $cantidadDisponible
                    . '
                </span>

            ';
        }
    }


    $estado =
        (int) $row['activo'] === 1

            ? '
                <span class="badge text-bg-success">
                    Activo
                </span>
            '

            : '
                <span class="badge text-bg-secondary">
                    Inactivo
                </span>
            ';


    $acciones = '

        <a
            href="'
            . BASE_URL
            . 'modules/combos/configurar.php?producto_id='
            . $id
            . '"

            class="btn btn-outline-primary btn-sm"
        >
            <i class="fa-solid fa-gears me-1"></i>
            Configurar
        </a>

    ';


    $data[] = [

        'id' =>
            $id,

        'combo' =>
            $combo,

        'precio' =>
            $precio,

        'componentes' => '

            <span class="badge text-bg-light border">
                '
                . $componentes
                . '
            </span>

        ',

        'disponibles' =>
            $disponibles,

        'estado' =>
            $estado,

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