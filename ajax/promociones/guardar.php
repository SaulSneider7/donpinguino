<?php

session_start();

header('Content-Type: application/json; charset=utf-8');

require_once __DIR__ . '/../../config/database.php';


function responder(
    bool $success,
    string $message,
    array $extra = []
): void {

    echo json_encode(
        array_merge(
            [
                'success' => $success,
                'message' => $message
            ],
            $extra
        ),
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
    isset($_POST['id'])
    && $_POST['id'] !== ''
        ? (int) $_POST['id']
        : null;


$nombre =
    trim(
        $_POST['nombre']
        ?? ''
    );


$descripcion =
    trim(
        $_POST['descripcion']
        ?? ''
    );


$fechaInicio =
    $_POST['fecha_inicio']
    ?? '';


$fechaFin =
    $_POST['fecha_fin']
    ?? '';


$prioridad =
    max(
        0,
        (int) (
            $_POST['prioridad']
            ?? 0
        )
    );


$acumulable =
    isset($_POST['acumulable'])
    &&
    (int) $_POST['acumulable'] === 1
        ? 1
        : 0;


$dias =
    json_decode(
        $_POST['dias']
        ?? '[]',
        true
    );


$reglas =
    json_decode(
        $_POST['reglas']
        ?? '[]',
        true
    );


if ($nombre === '') {

    responder(
        false,
        'Ingrese el nombre de la promoción.'
    );
}


if (
    $fechaInicio === ''
    ||
    $fechaFin === ''
) {

    responder(
        false,
        'Ingrese las fechas de vigencia.'
    );
}


if (
    $fechaFin
    <
    $fechaInicio
) {

    responder(
        false,
        'La fecha final no puede ser anterior a la inicial.'
    );
}


if (!is_array($dias)) {

    $dias = [];
}


if (
    !is_array($reglas)
    ||
    count($reglas) === 0
) {

    responder(
        false,
        'Agregue al menos una regla promocional.'
    );
}


$tiposPermitidos = [

    'PRECIO_ESPECIAL',

    'PORCENTAJE',

    'DESCUENTO_FIJO',

    'UNIDAD_N_PRECIO_ESPECIAL',

    'UNIDAD_N_PORCENTAJE',

    'CANTIDAD_POR_PRECIO'
];


try {

    $conn->begin_transaction();


    // ========================================================
    // CREAR / ACTUALIZAR CABECERA
    // ========================================================

    if ($id === null) {

        $sql = "
            INSERT INTO promociones (
                nombre,
                descripcion,

                fecha_inicio,
                fecha_fin,

                prioridad,
                acumulable,

                activo
            )
            VALUES (
                ?,
                ?,

                ?,
                ?,

                ?,
                ?,

                1
            )
        ";


        $stmt =
            $conn->prepare($sql);


        $stmt->bind_param(
            'ssssii',

            $nombre,
            $descripcion,

            $fechaInicio,
            $fechaFin,

            $prioridad,
            $acumulable
        );


        if (!$stmt->execute()) {

            throw new Exception(
                'No se pudo registrar la promoción: '
                . $stmt->error
            );
        }


        $id =
            $stmt->insert_id;

    } else {

        $sql = "
            UPDATE promociones

            SET
                nombre = ?,
                descripcion = ?,

                fecha_inicio = ?,
                fecha_fin = ?,

                prioridad = ?,
                acumulable = ?

            WHERE id = ?

            LIMIT 1
        ";


        $stmt =
            $conn->prepare($sql);


        $stmt->bind_param(
            'ssssiii',

            $nombre,
            $descripcion,

            $fechaInicio,
            $fechaFin,

            $prioridad,
            $acumulable,

            $id
        );


        if (!$stmt->execute()) {

            throw new Exception(
                'No se pudo actualizar la promoción.'
            );
        }


        /*
         * Eliminamos configuración antigua
         * y la reconstruimos.
         */
        $stmtDeleteDias =
            $conn->prepare(
                "
                    DELETE
                    FROM promocion_dias
                    WHERE promocion_id = ?
                "
            );


        $stmtDeleteDias
            ->bind_param(
                'i',
                $id
            );


        $stmtDeleteDias
            ->execute();


        $stmtDeleteReglas =
            $conn->prepare(
                "
                    DELETE
                    FROM promocion_productos
                    WHERE promocion_id = ?
                "
            );


        $stmtDeleteReglas
            ->bind_param(
                'i',
                $id
            );


        $stmtDeleteReglas
            ->execute();
    }


    // ========================================================
    // DÍAS
    //
    // Sin filas = todos los días.
    // ========================================================

    foreach ($dias as $dia) {

        $dia =
            (int) $dia;


        if (
            $dia < 1
            ||
            $dia > 7
        ) {

            throw new Exception(
                'Día de promoción inválido.'
            );
        }


        $sqlDia = "
            INSERT INTO promocion_dias (
                promocion_id,
                dia_semana
            )
            VALUES (
                ?,
                ?
            )
        ";


        $stmtDia =
            $conn->prepare(
                $sqlDia
            );


        $stmtDia->bind_param(
            'ii',

            $id,
            $dia
        );


        if (!$stmtDia->execute()) {

            throw new Exception(
                'No se pudieron registrar los días.'
            );
        }
    }


    // ========================================================
    // REGLAS
    // ========================================================

    foreach ($reglas as $regla) {

        $productoId =
            (int) (
                $regla['producto_id']
                ?? 0
            );


        $tipoBeneficio =
            $regla['tipo_beneficio']
            ?? '';


        $cantidadMinima =
            (float) (
                $regla['cantidad_minima']
                ?? 1
            );


        $unidadBeneficiada =
            isset(
                $regla[
                    'unidad_beneficiada'
                ]
            )
            &&
            $regla[
                'unidad_beneficiada'
            ] !== null
                ? (int)
                    $regla[
                        'unidad_beneficiada'
                    ]
                : null;


        $precioPromocional =
            isset(
                $regla[
                    'precio_promocional'
                ]
            )
            &&
            $regla[
                'precio_promocional'
            ] !== null
                ? (float)
                    $regla[
                        'precio_promocional'
                    ]
                : null;


        $porcentaje =
            isset(
                $regla[
                    'porcentaje_descuento'
                ]
            )
            &&
            $regla[
                'porcentaje_descuento'
            ] !== null
                ? (float)
                    $regla[
                        'porcentaje_descuento'
                    ]
                : null;


        $montoDescuento =
            isset(
                $regla[
                    'monto_descuento'
                ]
            )
            &&
            $regla[
                'monto_descuento'
            ] !== null
                ? (float)
                    $regla[
                        'monto_descuento'
                    ]
                : null;


        $maxAplicaciones =
            isset(
                $regla[
                    'max_aplicaciones_por_venta'
                ]
            )
            &&
            $regla[
                'max_aplicaciones_por_venta'
            ] !== null
                ? (int)
                    $regla[
                        'max_aplicaciones_por_venta'
                    ]
                : null;


        // ====================================================
        // VALIDACIONES
        // ====================================================

        if ($productoId <= 0) {

            throw new Exception(
                'Producto inválido en una promoción.'
            );
        }


        if (
            !in_array(
                $tipoBeneficio,
                $tiposPermitidos,
                true
            )
        ) {

            throw new Exception(
                'Tipo de promoción inválido.'
            );
        }


        if ($cantidadMinima <= 0) {

            throw new Exception(
                'La cantidad mínima debe ser mayor a cero.'
            );
        }


        // ----------------------------------------------------
        // PRODUCTO EXISTENTE
        // ----------------------------------------------------

        $stmtProducto =
            $conn->prepare(
                "
                    SELECT id
                    FROM productos

                    WHERE
                        id = ?
                        AND activo = 1

                    LIMIT 1
                "
            );


        $stmtProducto
            ->bind_param(
                'i',
                $productoId
            );


        $stmtProducto
            ->execute();


        if (
            !$stmtProducto
                ->get_result()
                ->fetch_assoc()
        ) {

            throw new Exception(
                'Uno de los productos no existe o está desactivado.'
            );
        }


        // ----------------------------------------------------
        // VALIDAR CAMPOS SEGÚN TIPO
        // ----------------------------------------------------

        switch ($tipoBeneficio) {

            case 'PRECIO_ESPECIAL':

                if (
                    $precioPromocional === null
                    ||
                    $precioPromocional < 0
                ) {

                    throw new Exception(
                        'Ingrese el precio promocional.'
                    );
                }

                break;


            case 'PORCENTAJE':

                if (
                    $porcentaje === null
                    ||
                    $porcentaje < 0
                    ||
                    $porcentaje > 100
                ) {

                    throw new Exception(
                        'Ingrese un porcentaje válido.'
                    );
                }

                break;


            case 'DESCUENTO_FIJO':

                if (
                    $montoDescuento === null
                    ||
                    $montoDescuento < 0
                ) {

                    throw new Exception(
                        'Ingrese el monto del descuento.'
                    );
                }

                break;


            case 'UNIDAD_N_PRECIO_ESPECIAL':

                if (
                    !$unidadBeneficiada
                    ||
                    $unidadBeneficiada <= 0
                    ||
                    $precioPromocional === null
                ) {

                    throw new Exception(
                        'Complete cada N unidades y el precio promocional.'
                    );
                }

                break;


            case 'UNIDAD_N_PORCENTAJE':

                if (
                    !$unidadBeneficiada
                    ||
                    $unidadBeneficiada <= 0
                    ||
                    $porcentaje === null
                    ||
                    $porcentaje < 0
                    ||
                    $porcentaje > 100
                ) {

                    throw new Exception(
                        'Complete cada N unidades y el porcentaje.'
                    );
                }

                break;


            case 'CANTIDAD_POR_PRECIO':

                if (
                    !$unidadBeneficiada
                    ||
                    $unidadBeneficiada <= 0
                    ||
                    $precioPromocional === null
                    ||
                    $precioPromocional < 0
                ) {

                    throw new Exception(
                        'Complete la cantidad del grupo y su precio.'
                    );
                }


                /*
                 * Para "3 por S/20",
                 * la cantidad mínima debe ser al menos 3.
                 */
                if (
                    $cantidadMinima
                    <
                    $unidadBeneficiada
                ) {

                    $cantidadMinima =
                        $unidadBeneficiada;
                }

                break;
        }


        $sqlRegla = "
            INSERT INTO promocion_productos (
                promocion_id,
                producto_id,

                tipo_beneficio,

                cantidad_minima,
                unidad_beneficiada,

                precio_promocional,
                porcentaje_descuento,
                monto_descuento,

                max_aplicaciones_por_venta,

                activo
            )
            VALUES (
                ?,
                ?,

                ?,

                ?,
                ?,

                ?,
                ?,
                ?,

                ?,

                1
            )
        ";


        $stmtRegla =
            $conn->prepare(
                $sqlRegla
            );


        $stmtRegla->bind_param(
            'iisdidddi',

            $id,
            $productoId,

            $tipoBeneficio,

            $cantidadMinima,
            $unidadBeneficiada,

            $precioPromocional,
            $porcentaje,
            $montoDescuento,

            $maxAplicaciones
        );


        if (!$stmtRegla->execute()) {

            throw new Exception(
                'No se pudo registrar una regla: '
                . $stmtRegla->error
            );
        }
    }


    $conn->commit();


    responder(
        true,
        'Promoción guardada correctamente.',
        [
            'id' =>
                $id
        ]
    );


} catch (Throwable $e) {

    $conn->rollback();


    responder(
        false,
        $e->getMessage()
    );
}