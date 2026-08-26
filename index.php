<?php

$pageTitle = 'Inicio';

require_once __DIR__ . '/includes/header.php';
require_once __DIR__ . '/includes/navbar.php';

?>

<main class="container-fluid py-4">

    <!-- ======================================================
         ENCABEZADO
    ======================================================= -->

    <div class="d-flex flex-column flex-md-row justify-content-between align-items-md-center gap-3 mb-4">

        <div>

            <h1 class="h3 fw-bold mb-1">
                Inicio
            </h1>

            <p class="text-muted mb-0">
                Resumen de Don Pingüino
            </p>

        </div>


        <a
            href="<?= BASE_URL ?>modules/ventas/nueva.php"
            class="btn btn-warning btn-lg fw-semibold"
        >
            <i class="fa-solid fa-plus me-2"></i>
            Nueva venta
        </a>

    </div>


    <!-- ======================================================
         MÉTRICAS
    ======================================================= -->

    <div class="row g-3 mb-4">

        <!-- VENTAS -->
        <div class="col-12 col-sm-6 col-xl-3">

            <div class="card border-0 shadow-sm h-100">

                <div class="card-body">

                    <div class="d-flex justify-content-between">

                        <div>

                            <div class="text-muted small">
                                Ventas de hoy
                            </div>

                            <div
                                class="fs-3 fw-bold mt-1"
                                id="dashboardVentasHoy"
                            >
                                S/ 0.00
                            </div>

                            <small
                                class="text-muted"
                                id="dashboardVentasCantidad"
                            >
                                0 ventas
                            </small>

                        </div>


                        <div class="fs-2 text-success">

                            <i class="fa-solid fa-cash-register"></i>

                        </div>

                    </div>

                </div>

            </div>

        </div>


        <!-- UTILIDAD -->
        <div class="col-12 col-sm-6 col-xl-3">

            <div class="card border-0 shadow-sm h-100">

                <div class="card-body">

                    <div class="d-flex justify-content-between">

                        <div>

                            <div class="text-muted small">
                                Utilidad bruta hoy
                            </div>

                            <div
                                class="fs-3 fw-bold mt-1"
                                id="dashboardUtilidad"
                            >
                                S/ 0.00
                            </div>

                            <small class="text-muted">
                                Según costos registrados
                            </small>

                        </div>


                        <div class="fs-2 text-primary">

                            <i class="fa-solid fa-chart-line"></i>

                        </div>

                    </div>

                </div>

            </div>

        </div>


        <!-- DEUDA -->
        <div class="col-12 col-sm-6 col-xl-3">

            <div
                class="card border-0 shadow-sm h-100"
                role="button"
                id="cardDeudas"
            >

                <div class="card-body">

                    <div class="d-flex justify-content-between">

                        <div>

                            <div class="text-muted small">
                                Pendiente por cobrar
                            </div>

                            <div
                                class="fs-3 fw-bold text-danger mt-1"
                                id="dashboardDeuda"
                            >
                                S/ 0.00
                            </div>

                            <small
                                class="text-muted"
                                id="dashboardDeudores"
                            >
                                0 clientes
                            </small>

                        </div>


                        <div class="fs-2 text-danger">

                            <i class="fa-solid fa-hand-holding-dollar"></i>

                        </div>

                    </div>

                </div>

            </div>

        </div>


        <!-- STOCK BAJO -->
        <div class="col-12 col-sm-6 col-xl-3">

            <a
                href="<?= BASE_URL ?>modules/stock/index.php"
                class="text-decoration-none"
            >

                <div class="card border-0 shadow-sm h-100">

                    <div class="card-body">

                        <div class="d-flex justify-content-between">

                            <div>

                                <div class="text-muted small">
                                    Stock bajo
                                </div>

                                <div
                                    class="fs-3 fw-bold mt-1"
                                    id="dashboardStockBajo"
                                >
                                    0
                                </div>

                                <small class="text-muted">
                                    Productos
                                </small>

                            </div>


                            <div class="fs-2 text-warning">

                                <i class="fa-solid fa-box-open"></i>

                            </div>

                        </div>

                    </div>

                </div>

            </a>

        </div>


        <!-- ENVASES -->
        <div class="col-12 col-sm-6 col-xl-3">

            <a
                href="<?= BASE_URL ?>modules/envases/index.php"
                class="text-decoration-none"
            >

                <div class="card border-0 shadow-sm h-100">

                    <div class="card-body">

                        <div class="text-muted small">
                            Envases pendientes
                        </div>

                        <div
                            class="fs-3 fw-bold text-warning mt-1"
                            id="dashboardEnvases"
                        >
                            0
                        </div>

                        <small
                            class="text-muted"
                            id="dashboardCuentasEnvases"
                        >
                            0 cuentas
                        </small>

                    </div>

                </div>

            </a>

        </div>


        <!-- COMPRAS -->
        <div class="col-12 col-sm-6 col-xl-3">

            <a
                href="<?= BASE_URL ?>modules/compras/index.php"
                class="text-decoration-none"
            >

                <div class="card border-0 shadow-sm h-100">

                    <div class="card-body">

                        <div class="text-muted small">
                            Compras de hoy
                        </div>

                        <div
                            class="fs-3 fw-bold mt-1"
                            id="dashboardCompras"
                        >
                            S/ 0.00
                        </div>

                        <small
                            class="text-muted"
                            id="dashboardComprasCantidad"
                        >
                            0 compras
                        </small>

                    </div>

                </div>

            </a>

        </div>


        <!-- REGALOS -->
        <div class="col-12 col-sm-6 col-xl-3">

            <a
                href="<?= BASE_URL ?>modules/regalos/index.php"
                class="text-decoration-none"
            >

                <div class="card border-0 shadow-sm h-100">

                    <div class="card-body">

                        <div class="text-muted small">
                            Regalos este mes
                        </div>

                        <div
                            class="fs-3 fw-bold mt-1"
                            id="dashboardRegalos"
                        >
                            S/ 0.00
                        </div>

                        <small
                            class="text-muted"
                            id="dashboardRegalosCantidad"
                        >
                            0 registros
                        </small>

                    </div>

                </div>

            </a>

        </div>


        <!-- GASTOS -->
        <div class="col-12 col-sm-6 col-xl-3">

            <a
                href="<?= BASE_URL ?>modules/gastos/index.php"
                class="text-decoration-none text-reset"
            >

                <div class="card border-0 shadow-sm h-100">

                    <div class="card-body">

                        <div
                            class="
                                d-flex
                                justify-content-between
                                align-items-start
                            "
                        >

                            <div>

                                <div class="small text-muted">
                                    Gastos este mes
                                </div>

                                <div
                                    class="fs-4 fw-bold text-danger mt-1"
                                    id="dashboardGastosMes"
                                >
                                    S/ 0.00
                                </div>

                                <small
                                    class="text-muted"
                                    id="dashboardGastosCantidad"
                                >
                                    0 gastos
                                </small>

                            </div>


                            <div class="text-danger fs-4">

                                <i class="fa-solid fa-wallet"></i>

                            </div>

                        </div>

                    </div>

                </div>

            </a>

        </div>

    </div>


    <!-- ======================================================
         SEGUNDA FILA
    ======================================================= -->

    <div class="row g-3">

        <!-- ÚLTIMAS VENTAS -->
        <div class="col-12 col-xl-7">

            <div class="card border-0 shadow-sm h-100">

                <div class="card-header bg-white border-0 pt-3">

                    <div class="d-flex justify-content-between align-items-center">

                        <h5 class="fw-bold mb-0">
                            Últimas ventas
                        </h5>

                        <a
                            href="<?= BASE_URL ?>modules/ventas/index.php"
                            class="btn btn-outline-secondary btn-sm"
                        >
                            Ver todas
                        </a>

                    </div>

                </div>


                <div
                    class="list-group list-group-flush"
                    id="dashboardUltimasVentas"
                >

                    <div class="text-center py-5">

                        <div class="spinner-border"></div>

                    </div>

                </div>

            </div>

        </div>


        <!-- PRODUCTOS MÁS VENDIDOS -->
        <div class="col-12 col-xl-5">

            <div class="card border-0 shadow-sm h-100">

                <div class="card-header bg-white border-0 pt-3">

                    <h5 class="fw-bold mb-0">
                        Más vendidos este mes
                    </h5>

                </div>


                <div
                    class="list-group list-group-flush"
                    id="dashboardProductos"
                >

                    <div class="text-center py-5">

                        <div class="spinner-border"></div>

                    </div>

                </div>

            </div>

        </div>

    </div>

</main>






<div
    class="modal fade"
    id="modalDeudoresDashboard"
    tabindex="-1"
    aria-hidden="true"
>

    <div
        class="modal-dialog modal-lg modal-dialog-centered modal-dialog-scrollable"
    >

        <div class="modal-content">

            <div class="modal-header">

                <div>

                    <h5 class="modal-title fw-bold">
                        Clientes con deuda
                    </h5>

                    <small class="text-muted">
                        Ventas pendientes por cobrar
                    </small>

                </div>


                <button
                    type="button"
                    class="btn-close"
                    data-bs-dismiss="modal"
                ></button>

            </div>


            <div class="modal-body">

                <div class="card bg-light border-0 mb-3">

                    <div class="card-body">

                        <div class="small text-muted">
                            Total pendiente
                        </div>

                        <div
                            class="fs-3 fw-bold text-danger"
                            id="modalDeudaTotal"
                        >
                            S/ 0.00
                        </div>

                    </div>

                </div>


                <div id="listaDeudoresDashboard"></div>

            </div>


            <div class="modal-footer">

                <a
                    href="<?= BASE_URL ?>modules/clientes/index.php"
                    class="btn btn-outline-secondary"
                >
                    Ver clientes
                </a>


                <button
                    type="button"
                    class="btn btn-secondary"
                    data-bs-dismiss="modal"
                >
                    Cerrar
                </button>

            </div>

        </div>

    </div>

</div>

<script>
document.addEventListener('DOMContentLoaded', function () {

    const modalDeudoresDashboard =
        new bootstrap.Modal(
            document.getElementById(
                'modalDeudoresDashboard'
            )
        );


    let deudoresDashboard = [];

    cargarDashboard();


    function cargarDashboard() {

        $.ajax({

            url:
                '<?= BASE_URL ?>ajax/dashboard/resumen.php',

            type:
                'GET',

            dataType:
                'json',

            success: function (response) {

                if (!response.success) {

                    Swal.fire({
                        icon: 'error',
                        title: 'Error',
                        text: response.message
                    });

                    return;
                }


                pintarDashboard(response);
            },

            error: function (xhr) {

                console.error(
                    xhr.responseText
                );

            }

        });
    }


    function pintarDashboard(data) {

        deudoresDashboard =
            data.deudas.lista || [];

        /* ====================================================
           CARDS
        ==================================================== */

        $('#dashboardVentasHoy')
            .text(
                dinero(
                    data.ventas_hoy.total
                )
            );


        $('#dashboardVentasCantidad')
            .text(
                data.ventas_hoy.cantidad
                +
                (
                    Number(
                        data.ventas_hoy.cantidad
                    ) === 1
                        ? ' venta'
                        : ' ventas'
                )
            );


        $('#dashboardUtilidad')
            .text(
                dinero(
                    data.utilidad_hoy
                )
            );


        $('#dashboardDeuda')
            .text(
                dinero(
                    data.deudas.total
                )
            );


        $('#dashboardDeudores')
            .text(
                data.deudas.clientes
                +
                (
                    Number(
                        data.deudas.clientes
                    ) === 1
                        ? ' cliente'
                        : ' clientes'
                )
            );


        $('#dashboardStockBajo')
            .text(
                data.stock_bajo
            );


        $('#dashboardEnvases')
            .text(
                formatearCantidad(
                    data.envases.cantidad
                )
            );


        $('#dashboardCuentasEnvases')
            .text(
                data.envases.cuentas
                +
                (
                    Number(
                        data.envases.cuentas
                    ) === 1
                        ? ' cuenta'
                        : ' cuentas'
                )
            );


        $('#dashboardCompras')
            .text(
                dinero(
                    data.compras_hoy.total
                )
            );


        $('#dashboardComprasCantidad')
            .text(
                data.compras_hoy.cantidad
                +
                (
                    Number(
                        data.compras_hoy.cantidad
                    ) === 1
                        ? ' compra'
                        : ' compras'
                )
            );


        $('#dashboardRegalos')
            .text(
                dinero(
                    data.regalos_mes.costo
                )
            );


        $('#dashboardRegalosCantidad')
            .text(
                data.regalos_mes.cantidad
                +
                (
                    Number(
                        data.regalos_mes.cantidad
                    ) === 1
                        ? ' registro'
                        : ' registros'
                )
            );

            $('#dashboardGastosMes') .text(
                dinero(
                    data.gastos_mes.total
                )
            );


            $('#dashboardGastosCantidad') .text(
                data.gastos_mes.cantidad
                +
                (
                    Number(
                        data.gastos_mes.cantidad
                    ) === 1
                        ? ' gasto'
                        : ' gastos'
                )
            );
        
        
        $('#cardDeudas')
            .off('click')
            .on(
                'click',
                function () {

                    pintarDeudoresDashboard();

                    modalDeudoresDashboard.show();

                }
            );


        pintarUltimasVentas(
            data.ultimas_ventas
        );


        pintarProductos(
            data.productos_mas_vendidos
        );
    }

    // ========================================================
    // DEUDORES
    // ========================================================

    function pintarDeudoresDashboard() {

            const $lista =
                $('#listaDeudoresDashboard');


            $lista.empty();


            $('#modalDeudaTotal')
                .text(
                    dinero(
                        deudoresDashboard.reduce(
                            function (total, cliente) {

                                return (
                                    total
                                    +
                                    Number(
                                        cliente.deuda_total
                                        || 0
                                    )
                                );
                            },
                            0
                        )
                    )
                );


            if (
                deudoresDashboard.length
                === 0
            ) {

                $lista.html(`

                    <div class="text-center text-success py-5">

                        <i class="fa-solid fa-circle-check fa-2x mb-2"></i>

                        <div class="fw-semibold">
                            No hay clientes con deuda.
                        </div>

                    </div>

                `);

                return;
            }


            deudoresDashboard.forEach(
                function (cliente) {

                    $lista.append(`

                        <div class="border rounded p-3 mb-2">

                            <div
                                class="d-flex flex-column flex-sm-row
                                    justify-content-between
                                    align-items-sm-center
                                    gap-3"
                            >

                                <div>

                                    <div class="fw-semibold">
                                        ${escapeHtml(cliente.nombre)}
                                    </div>

                                    <small class="text-muted">

                                        ${cliente.ventas_pendientes}

                                        ${
                                            Number(
                                                cliente.ventas_pendientes
                                            ) === 1
                                                ? 'venta pendiente'
                                                : 'ventas pendientes'
                                        }

                                    </small>

                                </div>


                                <div class="text-sm-end">

                                    <div class="small text-muted">
                                        Debe
                                    </div>

                                    <div class="fs-5 fw-bold text-danger mb-2">

                                        ${dinero(cliente.deuda_total)}

                                    </div>


                                    <a
                                        href="<?= BASE_URL ?>modules/clientes/index.php?deudas_cliente=${cliente.cliente_id}"
                                        class="btn btn-outline-danger btn-sm"
                                    >
                                        <i class="fa-solid fa-eye me-1"></i>
                                        Ver deudas
                                    </a>

                                </div>

                            </div>

                        </div>

                    `);

                }
            );
        }



    // ========================================================
    // ÚLTIMAS VENTAS
    // ========================================================

    function pintarUltimasVentas(ventas) {

        const $lista =
            $('#dashboardUltimasVentas');


        $lista.empty();


        if (
            !ventas
            ||
            ventas.length === 0
        ) {

            $lista.html(`

                <div class="text-center text-muted py-5">

                    <i class="fa-solid fa-receipt fa-2x mb-2"></i>

                    <div>
                        Todavía no hay ventas.
                    </div>

                </div>

            `);

            return;
        }


        ventas.forEach(
            function (venta) {

                let badge = '';


                switch (
                    venta.estado_pago
                ) {

                    case 'PAGADO':

                        badge = `

                            <span class="badge text-bg-success">
                                Pagado
                            </span>

                        `;

                        break;


                    case 'PARCIAL':

                        badge = `

                            <span class="badge text-bg-warning">
                                Parcial
                            </span>

                        `;

                        break;


                    default:

                        badge = `

                            <span class="badge text-bg-danger">
                                Pendiente
                            </span>

                        `;
                }


                $lista.append(`

                    <a
                        href="<?= BASE_URL ?>modules/ventas/index.php"
                        class="list-group-item list-group-item-action py-3"
                    >

                        <div class="d-flex justify-content-between align-items-center gap-3">

                            <div>

                                <div class="fw-semibold">

                                    #${venta.id}
                                    ·
                                    ${escapeHtml(venta.cliente)}

                                </div>

                                <small class="text-muted">
                                    ${escapeHtml(venta.fecha_formateada)}
                                </small>

                            </div>


                            <div class="text-end">

                                <div class="fw-bold">
                                    ${dinero(venta.total)}
                                </div>

                                ${badge}

                            </div>

                        </div>

                    </a>

                `);

            }
        );
    }


    // ========================================================
    // MÁS VENDIDOS
    // ========================================================

    function pintarProductos(productos) {

        const $lista =
            $('#dashboardProductos');


        $lista.empty();


        if (
            !productos
            ||
            productos.length === 0
        ) {

            $lista.html(`

                <div class="text-center text-muted py-5">

                    <i class="fa-solid fa-chart-bar fa-2x mb-2"></i>

                    <div>
                        Sin datos este mes.
                    </div>

                </div>

            `);

            return;
        }


        productos.forEach(
            function (producto, index) {

                $lista.append(`

                    <div class="list-group-item py-3">

                        <div class="d-flex justify-content-between align-items-center gap-3">

                            <div class="d-flex align-items-center gap-3">

                                <span
                                    class="badge rounded-pill text-bg-dark"
                                >
                                    ${index + 1}
                                </span>


                                <div>

                                    <div class="fw-semibold">
                                        ${escapeHtml(producto.nombre)}
                                    </div>

                                    <small class="text-muted">

                                        ${formatearCantidad(producto.cantidad)}
                                        vendidos

                                    </small>

                                </div>

                            </div>


                            <div class="text-end">

                                <small class="text-muted">
                                    Ventas
                                </small>

                                <div class="fw-semibold">
                                    ${dinero(producto.total_vendido)}
                                </div>

                            </div>

                        </div>

                    </div>

                `);

            }
        );
    }


    function dinero(valor) {

        return (
            'S/ '
            +
            Number(
                valor || 0
            ).toFixed(2)
        );
    }


    function formatearCantidad(valor) {

        const numero =
            Number(
                valor || 0
            );


        if (
            Number.isInteger(numero)
        ) {
            return numero.toString();
        }


        return numero
            .toFixed(3)
            .replace(
                /0+$/,
                ''
            )
            .replace(
                /\.$/,
                ''
            );
    }


    function escapeHtml(text) {

        return $('<div>')
            .text(text ?? '')
            .html();
    }

});
</script>

<?php
require_once __DIR__ . '/includes/footer.php';
?>