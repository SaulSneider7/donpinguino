<?php

require_once __DIR__
    . '/catalogo_config.php';


require_once __DIR__
    . '/sistema/config/database.php';


/* ============================================================
   CATEGORÍAS
============================================================ */

$sqlCategorias = "
    SELECT
        c.id,
        c.nombre

    FROM categorias c

    INNER JOIN productos p
        ON p.categoria_id = c.id

    WHERE
        c.activo = 1

        AND p.activo = 1

        AND p.publicar_catalogo = 1

    GROUP BY
        c.id,
        c.nombre,
        c.orden_catalogo

    ORDER BY
        c.orden_catalogo ASC,
        c.nombre ASC
";


$categorias =
    $conn
        ->query(
            $sqlCategorias
        )
        ->fetch_all(
            MYSQLI_ASSOC
        );


/* ============================================================
   PRODUCTOS
============================================================ */

$sqlProductos = "
    SELECT
        p.id,

        p.nombre,
        p.descripcion,
        p.presentacion,

        p.tipo_producto,

        p.precio_regular,
        p.precio_venta,

        p.maneja_stock,
        p.stock_actual,

        p.imagen_url,

        p.destacado_catalogo,

        c.id AS categoria_id,
        c.nombre AS categoria

    FROM productos p

    LEFT JOIN categorias c
        ON c.id = p.categoria_id

    WHERE
        p.activo = 1

        AND p.publicar_catalogo = 1

    ORDER BY
        p.destacado_catalogo DESC,
        p.orden_catalogo ASC,
        p.nombre ASC
";


$productos =
    $conn
        ->query(
            $sqlProductos
        )
        ->fetch_all(
            MYSQLI_ASSOC
        );


function imagenProducto(
    ?string $ruta
): string {

    if (
        !$ruta
    ) {

        return '';
    }


    return SISTEMA_URL
        . ltrim(
            $ruta,
            '/'
        );
}

?>
<!doctype html>

<html lang="es">

<head>

    <meta charset="UTF-8">

    <meta
        name="viewport"
        content="width=device-width, initial-scale=1"
    >


    <title>
        Catálogo | <?= htmlspecialchars(CATALOGO_NOMBRE) ?>
    </title>


    <meta
        name="description"
        content="Catálogo virtual de Don Pingüino. Cervezas, licores, combos, hielo, bebidas y más."
    >


    <!-- Bootstrap -->

    <link
        href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.8/dist/css/bootstrap.min.css"
        rel="stylesheet"
    >


    <!-- Font Awesome -->

    <link
        href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/7.0.0/css/all.min.css"
        rel="stylesheet"
    >


    <!-- CSS -->

    <link
        href="<?= CATALOGO_BASE_URL ?>assets/css/catalogo.css"
        rel="stylesheet"
    >

</head>


<body class="bg-light">


<!-- =========================================================
     NAVBAR
========================================================= -->

<nav
    class="
        navbar
        navbar-expand-lg
        bg-dark
        navbar-dark
        sticky-top
        shadow-sm
    "
>

    <div class="container">

        <a
            class="navbar-brand fw-bold"
            href="/"
        >

            <i class="fa-solid fa-snowflake me-2"></i>

            Don Pingüino

        </a>


        <button
            type="button"
            class="btn btn-warning position-relative"
            data-bs-toggle="offcanvas"
            data-bs-target="#carritoOffcanvas"
        >

            <i class="fa-solid fa-cart-shopping"></i>

            <span class="d-none d-sm-inline ms-1">
                Carrito
            </span>


            <span
                class="
                    position-absolute
                    top-0
                    start-100
                    translate-middle
                    badge
                    rounded-pill
                    bg-danger
                "
                id="cantidadCarrito"
            >
                0
            </span>

        </button>

    </div>

</nav>



<!-- =========================================================
     HERO
========================================================= -->

<section class="bg-dark text-white py-5">

    <div class="container">

        <div class="row align-items-center g-4">

            <div class="col-12 col-lg-7">

                <div
                    class="
                        text-warning
                        fw-semibold
                        small
                        mb-2
                    "
                >
                    CATÁLOGO VIRTUAL
                </div>


                <h1 class="display-5 fw-bold">
                    ¿Qué vamos a tomar hoy?
                </h1>


                <p class="lead text-white-50 mb-0">
                    Encuentra tus bebidas favoritas y envía tu pedido directamente por WhatsApp.
                </p>

            </div>


            <div class="col-12 col-lg-5">

                <div class="input-group input-group-lg">

                    <span class="input-group-text bg-white">

                        <i class="fa-solid fa-magnifying-glass"></i>

                    </span>


                    <input
                        type="search"
                        class="form-control"
                        id="buscarProducto"
                        placeholder="Buscar Pilsen, ron, hielo..."
                        autocomplete="off"
                    >

                </div>

            </div>

        </div>

    </div>

</section>



<!-- =========================================================
     CATÁLOGO
========================================================= -->

<main class="container py-4">


    <!-- CATEGORÍAS -->

    <div
        class="
            d-flex
            gap-2
            overflow-auto
            pb-3
            mb-3
        "
        id="categoriasCatalogo"
    >

        <button
            type="button"
            class="btn btn-dark rounded-pill filtro-categoria active"
            data-categoria="0"
        >
            Todos
        </button>


        <?php foreach ($categorias as $categoria): ?>

            <button
                type="button"
                class="btn btn-outline-dark rounded-pill filtro-categoria text-nowrap"
                data-categoria="<?= (int) $categoria['id'] ?>"
            >

                <?= htmlspecialchars(
                    $categoria['nombre']
                ) ?>

            </button>

        <?php endforeach; ?>

    </div>


    <!-- TÍTULO -->

    <div
        class="
            d-flex
            justify-content-between
            align-items-end
            mb-4
        "
    >

        <div>

            <h2 class="h4 fw-bold mb-1">
                Productos
            </h2>

            <div
                class="text-muted"
                id="cantidadResultados"
            >
            </div>

        </div>

    </div>


    <!-- PRODUCTOS -->

    <div
        class="row g-3"
        id="contenedorProductos"
    >

        <?php foreach ($productos as $producto): ?>

            <?php

            $manejaStock =
                (int)
                $producto['maneja_stock'];


            /*
             * Para SIMPLE:
             * agotado si maneja stock y stock <= 0.
             *
             * En combos posteriormente podemos calcular
             * disponibilidad real por componentes.
             */
            $agotado =
                $producto['tipo_producto'] === 'SIMPLE'
                &&
                $manejaStock === 1
                &&
                (float)
                $producto['stock_actual'] <= 0;


            $imagen =
                imagenProducto(
                    $producto['imagen_url']
                );


            $nombreBusqueda =
                mb_strtolower(
                    $producto['nombre']
                    . ' '
                    . (
                        $producto['presentacion']
                        ?? ''
                    )
                    . ' '
                    . (
                        $producto['categoria']
                        ?? ''
                    )
                );

            ?>


            <div
                class="
                    col-6
                    col-md-4
                    col-lg-3
                    producto-catalogo
                "

                data-categoria="<?= (int) ($producto['categoria_id'] ?? 0) ?>"

                data-busqueda="<?= htmlspecialchars(
                    $nombreBusqueda,
                    ENT_QUOTES
                ) ?>"
            >

                <div
                    class="
                        card
                        border-0
                        shadow-sm
                        h-100
                        overflow-hidden
                    "
                >


                    <!-- IMAGEN -->

                    <div class="producto-imagen">

                        <?php if ($imagen): ?>

                            <img
                                src="<?= htmlspecialchars($imagen) ?>"
                                alt="<?= htmlspecialchars($producto['nombre']) ?>"
                                loading="lazy"
                            >

                        <?php else: ?>

                            <div
                                class="
                                    h-100
                                    d-flex
                                    align-items-center
                                    justify-content-center
                                    bg-body-secondary
                                    text-secondary
                                "
                            >

                                <i class="fa-solid fa-wine-bottle fa-3x"></i>

                            </div>

                        <?php endif; ?>


                        <?php if ((int) $producto['destacado_catalogo'] === 1): ?>

                            <span
                                class="
                                    badge
                                    text-bg-warning
                                    position-absolute
                                    top-0
                                    start-0
                                    m-2
                                "
                            >
                                Destacado
                            </span>

                        <?php endif; ?>


                        <?php if ($agotado): ?>

                            <span
                                class="
                                    badge
                                    text-bg-dark
                                    position-absolute
                                    top-0
                                    end-0
                                    m-2
                                "
                            >
                                Agotado
                            </span>

                        <?php endif; ?>

                    </div>


                    <!-- CUERPO -->

                    <div class="card-body d-flex flex-column">

                        <div
                            class="
                                small
                                text-muted
                                mb-1
                            "
                        >

                            <?= htmlspecialchars(
                                $producto['categoria']
                                ?? 'Otros'
                            ) ?>

                        </div>


                        <h3 class="h6 fw-bold mb-1">

                            <?= htmlspecialchars(
                                $producto['nombre']
                            ) ?>

                        </h3>


                        <?php if ($producto['presentacion']): ?>

                            <div class="small text-muted mb-3">

                                <?= htmlspecialchars(
                                    $producto['presentacion']
                                ) ?>

                            </div>

                        <?php endif; ?>


                        <div class="mt-auto">


                            <!-- PRECIO REGULAR -->

                            <?php
                            if (
                                (float)
                                $producto['precio_regular']
                                >
                                (float)
                                $producto['precio_venta']
                            ):
                            ?>

                                <div
                                    class="
                                        small
                                        text-muted
                                        text-decoration-line-through
                                    "
                                >

                                    S/
                                    <?= number_format(
                                        (float)
                                        $producto['precio_regular'],
                                        2
                                    ) ?>

                                </div>

                            <?php endif; ?>


                            <!-- PRECIO VENTA -->

                            <div class="fs-5 fw-bold mb-3">

                                S/
                                <?= number_format(
                                    (float)
                                    $producto['precio_venta'],
                                    2
                                ) ?>

                            </div>


                            <button
                                type="button"
                                class="
                                    btn
                                    btn-warning
                                    w-100
                                    btn-agregar-carrito
                                "

                                data-id="<?= (int) $producto['id'] ?>"

                                data-nombre="<?= htmlspecialchars(
                                    $producto['nombre'],
                                    ENT_QUOTES
                                ) ?>"

                                data-presentacion="<?= htmlspecialchars(
                                    $producto['presentacion'] ?? '',
                                    ENT_QUOTES
                                ) ?>"

                                data-precio="<?= number_format(
                                    (float)
                                    $producto['precio_venta'],
                                    2,
                                    '.',
                                    ''
                                ) ?>"

                                <?= $agotado
                                    ? 'disabled'
                                    : '' ?>
                            >

                                <?php if ($agotado): ?>

                                    Agotado

                                <?php else: ?>

                                    <i class="fa-solid fa-plus me-1"></i>
                                    Agregar

                                <?php endif; ?>

                            </button>

                        </div>

                    </div>

                </div>

            </div>

        <?php endforeach; ?>

    </div>


    <!-- SIN RESULTADOS -->

    <div
        class="
            text-center
            text-muted
            py-5
            d-none
        "
        id="sinResultados"
    >

        <i class="fa-solid fa-magnifying-glass fa-2x mb-3"></i>

        <div class="fw-semibold">
            No encontramos productos
        </div>

        <div class="small">
            Prueba con otra búsqueda o categoría.
        </div>

    </div>

</main>



<!-- =========================================================
     CARRITO
========================================================= -->

<div
    class="offcanvas offcanvas-end"
    tabindex="-1"
    id="carritoOffcanvas"
>

    <div class="offcanvas-header">

        <div>

            <h5 class="offcanvas-title fw-bold">
                Tu pedido
            </h5>

            <div class="small text-muted">
                Don Pingüino
            </div>

        </div>


        <button
            type="button"
            class="btn-close"
            data-bs-dismiss="offcanvas"
        ></button>

    </div>


    <div class="offcanvas-body d-flex flex-column">

        <div
            class="flex-grow-1"
            id="contenidoCarrito"
        >
        </div>


        <div class="border-top pt-3 mt-3">

            <div
                class="
                    d-flex
                    justify-content-between
                    align-items-center
                    mb-3
                "
            >

                <span class="fw-semibold">
                    Total
                </span>


                <span
                    class="fs-4 fw-bold"
                    id="totalCarrito"
                >
                    S/ 0.00
                </span>

            </div>


            <div class="mb-2">

                <div class="small text-muted text-center mb-2">
                    ¿A qué número deseas enviar el pedido?
                </div>

            </div>


            <div class="d-grid gap-2">

                <button
                    type="button"
                    class="btn btn-success btn-lg btn-pedir-whatsapp"
                    data-whatsapp="1"
                    disabled
                >

                    <i class="fa-brands fa-whatsapp me-2"></i>

                    Pedir por WhatsApp 1

                </button>


                <button
                    type="button"
                    class="btn btn-outline-success btn-lg btn-pedir-whatsapp"
                    data-whatsapp="2"
                    disabled
                >

                    <i class="fa-brands fa-whatsapp me-2"></i>

                    Pedir por WhatsApp 2

                </button>

            </div>

        </div>

    </div>

</div>



<!-- =========================================================
     FOOTER
========================================================= -->

<footer class="bg-dark text-white py-4 mt-5">

    <div class="container text-center">

        <div class="fw-bold">
            Don Pingüino
        </div>

        <div class="small text-white-50">
            Catálogo virtual
        </div>

    </div>

</footer>



<script
    src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.8/dist/js/bootstrap.bundle.min.js"
></script>


<script>

window.CATALOGO_CONFIG = {

    whatsapp1:
        '<?= htmlspecialchars(
            WHATSAPP_PEDIDOS_1,
            ENT_QUOTES
        ) ?>',

    whatsapp2:
        '<?= htmlspecialchars(
            WHATSAPP_PEDIDOS_2,
            ENT_QUOTES
        ) ?>'

};

</script>


<script
    src="<?= CATALOGO_BASE_URL ?>assets/js/catalogo.js"
></script>


</body>
</html>