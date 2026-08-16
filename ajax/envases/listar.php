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

    0 => 'c.nombre',
    1 => 'te.nombre',
    2 => 'me.saldo_nuevo',
    3 => 'me.fecha'

];


$orderColumn =
    $orderColumns[
        $orderColumnIndex
    ]
    ?? 'c.nombre';


/* ============================================================
   SUBQUERY ÚLTIMO MOVIMIENTO
============================================================ */

$baseFrom = "
    FROM movimientos_envases me

    INNER JOIN (
        SELECT
            cliente_id,
            tipo_envase_id,
            MAX(id) AS ultimo_id

        FROM movimientos_envases

        GROUP BY
            cliente_id,
            tipo_envase_id
    ) ult
        ON ult.ultimo_id = me.id

    INNER JOIN clientes c
        ON c.id = me.cliente_id

    INNER JOIN tipos_envase te
        ON te.id = me.tipo_envase_id
";


/* ============================================================
   WHERE
============================================================ */

$where = "
    WHERE me.saldo_nuevo > 0
";


$params = [];

$types = '';


if ($search !== '') {

    $where .= "
        AND (
            c.nombre LIKE ?
            OR te.nombre LIKE ?
        )
    ";


    $buscar =
        '%' . $search . '%';


    $params = [
        $buscar,
        $buscar
    ];


    $types =
        'ss';
}


/* ============================================================
   TOTAL GENERAL PENDIENTES
============================================================ */

$sqlTotal = "
    SELECT COUNT(*) AS total

    $baseFrom

    WHERE me.saldo_nuevo > 0
";


$resultTotal =
    $conn->query(
        $sqlTotal
    );


$total =
    (int)
    $resultTotal
        ->fetch_assoc()['total'];


/* ============================================================
   TOTAL FILTRADO
============================================================ */

$sqlFiltered = "
    SELECT COUNT(*) AS total

    $baseFrom

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
        me.cliente_id,
        me.tipo_envase_id,

        c.nombre AS cliente,
        te.nombre AS tipo_envase,

        me.saldo_nuevo,
        me.fecha

    $baseFrom

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

    $clienteId =
        (int)
        $row['cliente_id'];


    $tipoEnvaseId =
        (int)
        $row['tipo_envase_id'];


    $saldo =
        (float)
        $row['saldo_nuevo'];


    $acciones = '

        <button
            type="button"
            class="btn btn-success btn-sm btn-devolver-envase"

            data-cliente-id="' . $clienteId . '"
            data-tipo-envase-id="' . $tipoEnvaseId . '"

            title="Registrar devolución"
        >

            <i class="fa-solid fa-rotate-left me-1"></i>
            Devolver

        </button>
    ';


    $data[] = [

        'cliente' =>
            htmlspecialchars(
                $row['cliente']
            ),

        'tipo_envase' =>
            htmlspecialchars(
                $row['tipo_envase']
            ),

        'saldo' => '
            <span class="badge text-bg-danger fs-6">
                '
                . number_format(
                    $saldo,
                    0
                )
                . '
            </span>
        ',

        'ultima_actualizacion' =>
            date(
                'd/m/Y H:i',
                strtotime(
                    $row['fecha']
                )
            ),

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