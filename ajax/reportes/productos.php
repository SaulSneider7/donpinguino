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
        ?? 1
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

    0 => 'dv.nombre_producto',
    1 => 'cantidad_vendida',
    2 => 'ingresos',
    3 => 'costo',
    4 => 'utilidad',
    5 => 'margen'

];


$orderColumn =
    $orderColumns[
        $orderColumnIndex
    ]
    ?? 'cantidad_vendida';


/* ============================================================
   SEARCH
============================================================ */

$whereSearch = '';


$params =
    [
        $desde,
        $hasta
    ];


$types =
    'ss';


if ($search !== '') {

    $whereSearch = "
        AND (
            dv.nombre_producto LIKE ?
            OR dv.presentacion_producto LIKE ?
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
   TOTAL
============================================================ */

$sqlTotal = "
    SELECT
        COUNT(
            DISTINCT
            CONCAT(
                COALESCE(
                    CAST(dv.producto_id AS CHAR),
                    'NULL'
                ),
                '|',
                dv.nombre_producto
            )
        ) AS total

    FROM detalle_venta dv

    INNER JOIN ventas v
        ON v.id = dv.venta_id

    WHERE
        v.estado = 'ACTIVA'
        AND v.fecha BETWEEN ? AND ?
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
   FILTRADO
============================================================ */

$sqlFiltered = "
    SELECT
        COUNT(
            DISTINCT
            CONCAT(
                COALESCE(
                    CAST(dv.producto_id AS CHAR),
                    'NULL'
                ),
                '|',
                dv.nombre_producto
            )
        ) AS total

    FROM detalle_venta dv

    INNER JOIN ventas v
        ON v.id = dv.venta_id

    WHERE
        v.estado = 'ACTIVA'
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
        dv.producto_id,
        dv.nombre_producto,

        MAX(
            dv.presentacion_producto
        ) AS presentacion,

        SUM(
            dv.cantidad
        ) AS cantidad_vendida,

        SUM(
            dv.subtotal_final
        ) AS ingresos,

        SUM(
            dv.costo_unitario
            *
            dv.cantidad
        ) AS costo,

        SUM(
            dv.subtotal_final
            -
            (
                dv.costo_unitario
                *
                dv.cantidad
            )
        ) AS utilidad,

        CASE

            WHEN
                SUM(
                    dv.subtotal_final
                ) > 0

            THEN
                (
                    SUM(
                        dv.subtotal_final
                        -
                        (
                            dv.costo_unitario
                            *
                            dv.cantidad
                        )
                    )
                    /
                    SUM(
                        dv.subtotal_final
                    )
                )
                *
                100

            ELSE 0

        END AS margen

    FROM detalle_venta dv

    INNER JOIN ventas v
        ON v.id = dv.venta_id

    WHERE
        v.estado = 'ACTIVA'
        AND v.fecha BETWEEN ? AND ?

        $whereSearch

    GROUP BY
        dv.producto_id,
        dv.nombre_producto

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

    $producto = '

        <div class="fw-semibold">
            '
            . htmlspecialchars(
                $row['nombre_producto']
            )
            . '
        </div>

    ';


    if (
        !empty(
            $row['presentacion']
        )
    ) {

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


    $utilidad =
        (float)
        $row['utilidad'];


    $data[] = [

        'producto' =>
            $producto,

        'cantidad' =>
            number_format(
                (float)
                $row['cantidad_vendida'],
                3
            ),

        'ingresos' => '

            <span class="fw-semibold">
                S/
                '
                . number_format(
                    (float)
                    $row['ingresos'],
                    2
                )
                . '
            </span>

        ',

        'costo' =>
            'S/ '
            . number_format(
                (float)
                $row['costo'],
                2
            ),

        'utilidad' => '

            <span class="
                '
                . (
                    $utilidad >= 0
                        ? 'text-success'
                        : 'text-danger'
                )
                . '
                fw-semibold
            ">
                S/
                '
                . number_format(
                    $utilidad,
                    2
                )
                . '
            </span>

        ',

        'margen' =>
            number_format(
                (float)
                $row['margen'],
                2
            )
            . '%'
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