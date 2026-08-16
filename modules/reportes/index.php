<?php

$pageTitle = 'Reportes';

require_once __DIR__ . '/../../includes/header.php';
require_once __DIR__ . '/../../includes/navbar.php';

$hoy = date('Y-m-d');

?>

<main class="container-fluid py-4">

    <!-- ======================================================
         ENCABEZADO
    ======================================================= -->

    <div class="mb-4">

        <h1 class="h3 fw-bold mb-1">
            Reportes
        </h1>

        <p class="text-muted mb-0">
            Ventas, utilidad, compras, clientes y productos.
        </p>

    </div>


    <!-- ======================================================
         FILTROS
    ======================================================= -->

    <div class="card border-0 shadow-sm mb-4">

        <div class="card-body">

            <div class="d-flex flex-wrap gap-2 mb-3">

                <button
                    type="button"
                    class="btn btn-outline-dark btn-periodo"
                    data-periodo="HOY"
                >
                    Hoy
                </button>

                <button
                    type="button"
                    class="btn btn-outline-dark btn-periodo"
                    data-periodo="AYER"
                >
                    Ayer
                </button>

                <button
                    type="button"
                    class="btn btn-outline-dark btn-periodo"
                    data-periodo="SEMANA"
                >
                    Esta semana
                </button>

                <button
                    type="button"
                    class="btn btn-dark btn-periodo"
                    data-periodo="MES"
                >
                    Este mes
                </button>

                <button
                    type="button"
                    class="btn btn-outline-dark btn-periodo"
                    data-periodo="ANIO"
                >
                    Este año
                </button>

            </div>


            <div class="row g-3 align-items-end">

                <div class="col-12 col-md-4">

                    <label class="form-label">
                        Desde
                    </label>

                    <input
                        type="date"
                        class="form-control"
                        id="fechaInicio"
                    >

                </div>


                <div class="col-12 col-md-4">

                    <label class="form-label">
                        Hasta
                    </label>

                    <input
                        type="date"
                        class="form-control"
                        id="fechaFin"
                    >

                </div>


                <div class="col-12 col-md-4">

                    <button
                        type="button"
                        class="btn btn-dark w-100"
                        id="btnAplicarFiltros"
                    >
                        <i class="fa-solid fa-filter me-2"></i>
                        Aplicar
                    </button>

                </div>

            </div>

        </div>

    </div>


    <!-- ======================================================
         RESUMEN
    ======================================================= -->

    <div class="row g-3 mb-4">

        <!-- VENTAS -->
        <div class="col-12 col-sm-6 col-xl-3">

            <div class="card border-0 shadow-sm h-100">

                <div class="card-body">

                    <div class="small text-muted">
                        Ventas
                    </div>

                    <div
                        class="fs-3 fw-bold mt-1"
                        id="repVentas"
                    >
                        S/ 0.00
                    </div>

                    <small
                        class="text-muted"
                        id="repCantidadVentas"
                    >
                        0 ventas
                    </small>

                </div>

            </div>

        </div>


        <!-- COBRADO -->
        <div class="col-12 col-sm-6 col-xl-3">

            <div class="card border-0 shadow-sm h-100">

                <div class="card-body">

                    <div class="small text-muted">
                        Dinero cobrado
                    </div>

                    <div
                        class="fs-3 fw-bold text-success mt-1"
                        id="repCobrado"
                    >
                        S/ 0.00
                    </div>

                    <small class="text-muted">
                        Pagos recibidos en el período
                    </small>

                </div>

            </div>

        </div>


        <!-- PENDIENTE -->
        <div class="col-12 col-sm-6 col-xl-3">

            <div class="card border-0 shadow-sm h-100">

                <div class="card-body">

                    <div class="small text-muted">
                        Pendiente de ventas del período
                    </div>

                    <div
                        class="fs-3 fw-bold text-danger mt-1"
                        id="repPendiente"
                    >
                        S/ 0.00
                    </div>

                </div>

            </div>

        </div>


        <!-- UTILIDAD -->
        <div class="col-12 col-sm-6 col-xl-3">

            <div class="card border-0 shadow-sm h-100">

                <div class="card-body">

                    <div class="small text-muted">
                        Utilidad bruta
                    </div>

                    <div
                        class="fs-3 fw-bold text-primary mt-1"
                        id="repUtilidad"
                    >
                        S/ 0.00
                    </div>

                    <small class="text-muted">
                        Ventas - costo histórico
                    </small>

                </div>

            </div>

        </div>


        <!-- COSTO -->
        <div class="col-12 col-sm-6 col-xl-3">

            <div class="card border-0 shadow-sm h-100">

                <div class="card-body">

                    <div class="small text-muted">
                        Costo de productos vendidos
                    </div>

                    <div
                        class="fs-4 fw-bold mt-1"
                        id="repCosto"
                    >
                        S/ 0.00
                    </div>

                </div>

            </div>

        </div>


        <!-- COMPRAS -->
        <div class="col-12 col-sm-6 col-xl-3">

            <div class="card border-0 shadow-sm h-100">

                <div class="card-body">

                    <div class="small text-muted">
                        Compras
                    </div>

                    <div
                        class="fs-4 fw-bold mt-1"
                        id="repCompras"
                    >
                        S/ 0.00
                    </div>

                    <small
                        class="text-muted"
                        id="repCantidadCompras"
                    >
                        0 compras
                    </small>

                </div>

            </div>

        </div>


        <!-- REGALOS -->
        <div class="col-12 col-sm-6 col-xl-3">

            <div class="card border-0 shadow-sm h-100">

                <div class="card-body">

                    <div class="small text-muted">
                        Costo de regalos/premios
                    </div>

                    <div
                        class="fs-4 fw-bold mt-1"
                        id="repRegalos"
                    >
                        S/ 0.00
                    </div>

                </div>

            </div>

        </div>


        <!-- TICKET -->
        <div class="col-12 col-sm-6 col-xl-3">

            <div class="card border-0 shadow-sm h-100">

                <div class="card-body">

                    <div class="small text-muted">
                        Ticket promedio
                    </div>

                    <div
                        class="fs-4 fw-bold mt-1"
                        id="repTicket"
                    >
                        S/ 0.00
                    </div>

                </div>

            </div>

        </div>

    </div>


    <!-- ======================================================
         VENTAS POR DÍA
    ======================================================= -->

    <div class="card border-0 shadow-sm mb-4">

        <div class="card-header bg-white border-0 pt-3">

            <h5 class="fw-bold mb-0">
                Ventas por día
            </h5>

        </div>

        <div class="card-body">

            <div
                id="listaVentasDiarias"
                class="row g-2"
            ></div>

        </div>

    </div>


    <!-- ======================================================
         CLIENTES
    ======================================================= -->

    <div class="card border-0 shadow-sm mb-4">

        <div class="card-header bg-white border-0 pt-3">

            <div>

                <h5 class="fw-bold mb-0">
                    Clientes
                </h5>

                <small class="text-muted">
                    Ordena por Total consumido para ver quién más o menos compró.
                </small>

            </div>

        </div>


        <div class="card-body">

            <div class="table-responsive">

                <table
                    id="tablaReporteClientes"
                    class="table table-hover align-middle w-100"
                >

                    <thead>
                    <tr>
                        <th>Cliente</th>
                        <th>Compras</th>
                        <th>Total consumido</th>
                        <th>Ticket promedio</th>
                        <th>Deuda actual</th>
                        <th>Última compra</th>
                    </tr>
                    </thead>

                </table>

            </div>

        </div>

    </div>


    <!-- ======================================================
            DEUDORES
    ======================================================= -->

        <div class="card border-0 shadow-sm mb-4">

            <div class="card-header bg-white border-0 pt-3">

                <div>

                    <h5 class="fw-bold mb-0">
                        Clientes con deuda
                    </h5>

                    <small class="text-muted">
                        Deudas correspondientes a ventas realizadas en el período seleccionado.
                    </small>

                </div>

            </div>


            <div class="card-body">

                <div class="table-responsive">

                    <table
                        id="tablaReporteDeudores"
                        class="table table-hover align-middle w-100"
                    >

                        <thead>
                        <tr>
                            <th>Cliente</th>
                            <th>Ventas pendientes</th>
                            <th>Total vendido</th>
                            <th>Pagado</th>
                            <th>Debe</th>
                            <th>Última venta</th>
                            <th>Acciones</th>
                        </tr>
                        </thead>

                    </table>

                </div>

            </div>

        </div>



        <!-- ======================================================
            ENVASES PENDIENTES
        ======================================================= -->

        <div class="card border-0 shadow-sm mb-4">

            <div class="card-header bg-white border-0 pt-3">

                <div>

                    <h5 class="fw-bold mb-0">
                        Envases pendientes actuales
                    </h5>

                    <small class="text-muted">
                        Saldo actual de botellas retornables por cliente.
                    </small>

                </div>

            </div>


            <div class="card-body">

                <div class="table-responsive">

                    <table
                        id="tablaReporteEnvases"
                        class="table table-hover align-middle w-100"
                    >

                        <thead>
                        <tr>
                            <th>Cliente</th>
                            <th>Tipo de envase</th>
                            <th>Pendientes</th>
                            <th>Último movimiento</th>
                            <th>Acciones</th>
                        </tr>
                        </thead>

                    </table>

                </div>

            </div>

        </div>




    <!-- ======================================================
         PRODUCTOS
    ======================================================= -->

    <div class="card border-0 shadow-sm">

        <div class="card-header bg-white border-0 pt-3">

            <div>

                <h5 class="fw-bold mb-0">
                    Productos
                </h5>

                <small class="text-muted">
                    Rendimiento de productos vendidos en el período.
                </small>

            </div>

        </div>


        <div class="card-body">

            <div class="table-responsive">

                <table
                    id="tablaReporteProductos"
                    class="table table-hover align-middle w-100"
                >

                    <thead>
                    <tr>
                        <th>Producto</th>
                        <th>Cantidad</th>
                        <th>Ingresos</th>
                        <th>Costo</th>
                        <th>Utilidad</th>
                        <th>Margen</th>
                    </tr>
                    </thead>

                </table>

            </div>

        </div>

    </div>

</main>


<script>
document.addEventListener('DOMContentLoaded', function () {

    let tablaClientes = null;
    let tablaProductos = null;
    let tablaDeudores = null;
    let tablaEnvases = null;


    // ========================================================
    // FECHAS INICIALES = MES
    // ========================================================

    establecerPeriodo('MES');

    inicializarTablas();

    cargarTodo();


    // ========================================================
    // BOTONES DE PERÍODO
    // ========================================================

    $('.btn-periodo').on(
        'click',
        function () {

            $('.btn-periodo')
                .removeClass('btn-dark')
                .addClass('btn-outline-dark');


            $(this)
                .removeClass('btn-outline-dark')
                .addClass('btn-dark');


            establecerPeriodo(
                $(this).data('periodo')
            );


            cargarTodo();
        }
    );


    $('#btnAplicarFiltros').on(
        'click',
        function () {

            $('.btn-periodo')
                .removeClass('btn-dark')
                .addClass('btn-outline-dark');


            cargarTodo();
        }
    );


    // ========================================================
    // PERÍODOS
    // ========================================================

    function establecerPeriodo(periodo) {

        const hoy =
            new Date();


        let inicio =
            new Date(hoy);


        let fin =
            new Date(hoy);


        switch (periodo) {

            case 'HOY':

                break;


            case 'AYER':

                inicio.setDate(
                    inicio.getDate() - 1
                );

                fin =
                    new Date(inicio);

                break;


            case 'SEMANA':

                /*
                 * Semana lunes-domingo.
                 */
                const dia =
                    hoy.getDay();

                const diferencia =
                    dia === 0
                        ? -6
                        : 1 - dia;


                inicio =
                    new Date(hoy);

                inicio.setDate(
                    hoy.getDate()
                    + diferencia
                );


                fin =
                    new Date(inicio);

                fin.setDate(
                    inicio.getDate()
                    + 6
                );

                break;


            case 'MES':

                inicio =
                    new Date(
                        hoy.getFullYear(),
                        hoy.getMonth(),
                        1
                    );


                fin =
                    new Date(
                        hoy.getFullYear(),
                        hoy.getMonth() + 1,
                        0
                    );

                break;


            case 'ANIO':

                inicio =
                    new Date(
                        hoy.getFullYear(),
                        0,
                        1
                    );


                fin =
                    new Date(
                        hoy.getFullYear(),
                        11,
                        31
                    );

                break;
        }


        $('#fechaInicio')
            .val(
                fechaInput(inicio)
            );


        $('#fechaFin')
            .val(
                fechaInput(fin)
            );
    }


    function fechaInput(fecha) {

        const y =
            fecha.getFullYear();


        const m =
            String(
                fecha.getMonth() + 1
            ).padStart(2, '0');


        const d =
            String(
                fecha.getDate()
            ).padStart(2, '0');


        return `${y}-${m}-${d}`;
    }


    // ========================================================
    // VALIDAR FECHAS
    // ========================================================

    function fechasValidas() {

        const inicio =
            $('#fechaInicio').val();


        const fin =
            $('#fechaFin').val();


        if (!inicio || !fin) {

            Swal.fire({
                icon: 'warning',
                title: 'Seleccione las fechas'
            });

            return false;
        }


        if (fin < inicio) {

            Swal.fire({
                icon: 'warning',
                title: 'Rango inválido',
                text:
                    'La fecha final no puede ser anterior a la inicial.'
            });

            return false;
        }


        return true;
    }


    // ========================================================
    // CARGAR TODO
    // ========================================================

    function cargarTodo() {

        if (!fechasValidas()) {
            return;
        }


        cargarResumen();

        cargarVentasDiarias();


        if (tablaClientes) {
            tablaClientes.ajax.reload();
        }


        if (tablaProductos) {
            tablaProductos.ajax.reload();
        }

        if (tablaDeudores) {
            tablaDeudores.ajax.reload();
        }
    }


    // ========================================================
    // RESUMEN
    // ========================================================

    function cargarResumen() {

        $.ajax({

            url:
                '<?= BASE_URL ?>ajax/reportes/resumen.php',

            type:
                'GET',

            dataType:
                'json',

            data: {
                fecha_inicio:
                    $('#fechaInicio').val(),

                fecha_fin:
                    $('#fechaFin').val()
            },

            success: function (response) {

                if (!response.success) {

                    Swal.fire({
                        icon: 'error',
                        title: 'Error',
                        text: response.message
                    });

                    return;
                }


                const r =
                    response.data;


                $('#repVentas')
                    .text(
                        dinero(
                            r.ventas
                        )
                    );


                $('#repCantidadVentas')
                    .text(
                        r.cantidad_ventas
                        +
                        (
                            Number(
                                r.cantidad_ventas
                            ) === 1
                                ? ' venta'
                                : ' ventas'
                        )
                    );


                $('#repCobrado')
                    .text(
                        dinero(
                            r.cobrado
                        )
                    );


                $('#repPendiente')
                    .text(
                        dinero(
                            r.pendiente
                        )
                    );


                $('#repCosto')
                    .text(
                        dinero(
                            r.costo_vendido
                        )
                    );


                $('#repUtilidad')
                    .text(
                        dinero(
                            r.utilidad
                        )
                    );


                $('#repCompras')
                    .text(
                        dinero(
                            r.compras
                        )
                    );


                $('#repCantidadCompras')
                    .text(
                        r.cantidad_compras
                        +
                        (
                            Number(
                                r.cantidad_compras
                            ) === 1
                                ? ' compra'
                                : ' compras'
                        )
                    );


                $('#repRegalos')
                    .text(
                        dinero(
                            r.regalos
                        )
                    );


                $('#repTicket')
                    .text(
                        dinero(
                            r.ticket_promedio
                        )
                    );
            },

            error: function (xhr) {

                console.error(
                    xhr.responseText
                );

            }

        });
    }


    // ========================================================
    // VENTAS DIARIAS
    // ========================================================

    function cargarVentasDiarias() {

        $.ajax({

            url:
                '<?= BASE_URL ?>ajax/reportes/ventas_diarias.php',

            type:
                'GET',

            dataType:
                'json',

            data: {
                fecha_inicio:
                    $('#fechaInicio').val(),

                fecha_fin:
                    $('#fechaFin').val()
            },

            success: function (response) {

                if (!response.success) {
                    return;
                }


                const $lista =
                    $('#listaVentasDiarias');


                $lista.empty();


                if (
                    response.data.length
                    === 0
                ) {

                    $lista.html(`

                        <div class="col-12">

                            <div class="text-center text-muted py-4">
                                No existen ventas en este período.
                            </div>

                        </div>

                    `);

                    return;
                }


                response.data.forEach(
                    function (dia) {

                        $lista.append(`

                            <div class="col-12 col-sm-6 col-lg-4 col-xl-3">

                                <div class="border rounded p-3 h-100">

                                    <div class="small text-muted">
                                        ${escapeHtml(dia.fecha_formateada)}
                                    </div>

                                    <div class="fs-5 fw-bold">
                                        ${dinero(dia.total)}
                                    </div>

                                    <small class="text-muted">
                                        ${dia.cantidad}
                                        ${
                                            Number(dia.cantidad) === 1
                                                ? 'venta'
                                                : 'ventas'
                                        }
                                    </small>

                                </div>

                            </div>

                        `);

                    }
                );
            }

        });
    }


    // ========================================================
    // DATATABLES
    // ========================================================

    function inicializarTablas() {

        tablaClientes =
            $('#tablaReporteClientes')
                .DataTable({

                    processing:
                        true,

                    serverSide:
                        true,

                    ajax: {

                        url:
                            '<?= BASE_URL ?>ajax/reportes/clientes.php',

                        type:
                            'POST',

                        data: function (d) {

                            d.fecha_inicio =
                                $('#fechaInicio').val();

                            d.fecha_fin =
                                $('#fechaFin').val();
                        }
                    },

                    order: [
                        [2, 'desc']
                    ],

                    pageLength:
                        10,

                    columns: [

                        {
                            data: 'cliente'
                        },

                        {
                            data: 'compras'
                        },

                        {
                            data: 'consumo'
                        },

                        {
                            data: 'ticket'
                        },

                        {
                            data: 'deuda'
                        },

                        {
                            data: 'ultima_compra'
                        }

                    ],

                    language: {
                        url:
                            'https://cdn.datatables.net/plug-ins/2.3.3/i18n/es-ES.json'
                    }

                });


        tablaProductos =
            $('#tablaReporteProductos')
                .DataTable({

                    processing:
                        true,

                    serverSide:
                        true,

                    ajax: {

                        url:
                            '<?= BASE_URL ?>ajax/reportes/productos.php',

                        type:
                            'POST',

                        data: function (d) {

                            d.fecha_inicio =
                                $('#fechaInicio').val();

                            d.fecha_fin =
                                $('#fechaFin').val();
                        }
                    },

                    order: [
                        [1, 'desc']
                    ],

                    pageLength:
                        10,

                    columns: [

                        {
                            data: 'producto'
                        },

                        {
                            data: 'cantidad'
                        },

                        {
                            data: 'ingresos'
                        },

                        {
                            data: 'costo'
                        },

                        {
                            data: 'utilidad'
                        },

                        {
                            data: 'margen'
                        }

                    ],

                    language: {
                        url:
                            'https://cdn.datatables.net/plug-ins/2.3.3/i18n/es-ES.json'
                    }

                });

        // ========================================================
        // DEUDORES
        // ========================================================

        tablaDeudores =
            $('#tablaReporteDeudores')
                .DataTable({

                    processing: true,
                    serverSide: true,

                    ajax: {

                        url:
                            '<?= BASE_URL ?>ajax/reportes/deudores.php',

                        type:
                            'POST',

                        data: function (d) {

                            d.fecha_inicio =
                                $('#fechaInicio').val();

                            d.fecha_fin =
                                $('#fechaFin').val();
                        }
                    },

                    order: [
                        [4, 'desc']
                    ],

                    pageLength:
                        10,

                    columns: [

                        {
                            data: 'cliente'
                        },

                        {
                            data: 'ventas_pendientes'
                        },

                        {
                            data: 'total_vendido'
                        },

                        {
                            data: 'pagado'
                        },

                        {
                            data: 'deuda'
                        },

                        {
                            data: 'ultima_venta'
                        },

                        {
                            data: 'acciones',
                            orderable: false,
                            searchable: false
                        }

                    ],

                    language: {

                        url:
                            'https://cdn.datatables.net/plug-ins/2.3.3/i18n/es-ES.json'
                    }

                });


        // ========================================================
        // ENVASES
        // ========================================================

        tablaEnvases =
            $('#tablaReporteEnvases')
                .DataTable({

                    processing: true,
                    serverSide: true,

                    ajax: {

                        url:
                            '<?= BASE_URL ?>ajax/reportes/envases.php',

                        type:
                            'POST'
                    },

                    order: [
                        [2, 'desc']
                    ],

                    pageLength:
                        10,

                    columns: [

                        {
                            data: 'cliente'
                        },

                        {
                            data: 'tipo_envase'
                        },

                        {
                            data: 'saldo'
                        },

                        {
                            data: 'ultima_actualizacion'
                        },

                        {
                            data: 'acciones',
                            orderable: false,
                            searchable: false
                        }

                    ],

                    language: {

                        url:
                            'https://cdn.datatables.net/plug-ins/2.3.3/i18n/es-ES.json'
                    }

                });
    }


    // ========================================================
    // HELPERS
    // ========================================================

    function dinero(valor) {

        return (
            'S/ '
            +
            Number(
                valor || 0
            ).toFixed(2)
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
require_once __DIR__ . '/../../includes/footer.php';
?>