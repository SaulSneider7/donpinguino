<?php

session_start();

header('Content-Type: application/json; charset=utf-8');

require_once __DIR__ . '/../../config/database.php';


function responder(
    bool $success,
    string $message,
    ?array $data = null
): void {

    echo json_encode(
        [
            'success' => $success,
            'message' => $message,
            'data' => $data
        ],
        JSON_UNESCAPED_UNICODE
    );

    exit;
}


if (!isset($_SESSION['usuario_id'])) {

    http_response_code(401);

    responder(
        false,
        'Sesión expirada.'
    );
}


$id =
    (int) ($_GET['id'] ?? 0);


if ($id <= 0) {

    responder(
        false,
        'Promoción inválida.'
    );
}


/* ============================================================
   CABECERA
============================================================ */

$stmt =
    $conn->prepare(
        "
            SELECT
                id,
                nombre,
                descripcion,

                fecha_inicio,
                fecha_fin,

                prioridad,
                acumulable,
                activo

            FROM promociones

            WHERE id = ?

            LIMIT 1
        "
    );


$stmt->bind_param(
    'i',
    $id
);


$stmt->execute();


$promocion =
    $stmt
        ->get_result()
        ->fetch_assoc();


if (!$promocion) {

    responder(
        false,
        'Promoción no encontrada.'
    );
}


/* ============================================================
   DÍAS
============================================================ */

$stmtDias =
    $conn->prepare(
        "
            SELECT dia_semana

            FROM promocion_dias

            WHERE promocion_id = ?

            ORDER BY dia_semana ASC
        "
    );


$stmtDias->bind_param(
    'i',
    $id
);


$stmtDias->execute();


$resultDias =
    $stmtDias
        ->get_result();


$dias = [];


while (
    $row =
        $resultDias
            ->fetch_assoc()
) {

    $dias[] =
        (int)
        $row['dia_semana'];
}


/* ============================================================
   REGLAS
============================================================ */

$stmtReglas =
    $conn->prepare(
        "
            SELECT
                pp.id,
                pp.producto_id,

                p.nombre AS producto_nombre,
                p.presentacion,

                pp.tipo_beneficio,

                pp.cantidad_minima,
                pp.unidad_beneficiada,

                pp.precio_promocional,
                pp.porcentaje_descuento,
                pp.monto_descuento,

                pp.max_aplicaciones_por_venta

            FROM promocion_productos pp

            INNER JOIN productos p
                ON p.id = pp.producto_id

            WHERE
                pp.promocion_id = ?
                AND pp.activo = 1

            ORDER BY pp.id ASC
        "
    );


$stmtReglas->bind_param(
    'i',
    $id
);


$stmtReglas->execute();


$reglas =
    $stmtReglas
        ->get_result()
        ->fetch_all(
            MYSQLI_ASSOC
        );


responder(
    true,
    'Promoción encontrada.',
    [
        'promocion' =>
            $promocion,

        'dias' =>
            $dias,

        'reglas' =>
            $reglas
    ]
);