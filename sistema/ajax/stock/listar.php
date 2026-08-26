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
        ?? 'asc'
    );


if (
    !in_array(
        $orderDir,
        ['asc', 'desc'],
        true
    )
) {
    $orderDir = 'asc';
}


$orderColumns = [

    0 => 'p.nombre',
    1 => 'c.nombre',
    2 => 'p.stock_actual',
    3 => 'p.stock_minimo',
    4 => 'p.stock_actual'

];


$orderColumn =
    $orderColumns[
        $orderColumnIndex
    ]
    ?? 'p.nombre';


$baseWhere = "
    WHERE
        p.activo = 1
        AND p.maneja_stock = 1
        AND p.tipo_producto = 'SIMPLE'
";


$params = [];

$types = '';


if ($search !== '') {

    $baseWhere .= "
        AND (
            p.nombre LIKE ?
            OR p.presentacion LIKE ?
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


/* ============================================================
   TOTAL
============================================================ */

$sqlTotal = "
    SELECT COUNT(*) AS total

    FROM productos p

    WHERE
        p.activo = 1
        AND p.maneja_stock = 1
        AND p.tipo_producto = 'SIMPLE'
";


$total =
    (int)
    $conn
        ->query($sqlTotal)
        ->fetch_assoc()['total'];


/* ============================================================
   TOTAL FILTRADO
============================================================ */

$sqlFiltered = "
    SELECT COUNT(*) AS total

    FROM productos p

    LEFT JOIN categorias c
        ON c.id = p.categoria_id

    $baseWhere
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
        p.id,
        p.nombre,
        p.presentacion,

        p.stock_actual,
        p.stock_minimo,

        c.nombre AS categoria

    FROM productos p

    LEFT JOIN categorias c
        ON c.id = p.categoria_id

    $baseWhere

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
        (int) $row['id'];


    $stockActual =
        (float) $row['stock_actual'];


    $stockMinimo =
        (float) $row['stock_minimo'];


    $producto = '

        <div class="fw-semibold">
            '
            . htmlspecialchars(
                $row['nombre']
            )
            . '
        </div>

    ';


    if (!empty($row['presentacion'])) {

        $producto .= '

            <small class="text-muted">
                '
                . htmlspecialchars(
                    $row['presentacion']
                )
                . '
            </small>

        ';
    }


    if ($stockActual <= 0) {

        $estado = '

            <span class="badge text-bg-danger">
                Agotado
            </span>

        ';

    } elseif (
        $stockActual
        <=
        $stockMinimo
    ) {

        $estado = '

            <span class="badge text-bg-warning">
                Stock bajo
            </span>

        ';

    } else {

        $estado = '

            <span class="badge text-bg-success">
                Disponible
            </span>

        ';
    }


    $acciones = '

        <div class="btn-group btn-group-sm">

            <button
                type="button"
                class="btn btn-outline-primary btn-kardex"
                data-id="' . $id . '"
                data-nombre="' .
                    htmlspecialchars(
                        $row['nombre'],
                        ENT_QUOTES
                    )
                . '"
                title="Ver Kardex"
            >
                <i class="fa-solid fa-clock-rotate-left"></i>
            </button>


            <button
                type="button"
                class="btn btn-outline-dark btn-ajustar-stock"
                data-id="' . $id . '"
                title="Ajustar stock"
            >
                <i class="fa-solid fa-sliders"></i>
            </button>

        </div>

    ';


    $data[] = [

        'producto' =>
            $producto,

        'categoria' =>
            htmlspecialchars(
                $row['categoria']
                ?? 'Sin categoría'
            ),

        'stock_actual' => '

            <span class="fw-bold fs-6">
                '
                . number_format(
                    $stockActual,
                    3
                )
                . '
            </span>

        ',

        'stock_minimo' =>
            number_format(
                $stockMinimo,
                3
            ),

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