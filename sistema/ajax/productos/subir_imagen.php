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
   CAPTURAR ERRORES INESPERADOS
============================================================ */

try {


    /* ========================================================
       SESIÓN
    ======================================================== */

    if (!isset($_SESSION['usuario_id'])) {

        http_response_code(401);

        responder(
            false,
            'Sesión expirada.'
        );
    }


    /* ========================================================
       PRODUCTO
    ======================================================== */

    $productoId =
        (int) (
            $_POST['producto_id']
            ?? 0
        );


    if ($productoId <= 0) {

        responder(
            false,
            'Producto inválido.'
        );
    }


    /* ========================================================
       BUSCAR PRODUCTO
    ======================================================== */

    $stmt =
        $conn->prepare(
            "
                SELECT
                    id,
                    imagen_url

                FROM productos

                WHERE id = ?

                LIMIT 1
            "
        );


    if (!$stmt) {

        responder(
            false,
            'Error preparando producto: '
            . $conn->error
        );
    }


    $stmt->bind_param(
        'i',
        $productoId
    );


    $stmt->execute();


    $producto =
        $stmt
            ->get_result()
            ->fetch_assoc();


    if (!$producto) {

        responder(
            false,
            'Producto no encontrado.'
        );
    }


    /* ========================================================
       ARCHIVO
    ======================================================== */

    if (
        !isset($_FILES['imagen'])
    ) {

        responder(
            false,
            'No se recibió ninguna imagen.'
        );
    }


    $archivo =
        $_FILES['imagen'];


    if (
        $archivo['error']
        !== UPLOAD_ERR_OK
    ) {

        responder(
            false,
            'Error al recibir la imagen. Código: '
            . $archivo['error']
        );
    }


    /* ========================================================
       TAMAÑO
    ======================================================== */

    $maximo =
        5 * 1024 * 1024;


    if (
        $archivo['size'] <= 0
    ) {

        responder(
            false,
            'La imagen está vacía.'
        );
    }


    if (
        $archivo['size'] > $maximo
    ) {

        responder(
            false,
            'La imagen no puede superar los 5 MB.'
        );
    }


    /* ========================================================
       VALIDAR ARCHIVO SUBIDO
    ======================================================== */

    if (
        !is_uploaded_file(
            $archivo['tmp_name']
        )
    ) {

        responder(
            false,
            'El archivo recibido no es válido.'
        );
    }


    /* ========================================================
       MIME REAL
    ======================================================== */

    if (
        !class_exists('finfo')
    ) {

        responder(
            false,
            'La extensión Fileinfo de PHP no está habilitada.'
        );
    }


    $finfo =
        new finfo(
            FILEINFO_MIME_TYPE
        );


    $mime =
        $finfo->file(
            $archivo['tmp_name']
        );


    $tiposPermitidos = [

        'image/jpeg' =>
            'jpg',

        'image/png' =>
            'png',

        'image/webp' =>
            'webp'

    ];


    if (
        !isset(
            $tiposPermitidos[$mime]
        )
    ) {

        responder(
            false,
            'Formato no permitido: '
            . $mime
        );
    }


    $extension =
        $tiposPermitidos[$mime];


    /* ========================================================
       DIRECTORIO
    ======================================================== */

    $directorio =
        realpath(
            __DIR__
            . '/../../assets/img'
        );


    if (!$directorio) {

        responder(
            false,
            'No existe la carpeta assets/img.'
        );
    }


    $directorio .=
        DIRECTORY_SEPARATOR
        .
        'productos'
        .
        DIRECTORY_SEPARATOR;


    /*
     * Creamos productos/ si todavía no existe.
     */
    if (
        !is_dir(
            $directorio
        )
    ) {

        if (
            !mkdir(
                $directorio,
                0755,
                true
            )
        ) {

            responder(
                false,
                'No se pudo crear la carpeta de productos.'
            );
        }
    }


    if (
        !is_writable(
            $directorio
        )
    ) {

        responder(
            false,
            'La carpeta de productos no tiene permisos de escritura.'
        );
    }


    /* ========================================================
       NOMBRE ÚNICO
    ======================================================== */

    $nombreArchivo =
        'producto_'
        .
        $productoId
        .
        '_'
        .
        date('Ymd_His')
        .
        '_'
        .
        bin2hex(
            random_bytes(3)
        )
        .
        '.'
        .
        $extension;


    $rutaFisica =
        $directorio
        .
        $nombreArchivo;


    $rutaDb =
        'assets/img/productos/'
        .
        $nombreArchivo;


    /* ========================================================
       MOVER ARCHIVO
    ======================================================== */

    if (
        !move_uploaded_file(
            $archivo['tmp_name'],
            $rutaFisica
        )
    ) {

        responder(
            false,
            'No se pudo mover la imagen a la carpeta de productos.'
        );
    }


    /* ========================================================
       ACTUALIZAR BD
    ======================================================== */

    $stmtUpdate =
        $conn->prepare(
            "
                UPDATE productos

                SET imagen_url = ?

                WHERE id = ?

                LIMIT 1
            "
        );


    if (!$stmtUpdate) {

        @unlink(
            $rutaFisica
        );


        responder(
            false,
            'Error preparando actualización: '
            . $conn->error
        );
    }


    $stmtUpdate->bind_param(
        'si',
        $rutaDb,
        $productoId
    );


    if (
        !$stmtUpdate->execute()
    ) {

        @unlink(
            $rutaFisica
        );


        responder(
            false,
            'No se pudo actualizar la imagen en la base de datos: '
            . $stmtUpdate->error
        );
    }


    /* ========================================================
       BORRAR IMAGEN ANTERIOR
    ======================================================== */

    $imagenAnterior =
        trim(
            $producto['imagen_url']
            ?? ''
        );


    if (
        $imagenAnterior !== ''
        &&
        $imagenAnterior !== $rutaDb
    ) {

        $rutaAnterior =
            __DIR__
            . '/../../'
            . $imagenAnterior;


        if (
            is_file(
                $rutaAnterior
            )
        ) {

            @unlink(
                $rutaAnterior
            );
        }
    }


    /* ========================================================
       RESPUESTA
    ======================================================== */

    responder(
        true,
        'Imagen subida correctamente.',
        [
            'imagen_url' =>
                $rutaDb
        ]
    );


} catch (Throwable $e) {

    responder(
        false,
        'Error interno al subir la imagen: '
        . $e->getMessage()
    );
}