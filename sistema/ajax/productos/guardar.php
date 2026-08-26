<?php

session_start();

header(
    'Content-Type: application/json; charset=utf-8'
);

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


/* ============================================================
   SESIÓN
============================================================ */

if (!isset($_SESSION['usuario_id'])) {

    http_response_code(401);

    responder(
        false,
        'Sesión expirada.'
    );
}


/* ============================================================
   DATOS
============================================================ */

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


$categoriaId =
    isset($_POST['categoria_id'])
    && $_POST['categoria_id'] !== ''
        ? (int) $_POST['categoria_id']
        : null;


$descripcion =
    trim(
        $_POST['descripcion']
        ?? ''
    );


$presentacion =
    trim(
        $_POST['presentacion']
        ?? ''
    );


$tipoProducto =
    $_POST['tipo_producto']
    ?? 'SIMPLE';


$costoReferencia =
    (float) (
        $_POST['costo_referencia']
        ?? 0
    );


$precioRegular =
    (float) (
        $_POST['precio_regular']
        ?? 0
    );


$precioVenta =
    (float) (
        $_POST['precio_venta']
        ?? 0
    );


$manejaStock =
    isset($_POST['maneja_stock'])
        ? 1
        : 0;


$stockMinimo =
    (float) (
        $_POST['stock_minimo']
        ?? 0
    );


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
    (float) (
        $_POST['envases_por_unidad']
        ?? 0
    );


/*
 * Esta ruta corresponde a la imagen que YA existe.
 *
 * Si el usuario selecciona una imagen nueva,
 * subir_imagen.php será el encargado de reemplazarla.
 */
$imagenUrl =
    trim(
        $_POST['imagen_url']
        ?? ''
    );


if ($imagenUrl === '') {

    $imagenUrl = null;
}


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
        [
            'SIMPLE',
            'COMBO'
        ],
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
    ||
    $precioRegular < 0
    ||
    $precioVenta < 0
) {

    responder(
        false,
        'Los precios no pueden ser negativos.'
    );
}


if ($stockMinimo < 0) {

    responder(
        false,
        'El stock mínimo no puede ser negativo.'
    );
}


/* ============================================================
   COMBOS
============================================================ */

/*
 * Los combos no tienen stock propio.
 *
 * Su disponibilidad depende del stock
 * de sus componentes.
 */

if ($tipoProducto === 'COMBO') {

    $manejaStock = 0;

    $stockMinimo = 0;
}


if (!$manejaStock) {

    $stockMinimo = 0;
}


/* ============================================================
   ENVASES
============================================================ */

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

    /*
     * Un producto nuevo siempre empieza con stock 0.
     *
     * El inventario real se registra después mediante:
     *
     * Compras
     * Ajustes de inventario
     */

    $stockInicial = 0;


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


    $stmt =
        $conn->prepare(
            $sql
        );


    if (!$stmt) {

        responder(
            false,
            'Error al preparar la consulta: '
            . $conn->error
        );
    }


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
        $stockInicial,
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


    $productoId =
        (int)
        $stmt->insert_id;


    responder(
        true,
        'Producto registrado correctamente.',
        [
            'id' =>
                $productoId
        ]
    );
}


/* ============================================================
   VERIFICAR QUE EL PRODUCTO EXISTA
============================================================ */

$stmtExiste =
    $conn->prepare(
        "
            SELECT id

            FROM productos

            WHERE id = ?

            LIMIT 1
        "
    );


$stmtExiste->bind_param(
    'i',
    $id
);


$stmtExiste->execute();


$productoExiste =
    $stmtExiste
        ->get_result()
        ->fetch_assoc();


if (!$productoExiste) {

    responder(
        false,
        'Producto no encontrado.'
    );
}


/* ============================================================
   EDITAR PRODUCTO
============================================================ */

/*
 * IMPORTANTE:
 *
 * NO actualizamos stock_actual.
 *
 * El stock actual debe conservarse exactamente como está.
 * Solo puede cambiar mediante:
 *
 * - compras
 * - ventas
 * - regalos
 * - ajustes
 *
 * Esto evita modificaciones accidentales del inventario
 * desde el mantenimiento de productos.
 */

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
        stock_minimo = ?,

        controla_envase = ?,
        envases_por_unidad = ?,

        imagen_url = ?,

        publicar_catalogo = ?,
        destacado_catalogo = ?

    WHERE id = ?

    LIMIT 1
";


$stmt =
    $conn->prepare(
        $sql
    );


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
 * i categoria
 * i tipo envase
 *
 * s nombre
 * s descripción
 * s presentación
 * s tipo producto
 *
 * d costo
 * d precio regular
 * d precio venta
 *
 * i maneja stock
 * d stock mínimo
 *
 * i controla envase
 * d envases por unidad
 *
 * s imagen
 *
 * i publicar
 * i destacado
 *
 * i producto ID
 */

$stmt->bind_param(
    'iissssdddididsiii',

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


/* ============================================================
   RESPUESTA
============================================================ */

responder(
    true,
    'Producto actualizado correctamente.',
    [
        'id' =>
            $id
    ]
);