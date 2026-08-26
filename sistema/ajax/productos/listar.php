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


if ($length <= 0 || $length > 100) {
    $length = 10;
}


$search =
    trim(
        $_POST['search']['value'] ?? ''
    );


$orderColumnIndex =
    (int) ($_POST['order'][0]['column'] ?? 0);

$orderDir =
    strtolower(
        $_POST['order'][0]['dir'] ?? 'desc'
    );


if (!in_array($orderDir, ['asc', 'desc'], true)) {
    $orderDir = 'desc';
}


// ============================================================
// COLUMNAS PERMITIDAS PARA ORDER BY
// ============================================================

$orderColumns = [

    0 => 'p.id',
    1 => 'p.nombre',
    2 => 'c.nombre',
    3 => 'p.precio_venta',
    4 => 'p.stock_actual',
    5 => 'p.publicar_catalogo',
    6 => 'p.activo'

];


$orderColumn =
    $orderColumns[$orderColumnIndex]
    ?? 'p.id';


// ============================================================
// TOTAL SIN FILTRO
// ============================================================

$resultTotal = $conn->query(
    "
        SELECT COUNT(*) AS total
        FROM productos
    "
);


$total =
    (int) $resultTotal
        ->fetch_assoc()['total'];


// ============================================================
// WHERE DE BÚSQUEDA
// ============================================================

$where = '';

$params = [];

$types = '';


if ($search !== '') {

    $where = "
        WHERE (
            p.nombre LIKE ?
            OR p.presentacion LIKE ?
            OR c.nombre LIKE ?
        )
    ";

    $buscar = '%' . $search . '%';

    $params = [
        $buscar,
        $buscar,
        $buscar
    ];

    $types = 'sss';

}


// ============================================================
// TOTAL FILTRADO
// ============================================================

$sqlFiltered = "
    SELECT COUNT(*) AS total

    FROM productos p

    LEFT JOIN categorias c
        ON c.id = p.categoria_id

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
    (int) $stmtFiltered
        ->get_result()
        ->fetch_assoc()['total'];


// ============================================================
// DATA
// ============================================================

$sql = "
    SELECT
        p.id,
        p.nombre,
        p.presentacion,

        c.nombre AS categoria,

        p.precio_regular,
        p.precio_venta,

        p.maneja_stock,
        p.stock_actual,
        p.stock_minimo,

        p.publicar_catalogo,
        p.destacado_catalogo,

        p.activo

    FROM productos p

    LEFT JOIN categorias c
        ON c.id = p.categoria_id

    $where

    ORDER BY $orderColumn $orderDir

    LIMIT ?, ?
";


$stmt = $conn->prepare($sql);


$paramsData = $params;

$paramsData[] = $start;
$paramsData[] = $length;


$typesData =
    $types . 'ii';


$stmt->bind_param(
    $typesData,
    ...$paramsData
);


$stmt->execute();


$result =
    $stmt->get_result();


$data = [];


while ($row = $result->fetch_assoc()) {

    $id = (int) $row['id'];

    $activo =
        (int) $row['activo'];

    $publicar =
        (int) $row['publicar_catalogo'];

    $destacado =
        (int) $row['destacado_catalogo'];


    // --------------------------------------------------------
    // PRODUCTO
    // --------------------------------------------------------

    $producto = '
        <div>
            <div class="fw-semibold">
                '
                . htmlspecialchars($row['nombre'])
                . '
            </div>
    ';


    if (!empty($row['presentacion'])) {

        $producto .= '
            <small class="text-muted">
                '
                . htmlspecialchars($row['presentacion'])
                . '
            </small>
        ';

    }


    $producto .= '</div>';


    // --------------------------------------------------------
    // PRECIO
    // --------------------------------------------------------

    $precio = '
        <div>
            <span class="fw-semibold">
                S/ '
                . number_format(
                    (float) $row['precio_venta'],
                    2
                )
                . '
            </span>
    ';


    if (
        (float) $row['precio_regular']
        > (float) $row['precio_venta']
    ) {

        $precio .= '
            <div>
                <small class="text-muted text-decoration-line-through">
                    S/ '
                    . number_format(
                        (float) $row['precio_regular'],
                        2
                    )
                    . '
                </small>
            </div>
        ';

    }


    $precio .= '</div>';


    // --------------------------------------------------------
    // STOCK
    // --------------------------------------------------------

    if (!(int) $row['maneja_stock']) {

        $stock = '
            <span class="badge text-bg-secondary">
                No aplica
            </span>
        ';

    } elseif (
        (float) $row['stock_actual'] <= 0
    ) {

        $stock = '
            <span class="badge text-bg-danger">
                Agotado
            </span>
        ';

    } elseif (
        (float) $row['stock_actual']
        <= (float) $row['stock_minimo']
    ) {

        $stock = '
            <span class="badge text-bg-warning">
                '
                . number_format(
                    (float) $row['stock_actual'],
                    0
                )
                . '
            </span>
        ';

    } else {

        $stock = '
            <span class="fw-semibold">
                '
                . number_format(
                    (float) $row['stock_actual'],
                    0
                )
                . '
            </span>
        ';

    }


    // --------------------------------------------------------
    // CATÁLOGO
    // --------------------------------------------------------

    if (!$publicar) {

        $catalogo = '
            <span class="badge text-bg-secondary">
                Oculto
            </span>
        ';

    } elseif ($destacado) {

        $catalogo = '
            <span class="badge text-bg-warning">
                <i class="fa-solid fa-star me-1"></i>
                Destacado
            </span>
        ';

    } else {

        $catalogo = '
            <span class="badge text-bg-success">
                Visible
            </span>
        ';

    }


    // --------------------------------------------------------
    // ESTADO
    // --------------------------------------------------------

    $estado =
        $activo
            ? '
                <span class="badge text-bg-success">
                    Activo
                </span>
            '
            : '
                <span class="badge text-bg-danger">
                    Inactivo
                </span>
            ';


    // --------------------------------------------------------
    // ACCIONES
    // --------------------------------------------------------

    $acciones = '

        <div class="btn-group btn-group-sm">

            <button
                type="button"
                class="btn btn-outline-primary btn-editar-producto"
                data-id="' . $id . '"
                title="Editar"
            >
                <i class="fa-solid fa-pen"></i>
            </button>


            <button
                type="button"
                class="
                    btn
                    '
                    . (
                        $activo
                            ? 'btn-outline-danger'
                            : 'btn-outline-success'
                    )
                    . '
                    btn-estado-producto
                "
                data-id="' . $id . '"
                data-activo="' . $activo . '"
                title="
                    '
                    . (
                        $activo
                            ? 'Desactivar'
                            : 'Activar'
                    )
                    . '
                "
            >
                <i
                    class="
                        fa-solid
                        '
                        . (
                            $activo
                                ? 'fa-ban'
                                : 'fa-check'
                        )
                        . '
                    "
                ></i>
            </button>

        </div>

    ';


    $data[] = [

        'id' => $id,

        'producto' =>
            $producto,

        'categoria' =>
            htmlspecialchars(
                $row['categoria'] ?? 'Sin categoría'
            ),

        'precio' =>
            $precio,

        'stock' =>
            $stock,

        'catalogo' =>
            $catalogo,

        'estado' =>
            $estado,

        'acciones' =>
            $acciones

    ];

}


echo json_encode([
    'draw' => $draw,
    'recordsTotal' => $total,
    'recordsFiltered' => $filtered,
    'data' => $data
]);