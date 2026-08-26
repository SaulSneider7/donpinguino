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
    (int) (
        $_POST['draw']
        ?? 0
    );


$start =
    max(
        0,
        (int) (
            $_POST['start']
            ?? 0
        )
    );


$length =
    (int) (
        $_POST['length']
        ?? 10
    );


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
        [
            'asc',
            'desc'
        ],
        true
    )
) {

    $orderDir =
        'desc';
}


$orderColumns = [

    0 => 'g.id',
    1 => 'g.fecha',
    2 => 'g.tipo',
    3 => 'g.descripcion',
    4 => 'g.monto',
    5 => 'g.activo'

];


$orderColumn =
    $orderColumns[
        $orderColumnIndex
    ]
    ?? 'g.fecha';


/* ============================================================
   TOTAL
============================================================ */

$total =
    (int)
    $conn
        ->query(
            "
                SELECT
                    COUNT(*) AS total

                FROM gastos
            "
        )
        ->fetch_assoc()['total'];


/* ============================================================
   SEARCH
============================================================ */

$where = '';

$params = [];

$types = '';


if ($search !== '') {

    $where = "
        WHERE (
            g.descripcion LIKE ?
            OR g.tipo LIKE ?
            OR g.observacion LIKE ?
        )
    ";


    $buscar =
        '%'
        . $search
        . '%';


    $params = [
        $buscar,
        $buscar,
        $buscar
    ];


    $types =
        'sss';
}


/* ============================================================
   FILTRADO
============================================================ */

$sqlFiltered = "
    SELECT
        COUNT(*) AS total

    FROM gastos g

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
        g.id,
        g.fecha,
        g.tipo,
        g.descripcion,
        g.monto,
        g.observacion,
        g.activo

    FROM gastos g

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

    $id =
        (int)
        $row['id'];


    $activo =
        (int)
        $row['activo'];


    switch (
        $row['tipo']
    ) {

        case 'AGUA':

            $tipoLabel =
                'Agua';

            break;


        case 'LUZ':

            $tipoLabel =
                'Luz';

            break;


        case 'COMIDA':

            $tipoLabel =
                'Comida';

            break;


        case 'INSUMOS':

            $tipoLabel =
                'Insumos';

            break;


        case 'DELIVERY':

            $tipoLabel =
                'Delivery';

            break;


        case 'ALQUILER':

            $tipoLabel =
                'Alquiler';

            break;


        default:

            $tipoLabel =
                'Otro';
    }


    $descripcion = '

        <div class="fw-semibold">
            '
            . htmlspecialchars(
                $row['descripcion']
            )
            . '
        </div>

    ';


    if (
        !empty(
            $row['observacion']
        )
    ) {

        $descripcion .= '

            <small class="text-muted">
                '
                . htmlspecialchars(
                    mb_strimwidth(
                        $row['observacion'],
                        0,
                        70,
                        '...'
                    )
                )
                . '
            </small>

        ';
    }


    $estado =
        $activo

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

        <div class="btn-group btn-group-sm">

            <button
                type="button"
                class="btn btn-outline-primary btn-editar-gasto"
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
                    btn-estado-gasto
                "

                data-id="' . $id . '"
                data-activo="' . $activo . '"
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

        'id' =>
            $id,

        'fecha' =>
            date(
                'd/m/Y',
                strtotime(
                    $row['fecha']
                )
            ),

        'tipo' => '

            <span class="badge text-bg-light border">
                '
                . htmlspecialchars(
                    $tipoLabel
                )
                . '
            </span>

        ',

        'descripcion' =>
            $descripcion,

        'monto' => '

            <span class="fw-bold text-danger">
                S/
                '
                . number_format(
                    (float)
                    $row['monto'],
                    2
                )
                . '
            </span>

        ',

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