<?php

session_start();

header('Content-Type: application/json; charset=utf-8');

require_once __DIR__ . '/../../config/database.php';


function responder(
    bool $success,
    string $message
): void {

    echo json_encode(
        [
            'success' => $success,
            'message' => $message
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


$productoId =
    (int) (
        $_POST['producto_id']
        ?? 0
    );


$componentes =
    json_decode(
        $_POST['componentes']
        ?? '[]',
        true
    );


if ($productoId <= 0) {

    responder(
        false,
        'Combo inválido.'
    );
}


if (
    !is_array($componentes)
    ||
    count($componentes) === 0
) {

    responder(
        false,
        'Agregue al menos un componente.'
    );
}


try {

    $conn->begin_transaction();


    // ========================================================
    // VALIDAR PRODUCTO COMBO
    // ========================================================

    $stmt =
        $conn->prepare(
            "
            SELECT id

            FROM productos

            WHERE
                id = ?
                AND tipo_producto = 'COMBO'

            LIMIT 1

            FOR UPDATE
            "
        );


    $stmt->bind_param(
        'i',
        $productoId
    );


    $stmt->execute();


    if (
        !$stmt
            ->get_result()
            ->fetch_assoc()
    ) {

        throw new Exception(
            'El producto no es un combo válido.'
        );
    }


    // ========================================================
    // CREAR CABECERA SI NO EXISTE
    // ========================================================

    $stmtCombo =
        $conn->prepare(
            "
            SELECT id

            FROM combos

            WHERE producto_id = ?

            LIMIT 1
            "
        );


    $stmtCombo->bind_param(
        'i',
        $productoId
    );


    $stmtCombo->execute();


    $combo =
        $stmtCombo
            ->get_result()
            ->fetch_assoc();


    if ($combo) {

        $comboId =
            (int) $combo['id'];

    } else {

        $stmtInsertCombo =
            $conn->prepare(
                "
                INSERT INTO combos (
                    producto_id,
                    activo
                )
                VALUES (
                    ?,
                    1
                )
                "
            );


        $stmtInsertCombo
            ->bind_param(
                'i',
                $productoId
            );


        if (
            !$stmtInsertCombo
                ->execute()
        ) {

            throw new Exception(
                'No se pudo crear el combo.'
            );
        }


        $comboId =
            $stmtInsertCombo
                ->insert_id;
    }


    // ========================================================
    // VALIDAR COMPONENTES
    // ========================================================

    $idsUsados = [];


    foreach (
        $componentes
        as $componente
    ) {

        $id =
            (int) (
                $componente[
                    'producto_id'
                ]
                ?? 0
            );


        $cantidad =
            (float) (
                $componente[
                    'cantidad'
                ]
                ?? 0
            );


        if (
            $id <= 0
            ||
            $cantidad <= 0
        ) {

            throw new Exception(
                'Componente inválido.'
            );
        }


        if (
            in_array(
                $id,
                $idsUsados,
                true
            )
        ) {

            throw new Exception(
                'Hay componentes repetidos.'
            );
        }


        $idsUsados[] =
            $id;


        $stmtProducto =
            $conn->prepare(
                "
                SELECT id

                FROM productos

                WHERE
                    id = ?
                    AND activo = 1
                    AND tipo_producto = 'SIMPLE'
                    AND maneja_stock = 1

                LIMIT 1
                "
            );


        $stmtProducto
            ->bind_param(
                'i',
                $id
            );


        $stmtProducto
            ->execute();


        if (
            !$stmtProducto
                ->get_result()
                ->fetch_assoc()
        ) {

            throw new Exception(
                'Uno de los componentes no es válido.'
            );
        }
    }


    // ========================================================
    // REEMPLAZAR COMPONENTES
    // ========================================================

    $stmtDelete =
        $conn->prepare(
            "
            DELETE
            FROM combo_componentes

            WHERE combo_id = ?
            "
        );


    $stmtDelete->bind_param(
        'i',
        $comboId
    );


    $stmtDelete->execute();


    foreach (
        $componentes
        as $componente
    ) {

        $id =
            (int)
            $componente[
                'producto_id'
            ];


        $cantidad =
            (float)
            $componente[
                'cantidad'
            ];


        $stmtInsert =
            $conn->prepare(
                "
                INSERT INTO combo_componentes (
                    combo_id,
                    producto_id,
                    cantidad
                )
                VALUES (
                    ?,
                    ?,
                    ?
                )
                "
            );


        $stmtInsert->bind_param(
            'iid',

            $comboId,
            $id,
            $cantidad
        );


        if (!$stmtInsert->execute()) {

            throw new Exception(
                'No se pudo registrar un componente.'
            );
        }
    }


    $conn->commit();


    responder(
        true,
        'Componentes guardados correctamente.'
    );


} catch (Throwable $e) {

    $conn->rollback();


    responder(
        false,
        $e->getMessage()
    );
}