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
    2 => 'p.fecha_inicio',
    3 => 'p.id',
    4 => 'cantidad_productos',
    5 => 'p.prioridad',
    6 => 'p.activo'

];


$orderColumn =
    $orderColumns[
        $orderColumnIndex
    ]
    ?? 'p.id';


/* ============================================================
   TOTAL
============================================================ */

$total =
    (int)
    $conn
        ->query(
            "
                SELECT COUNT(*) AS total
                FROM promociones
            "
        )
        ->fetch_assoc()['total'];


/* ============================================================
   BÚSQUEDA
============================================================ */

$where = '';

$params = [];

$types = '';


if ($search !== '') {

    $where = "
        WHERE (
            p.nombre LIKE ?
            OR p.descripcion LIKE ?
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
   TOTAL FILTRADO
============================================================ */

$sqlFiltered = "
    SELECT COUNT(*) AS total

    FROM promociones p

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
        p.id,
        p.nombre,
        p.descripcion,

        p.fecha_inicio,
        p.fecha_fin,

        p.prioridad,
        p.acumulable,
        p.activo,

        (
            SELECT COUNT(*)

            FROM promocion_productos pp

            WHERE
                pp.promocion_id = p.id
                AND pp.activo = 1

        ) AS cantidad_productos,

        (
            SELECT
                GROUP_CONCAT(
                    pd.dia_semana
                    ORDER BY pd.dia_semana
                    SEPARATOR ','
                )

            FROM promocion_dias pd

            WHERE pd.promocion_id = p.id

        ) AS dias

    FROM promociones p

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
    $stmt
        ->get_result();


$data = [];


$nombresDias = [

    1 => 'Lun',
    2 => 'Mar',
    3 => 'Mié',
    4 => 'Jue',
    5 => 'Vie',
    6 => 'Sáb',
    7 => 'Dom'
];


while (
    $row =
        $result->fetch_assoc()
) {

    $id =
        (int) $row['id'];


    $activo =
        (int) $row['activo'];


    // ========================================================
    // PROMOCIÓN
    // ========================================================

    $promocion = '

        <div class="fw-semibold">
            '
            . htmlspecialchars(
                $row['nombre']
            )
            . '
        </div>

    ';


    if (!empty($row['descripcion'])) {

        $promocion .= '

            <small class="text-muted">
                '
                . htmlspecialchars(
                    mb_strimwidth(
                        $row['descripcion'],
                        0,
                        70,
                        '...'
                    )
                )
                . '
            </small>

        ';
    }


    // ========================================================
    // VIGENCIA
    // ========================================================

    $vigencia = '

        <div>
            '
            . date(
                'd/m/Y',
                strtotime(
                    $row['fecha_inicio']
                )
            )
            . '
        </div>

        <small class="text-muted">
            hasta
            '
            . date(
                'd/m/Y',
                strtotime(
                    $row['fecha_fin']
                )
            )
            . '
        </small>

    ';


    // ========================================================
    // DÍAS
    // ========================================================

    if (empty($row['dias'])) {

        $diasHtml = '

            <span class="badge text-bg-secondary">
                Todos
            </span>

        ';

    } else {

        $diasHtml = '';


        foreach (
            explode(
                ',',
                $row['dias']
            )
            as $dia
        ) {

            $numero =
                (int) $dia;


            if (
                isset(
                    $nombresDias[
                        $numero
                    ]
                )
            ) {

                $diasHtml .= '

                    <span class="badge text-bg-light border me-1">
                        '
                        . $nombresDias[
                            $numero
                        ]
                        . '
                    </span>

                ';
            }
        }
    }


    // ========================================================
    // ESTADO
    // ========================================================

    $hoy =
        date('Y-m-d');


    if (!$activo) {

        $estado = '

            <span class="badge text-bg-secondary">
                Inactiva
            </span>

        ';

    } elseif (
        $hoy
        <
        $row['fecha_inicio']
    ) {

        $estado = '

            <span class="badge text-bg-info">
                Programada
            </span>

        ';

    } elseif (
        $hoy
        >
        $row['fecha_fin']
    ) {

        $estado = '

            <span class="badge text-bg-dark">
                Finalizada
            </span>

        ';

    } else {

        $estado = '

            <span class="badge text-bg-success">
                Vigente
            </span>

        ';
    }


    // ========================================================
    // ACCIONES
    // ========================================================

    $acciones = '

        <div class="btn-group btn-group-sm">

            <a
                href="'
                . BASE_URL
                . 'modules/promociones/form.php?id='
                . $id
                . '"
                class="btn btn-outline-primary"
                title="Editar"
            >
                <i class="fa-solid fa-pen"></i>
            </a>


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
                    btn-estado-promocion
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

        'promocion' =>
            $promocion,

        'vigencia' =>
            $vigencia,

        'dias' =>
            $diasHtml,

        'productos' => '

            <span class="badge text-bg-primary">
                '
                . (int)
                $row['cantidad_productos']
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