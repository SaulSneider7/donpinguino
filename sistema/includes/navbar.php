<?php

$currentPath =
    parse_url(
        $_SERVER['REQUEST_URI'],
        PHP_URL_PATH
    );


function navActive(
    string $needle,
    string $currentPath
): string {

    return str_contains(
        $currentPath,
        $needle
    )
        ? 'active'
        : '';
}

?>


<!-- =========================================================
     NAVBAR SUPERIOR
     Visible siempre.
========================================================= -->

<nav
    class="navbar navbar-dark bg-dark sticky-top shadow-sm"
>

    <div class="container-fluid">

        <div class="d-flex align-items-center gap-2">

            <!-- BOTÓN SIDEBAR -->
            <button
                class="btn btn-dark border-0"
                type="button"
                data-bs-toggle="offcanvas"
                data-bs-target="#sidebarApp"
                aria-controls="sidebarApp"
            >
                <i class="fa-solid fa-bars fs-5"></i>
            </button>


            <!-- MARCA -->
            <a
                class="navbar-brand fw-bold mb-0"
                href="<?= BASE_URL ?>index.php"
            >
                <img
                    src="<?= BASE_URL ?>/assets/img/favicon.webp"
                    alt="Don Pingüino"
                    class="d-inline-block align-text-top"
                    width="20"
                >
                Don Pingüino
            </a>

        </div>


        <div class="d-flex align-items-center gap-2">

            <!-- ACCESO RÁPIDO A VENTA -->
            <a
                href="<?= BASE_URL ?>modules/ventas/nueva.php"
                class="btn btn-warning fw-semibold d-none d-sm-inline-flex align-items-center"
            >
                <i class="fa-solid fa-plus me-2"></i>
                Nueva venta
            </a>


            <!-- USUARIO -->
            <div class="dropdown">

                <button
                    class="btn btn-outline-light dropdown-toggle"
                    type="button"
                    data-bs-toggle="dropdown"
                    aria-expanded="false"
                >
                    <i class="fa-solid fa-user me-1"></i>

                    <span class="d-none d-md-inline">
                        <?= htmlspecialchars(
                            $_SESSION['usuario_nombre']
                        ) ?>
                    </span>
                </button>


                <ul
                    class="dropdown-menu dropdown-menu-end"
                >

                    <li>

                        <a
                            class="dropdown-item text-danger"
                            href="<?= BASE_URL ?>logout.php"
                        >
                            <i class="fa-solid fa-right-from-bracket me-2"></i>
                            Cerrar sesión
                        </a>

                    </li>

                </ul>

            </div>

        </div>

    </div>

</nav>


<!-- =========================================================
     SIDEBAR / OFFCANVAS
========================================================= -->

<div
    class="offcanvas offcanvas-start text-bg-dark"
    tabindex="-1"
    id="sidebarApp"
    aria-labelledby="sidebarAppLabel"
    style="width: 280px;"
>

    <!-- HEADER -->
    <div class="offcanvas-header border-bottom border-secondary">

        <div>

            <h5
                class="offcanvas-title fw-bold"
                id="sidebarAppLabel"
            >
                <i class="fa-solid fa-store me-2"></i>
                Don Pingüino
            </h5>

            <small class="text-secondary">
                Sistema de gestión
            </small>

        </div>


        <button
            type="button"
            class="btn-close btn-close-white"
            data-bs-dismiss="offcanvas"
            aria-label="Cerrar"
        ></button>

    </div>


    <!-- BODY -->
    <div class="offcanvas-body p-2">

        <!-- =================================================
             PRINCIPAL
        ================================================== -->

        <div class="small text-secondary px-3 mt-2 mb-2">
            PRINCIPAL
        </div>


        <!-- INICIO -->
        <a
            href="<?= BASE_URL ?>index.php"
            class="
                nav-link
                text-white
                rounded
                px-3
                py-2
                mb-1
                <?= navActive(
                    '/index.php',
                    $currentPath
                ) ?>
            "
        >
            <i class="fa-solid fa-house me-3"></i>
            Inicio
        </a>


        <!-- VENTAS -->
        <a
            href="<?= BASE_URL ?>modules/ventas/index.php"
            class="
                nav-link
                text-white
                rounded
                px-3
                py-2
                mb-1
                <?= navActive(
                    '/modules/ventas/',
                    $currentPath
                ) ?>
            "
        >
            <i class="fa-solid fa-cash-register me-3"></i>
            Ventas
        </a>


        <!-- STOCK -->
        <a
            href="<?= BASE_URL ?>modules/stock/index.php"
            class="
                nav-link
                text-white
                rounded
                px-3
                py-2
                mb-1
                <?= navActive(
                    '/modules/stock/',
                    $currentPath
                ) ?>
            "
        >
            <i class="fa-solid fa-boxes-stacked me-3"></i>
            Stock
        </a>


        <!-- CLIENTES -->
        <a
            href="<?= BASE_URL ?>modules/clientes/index.php"
            class="
                nav-link
                text-white
                rounded
                px-3
                py-2
                mb-1
                <?= navActive(
                    '/modules/clientes/',
                    $currentPath
                ) ?>
            "
        >
            <i class="fa-solid fa-users me-3"></i>
            Clientes
        </a>


        <!-- =================================================
             GESTIÓN
        ================================================== -->

        <div class="small text-secondary px-3 mt-4 mb-2">
            GESTIÓN
        </div>


        <!-- COMPRAS -->
        <a
            href="<?= BASE_URL ?>modules/compras/index.php"
            class="
                nav-link
                text-white
                rounded
                px-3
                py-2
                mb-1
                <?= navActive(
                    '/modules/compras/',
                    $currentPath
                ) ?>
            "
        >
            <i class="fa-solid fa-truck-ramp-box me-3"></i>
            Compras
        </a>


        <!-- GASTOS -->
        <a
            href="<?= BASE_URL ?>modules/gastos/index.php"
            class="
                nav-link
                text-white
                rounded
                px-3
                py-2
                mb-1
                <?= navActive(
                    '/modules/gastos/',
                    $currentPath
                ) ?>
            "
        >
            <i class="fa-solid fa-wallet me-3"></i>
            Gastos
        </a>


        <!-- ENVASES -->
        <a
            href="<?= BASE_URL ?>modules/envases/index.php"
            class="
                nav-link
                text-white
                rounded
                px-3
                py-2
                mb-1
                <?= navActive(
                    '/modules/envases/',
                    $currentPath
                ) ?>
            "
        >
            <i class="fa-solid fa-bottle-water me-3"></i>
            Envases
        </a>


        <!-- REGALOS -->
        <a
            href="<?= BASE_URL ?>modules/regalos/index.php"
            class="
                nav-link
                text-white
                rounded
                px-3
                py-2
                mb-1
                <?= navActive(
                    '/modules/regalos/',
                    $currentPath
                ) ?>
            "
        >
            <i class="fa-solid fa-gift me-3"></i>
            Regalos / Premios
        </a>


        <!-- =================================================
             CONFIGURACIÓN COMERCIAL
        ================================================== -->

        <div class="small text-secondary px-3 mt-4 mb-2">
            CATÁLOGO Y VENTAS
        </div>


        <!-- PRODUCTOS -->
        <a
            href="<?= BASE_URL ?>modules/productos/index.php"
            class="
                nav-link
                text-white
                rounded
                px-3
                py-2
                mb-1
                <?= navActive(
                    '/modules/productos/',
                    $currentPath
                ) ?>
            "
        >
            <i class="fa-solid fa-box me-3"></i>
            Productos
        </a>


        <!-- COMBOS -->
        <a
            href="<?= BASE_URL ?>modules/combos/index.php"
            class="
                nav-link
                text-white
                rounded
                px-3
                py-2
                mb-1
                <?= navActive(
                    '/modules/combos/',
                    $currentPath
                ) ?>
            "
        >
            <i class="fa-solid fa-boxes-packing me-3"></i>
            Combos
        </a>


        <!-- PROMOCIONES -->
        <a
            href="<?= BASE_URL ?>modules/promociones/index.php"
            class="
                nav-link
                text-white
                rounded
                px-3
                py-2
                mb-1
                <?= navActive(
                    '/modules/promociones/',
                    $currentPath
                ) ?>
            "
        >
            <i class="fa-solid fa-tags me-3"></i>
            Promociones
        </a>


        <!-- =================================================
             ANÁLISIS
        ================================================== -->

        <div class="small text-secondary px-3 mt-4 mb-2">
            ANÁLISIS
        </div>


        <!-- REPORTES -->
        <a
            href="<?= BASE_URL ?>modules/reportes/index.php"
            class="
                nav-link
                text-white
                rounded
                px-3
                py-2
                mb-1
                <?= navActive(
                    '/modules/reportes/',
                    $currentPath
                ) ?>
            "
        >
            <i class="fa-solid fa-chart-column me-3"></i>
            Reportes
        </a>

    </div>


    <!-- FOOTER -->
    <div class="border-top border-secondary p-3">

        <div class="small text-secondary">
            Sesión iniciada como
        </div>

        <div class="fw-semibold mb-3">
            <?= htmlspecialchars(
                $_SESSION['usuario_nombre']
            ) ?>
        </div>


        <a
            href="<?= BASE_URL ?>logout.php"
            class="btn btn-outline-light btn-sm w-100"
        >
            <i class="fa-solid fa-right-from-bracket me-2"></i>
            Cerrar sesión
        </a>

    </div>

</div>


<!-- =========================================================
     BARRA INFERIOR MÓVIL
========================================================= -->

<nav
    class="
        navbar
        bg-white
        border-top
        fixed-bottom
        d-lg-none
        p-0
    "
>

    <div
        class="
            container-fluid
            d-flex
            justify-content-around
            px-0
        "
    >

        <!-- INICIO -->
        <a
            href="<?= BASE_URL ?>index.php"
            class="
                text-decoration-none
                text-center
                text-dark
                py-2
                flex-fill
            "
        >
            <i class="fa-solid fa-house d-block"></i>

            <small>
                Inicio
            </small>
        </a>


        <!-- VENTAS -->
        <a
            href="<?= BASE_URL ?>modules/ventas/index.php"
            class="
                text-decoration-none
                text-center
                text-dark
                py-2
                flex-fill
            "
        >
            <i class="fa-solid fa-receipt d-block"></i>

            <small>
                Ventas
            </small>
        </a>


        <!-- VENDER -->
        <a
            href="<?= BASE_URL ?>modules/ventas/nueva.php"
            class="
                text-decoration-none
                text-center
                py-2
                flex-fill
                bg-warning
                text-dark
                fw-bold
            "
        >
            <i class="fa-solid fa-plus d-block"></i>

            <small>
                Vender
            </small>
        </a>


        <!-- CLIENTES -->
        <a
            href="<?= BASE_URL ?>modules/clientes/index.php"
            class="
                text-decoration-none
                text-center
                text-dark
                py-2
                flex-fill
            "
        >
            <i class="fa-solid fa-users d-block"></i>

            <small>
                Clientes
            </small>
        </a>


        <!-- MÁS -->
        <button
            type="button"
            class="
                btn
                border-0
                rounded-0
                text-center
                text-dark
                py-2
                flex-fill
            "
            data-bs-toggle="offcanvas"
            data-bs-target="#sidebarApp"
        >
            <i class="fa-solid fa-bars d-block"></i>

            <small>
                Más
            </small>
        </button>

    </div>

</nav>