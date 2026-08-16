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
    || $length > 100
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


/* ============================================================
   COLUMNAS PERMITIDAS
============================================================ */

$orderColumns = [
    0 => 'c.id',
    1 => 'c.nombre',
    2 => 'c.telefono',
    3 => 'c.direccion',
    4 => 'c.activo'
];


$orderColumn =
    $orderColumns[$orderColumnIndex]
    ?? 'c.id';


/* ============================================================
   TOTAL GENERAL
============================================================ */

$resultTotal =
    $conn->query(
        "
        SELECT COUNT(*) AS total
        FROM clientes
        "
    );


$total =
    (int) $resultTotal
        ->fetch_assoc()['total'];


/* ============================================================
   FILTRO
============================================================ */

$where = '';

$params = [];

$types = '';


if ($search !== '') {

    $where = "
        WHERE (
            c.nombre LIKE ?
            OR c.telefono LIKE ?
            OR c.direccion LIKE ?
        )
    ";


    $buscar =
        '%' . $search . '%';


    $params = [
        $buscar,
        $buscar,
        $buscar
    ];


    $types = 'sss';
}


/* ============================================================
   TOTAL FILTRADO
============================================================ */

$sqlFiltered = "
    SELECT COUNT(*) AS total

    FROM clientes c

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


/* ============================================================
   DATA
============================================================ */

$sql = "
    SELECT
        c.id,
        c.nombre,
        c.telefono,
        c.direccion,
        c.observacion,
        c.activo

    FROM clientes c

    $where

    ORDER BY
        $orderColumn $orderDir

    LIMIT ?, ?
";


$stmt =
    $conn->prepare($sql);


$paramsData =
    $params;


$paramsData[] =
    $start;


$paramsData[] =
    $length;


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


while (
    $row = $result->fetch_assoc()
) {

    $id =
        (int) $row['id'];

    $activo =
        (int) $row['activo'];


    /* CLIENTE */

    $cliente = '
        <div>
            <div class="fw-semibold">
                '
                . htmlspecialchars(
                    $row['nombre']
                )
                . '
            </div>
    ';


    if (!empty($row['observacion'])) {

        $cliente .= '
            <small class="text-muted">
                '
                . htmlspecialchars(
                    mb_strimwidth(
                        $row['observacion'],
                        0,
                        60,
                        '...'
                    )
                )
                . '
            </small>
        ';
    }


    $cliente .= '</div>';


    /* TELÉFONO */

    if (!empty($row['telefono'])) {

        $telefono = '
            <i class="fa-solid fa-phone me-1 text-muted"></i>
            '
            . htmlspecialchars(
                $row['telefono']
            );

    } else {

        $telefono = '
            <span class="text-muted">
                -
            </span>
        ';
    }


    /* DIRECCIÓN */

    $direccion =
        !empty($row['direccion'])
            ? htmlspecialchars(
                $row['direccion']
            )
            : '<span class="text-muted">-</span>';


    /* ESTADO */

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


    /* ACCIONES */

    $acciones = '

        <div class="btn-group btn-group-sm">

             <button
                type="button"
                class="btn btn-outline-warning btn-deudas-cliente"
                data-id="' . $id . '"
                data-nombre="' .
                    htmlspecialchars(
                        $row['nombre'],
                        ENT_QUOTES
                    )
                . '"
                title="Ver deudas"
            >
                <i class="fa-solid fa-hand-holding-dollar"></i>
            </button>
        

            <button
                type="button"
                class="btn btn-outline-primary btn-editar-cliente"
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
                    btn-estado-cliente
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

        'id' =>
            $id,

        'cliente' =>
            $cliente,

        'telefono' =>
            $telefono,

        'direccion' =>
            $direccion,

        'estado' =>
            $estado,

        'acciones' =>
            $acciones

    ];
}


echo json_encode(
    [
        'draw' => $draw,
        'recordsTotal' => $total,
        'recordsFiltered' => $filtered,
        'data' => $data
    ],
    JSON_UNESCAPED_UNICODE
);