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
    isset($_POST['id']) && $_POST['id'] !== ''
        ? (int) $_POST['id']
        : null;


$nombre = trim($_POST['nombre'] ?? '');


$categoriaId =
    isset($_POST['categoria_id'])
    && $_POST['categoria_id'] !== ''
        ? (int) $_POST['categoria_id']
        : null;


$descripcion =
    trim($_POST['descripcion'] ?? '');


$presentacion =
    trim($_POST['presentacion'] ?? '');


$tipoProducto =
    $_POST['tipo_producto'] ?? 'SIMPLE';


$costoReferencia =
    (float) ($_POST['costo_referencia'] ?? 0);


$precioRegular =
    (float) ($_POST['precio_regular'] ?? 0);


$precioVenta =
    (float) ($_POST['precio_venta'] ?? 0);


$manejaStock =
    isset($_POST['maneja_stock'])
        ? 1
        : 0;


$stockActual =
    (float) ($_POST['stock_actual'] ?? 0);


$stockMinimo =
    (float) ($_POST['stock_minimo'] ?? 0);


$controlaEnvase =
    isset($_POST['controla_envase'])
        ? 1
        : 0;


$tipoEnvaseId =
    isset($_POST['tipo_envase_id'])
    && $_POST['tipo_envase_id'] !== ''
        ? (int) $_POST['tipo_envase_id']
        : null;


$envasesPorUnidad =
    (float) ($_POST['envases_por_unidad'] ?? 0);


$imagenUrl =
    trim($_POST['imagen_url'] ?? '');


$publicarCatalogo =
    isset($_POST['publicar_catalogo'])
        ? 1
        : 0;


$destacadoCatalogo =
    isset($_POST['destacado_catalogo'])
        ? 1
        : 0;


/* ============================================================
   VALIDACIONES
============================================================ */

if ($nombre === '') {

    responder(
        false,
        'Ingrese el nombre del producto.'
    );
}


if (
    !in_array(
        $tipoProducto,
        ['SIMPLE', 'COMBO'],
        true
    )
) {

    responder(
        false,
        'Tipo de producto inválido.'
    );
}


if (
    $costoReferencia < 0
    || $precioRegular < 0
    || $precioVenta < 0
) {

    responder(
        false,
        'Los precios no pueden ser negativos.'
    );
}


if (
    $stockActual < 0
    || $stockMinimo < 0
) {

    responder(
        false,
        'El stock no puede ser negativo.'
    );
}


/*
 * Los combos no manejan stock propio.
 * El stock posteriormente dependerá de sus componentes.
 */
if ($tipoProducto === 'COMBO') {

    $manejaStock = 0;
    $stockActual = 0;
    $stockMinimo = 0;
}


if (!$manejaStock) {

    $stockActual = 0;
    $stockMinimo = 0;
}


/*
 * Si no controla envases, limpiamos esos valores.
 */
if (!$controlaEnvase) {

    $tipoEnvaseId = null;
    $envasesPorUnidad = 0;

} else {

    if (!$tipoEnvaseId) {

        responder(
            false,
            'Seleccione el tipo de envase.'
        );
    }


    if ($envasesPorUnidad <= 0) {

        responder(
            false,
            'Envases por unidad debe ser mayor a cero.'
        );
    }
}


/* ============================================================
   CREAR PRODUCTO
============================================================ */

if ($id === null) {

    $sql = "
        INSERT INTO productos (
            categoria_id,
            tipo_envase_id,

            nombre,
            descripcion,
            presentacion,
            tipo_producto,

            costo_referencia,
            precio_regular,
            precio_venta,

            maneja_stock,
            stock_actual,
            stock_minimo,

            controla_envase,
            envases_por_unidad,

            imagen_url,

            publicar_catalogo,
            destacado_catalogo,

            activo
        )
        VALUES (
            ?, ?,
            ?, ?, ?, ?,
            ?, ?, ?,
            ?, ?, ?,
            ?, ?,
            ?,
            ?, ?,
            1
        )
    ";


    $stmt = $conn->prepare($sql);


    if (!$stmt) {

        responder(
            false,
            'Error al preparar la consulta: '
            . $conn->error
        );
    }


    /*
     * 17 parámetros:
     *
     * i categoriaId
     * i tipoEnvaseId
     *
     * s nombre
     * s descripcion
     * s presentacion
     * s tipoProducto
     *
     * d costoReferencia
     * d precioRegular
     * d precioVenta
     *
     * i manejaStock
     * d stockActual
     * d stockMinimo
     *
     * i controlaEnvase
     * d envasesPorUnidad
     *
     * s imagenUrl
     *
     * i publicarCatalogo
     * i destacadoCatalogo
     */

    $stmt->bind_param(
        'iissssdddiddidsii',

        $categoriaId,
        $tipoEnvaseId,

        $nombre,
        $descripcion,
        $presentacion,
        $tipoProducto,

        $costoReferencia,
        $precioRegular,
        $precioVenta,

        $manejaStock,
        $stockActual,
        $stockMinimo,

        $controlaEnvase,
        $envasesPorUnidad,

        $imagenUrl,

        $publicarCatalogo,
        $destacadoCatalogo
    );


    if (!$stmt->execute()) {

        responder(
            false,
            'No se pudo registrar el producto: '
            . $stmt->error
        );
    }


    responder(
        true,
        'Producto registrado correctamente.',
        [
            'id' => $stmt->insert_id
        ]
    );
}


/* ============================================================
   EDITAR PRODUCTO
============================================================ */

$sql = "
    UPDATE productos
    SET
        categoria_id = ?,
        tipo_envase_id = ?,

        nombre = ?,
        descripcion = ?,
        presentacion = ?,
        tipo_producto = ?,

        costo_referencia = ?,
        precio_regular = ?,
        precio_venta = ?,

        maneja_stock = ?,
        stock_actual = ?,
        stock_minimo = ?,

        controla_envase = ?,
        envases_por_unidad = ?,

        imagen_url = ?,

        publicar_catalogo = ?,
        destacado_catalogo = ?

    WHERE id = ?

    LIMIT 1
";


$stmt = $conn->prepare($sql);


if (!$stmt) {

    responder(
        false,
        'Error al preparar la consulta: '
        . $conn->error
    );
}


/*
 * Los mismos 17 parámetros anteriores
 * + id al final = 18.
 */
$stmt->bind_param(
    'iissssdddiddidsiii',

    $categoriaId,
    $tipoEnvaseId,

    $nombre,
    $descripcion,
    $presentacion,
    $tipoProducto,

    $costoReferencia,
    $precioRegular,
    $precioVenta,

    $manejaStock,
    $stockActual,
    $stockMinimo,

    $controlaEnvase,
    $envasesPorUnidad,

    $imagenUrl,

    $publicarCatalogo,
    $destacadoCatalogo,

    $id
);


if (!$stmt->execute()) {

    responder(
        false,
        'No se pudo actualizar el producto: '
        . $stmt->error
    );
}


responder(
    true,
    'Producto actualizado correctamente.'
);