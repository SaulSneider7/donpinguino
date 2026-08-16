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


$productoId =
    (int) (
        $_POST['producto_id']
        ?? 0
    );


if ($productoId <= 0) {

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


/* ============================================================
   TOTAL
============================================================ */

$sqlTotal = "
    SELECT COUNT(*) AS total

    FROM movimientos_stock

    WHERE producto_id = ?
";


$stmtTotal =
    $conn->prepare(
        $sqlTotal
    );


$stmtTotal->bind_param(
    'i',
    $productoId
);


$stmtTotal->execute();


$total =
    (int)
    $stmtTotal
        ->get_result()
        ->fetch_assoc()['total'];


/* ============================================================
   BÚSQUEDA
============================================================ */

$where = "
    WHERE ms.producto_id = ?
";


$params = [
    $productoId
];


$types = 'i';


if ($search !== '') {

    $where .= "
        AND (
            ms.tipo_movimiento LIKE ?
            OR ms.descripcion LIKE ?
            OR ms.referencia_tipo LIKE ?
        )
    ";


    $buscar =
        '%' . $search . '%';


    $params[] =
        $buscar;

    $params[] =
        $buscar;

    $params[] =
        $buscar;


    $types .=
        'sss';
}


/* ============================================================
   FILTRADO
============================================================ */

$sqlFiltered = "
    SELECT COUNT(*) AS total

    FROM movimientos_stock ms

    $where
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
        ms.id,
        ms.fecha,

        ms.tipo_movimiento,

        ms.referencia_tipo,
        ms.referencia_id,

        ms.cantidad,

        ms.stock_anterior,
        ms.stock_nuevo,

        ms.descripcion

    FROM movimientos_stock ms

    $where

    ORDER BY
        ms.fecha $orderDir,
        ms.id $orderDir

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

    $cantidad =
        (float)
        $row['cantidad'];


    if ($cantidad > 0) {

        $cantidadHtml = '

            <span class="text-success fw-semibold">
                +'
                . number_format(
                    $cantidad,
                    3
                )
                . '
            </span>

        ';

    } else {

        $cantidadHtml = '

            <span class="text-danger fw-semibold">
                '
                . number_format(
                    $cantidad,
                    3
                )
                . '
            </span>

        ';
    }


    switch (
        $row['tipo_movimiento']
    ) {

        case 'COMPRA':

            $badge =
                'text-bg-primary';

            $label =
                'Compra';

            break;


        case 'VENTA':

            $badge =
                'text-bg-success';

            $label =
                'Venta';

            break;


        case 'REGALO':

        case 'REGALO_PROMOCIONAL':

            $badge =
                'text-bg-warning';

            $label =
                'Regalo';

            break;


        case 'AJUSTE_ENTRADA':

            $badge =
                'text-bg-info';

            $label =
                'Ajuste +';

            break;


        case 'AJUSTE_SALIDA':

            $badge =
                'text-bg-danger';

            $label =
                'Ajuste -';

            break;


        default:

            $badge =
                'text-bg-secondary';

            $label =
                htmlspecialchars(
                    $row['tipo_movimiento']
                );
    }


    $movimiento = '

        <span class="badge '
        . $badge
        . '">
            '
            . $label
            . '
        </span>

    ';


    if (
        !empty(
            $row['referencia_tipo']
        )
    ) {

        $referencia =
            htmlspecialchars(
                $row['referencia_tipo']
            );


        if (
            !empty(
                $row['referencia_id']
            )
        ) {

            $referencia .=
                ' #'
                . (int)
                $row['referencia_id'];
        }

    } else {

        $referencia =
            '<span class="text-muted">-</span>';
    }


    $data[] = [

        'fecha' =>
            date(
                'd/m/Y H:i',
                strtotime(
                    $row['fecha']
                )
            ),

        'movimiento' =>
            $movimiento,

        'cantidad' =>
            $cantidadHtml,

        'stock_anterior' =>
            number_format(
                (float)
                $row['stock_anterior'],
                3
            ),

        'stock_nuevo' =>
            number_format(
                (float)
                $row['stock_nuevo'],
                3
            ),

        'referencia' =>
            $referencia,

        'descripcion' =>
            !empty(
                $row['descripcion']
            )
                ? htmlspecialchars(
                    $row['descripcion']
                )
                : '<span class="text-muted">-</span>'
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