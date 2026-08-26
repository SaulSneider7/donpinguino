<?php

$pageTitle = 'Gráficos';

require_once __DIR__ . '/../../includes/header.php';
require_once __DIR__ . '/../../includes/navbar.php';

$anioActual = (int) date('Y');

?>

<main class="container-fluid py-4">

    <!-- =====================================================
         CABECERA
    ====================================================== -->

    <div
        class="
            d-flex
            flex-column
            flex-md-row
            justify-content-between
            align-items-md-center
            gap-3
            mb-4
        "
    >

        <div>

            <h1 class="h3 fw-bold mb-1">
                Gráficos
            </h1>

            <p class="text-muted mb-0">
                Evolución y comportamiento del negocio.
            </p>

        </div>


        <!-- AÑO -->

        <div>

            <label
                for="filtroAnio"
                class="form-label small text-muted mb-1"
            >
                Año
            </label>

            <select
                class="form-select"
                id="filtroAnio"
                style="min-width: 130px;"
            >

                <?php
                for (
                    $anio = $anioActual;
                    $anio >= $anioActual - 4;
                    $anio--
                ):
                ?>

                    <option
                        value="<?= $anio ?>"
                        <?= $anio === $anioActual
                            ? 'selected'
                            : '' ?>
                    >
                        <?= $anio ?>
                    </option>

                <?php endfor; ?>

            </select>

        </div>

    </div>


    <!-- =====================================================
         NAVEGACIÓN REPORTES
    ====================================================== -->

    <div class="mb-4">

        <div class="btn-group">

            <a
                href="<?= BASE_URL ?>modules/reportes/index.php"
                class="btn btn-outline-dark"
            >
                <i class="fa-solid fa-table-list me-2"></i>
                Resumen
            </a>

            <a
                href="<?= BASE_URL ?>modules/reportes/graficos.php"
                class="btn btn-dark"
            >
                <i class="fa-solid fa-chart-line me-2"></i>
                Gráficos
            </a>

        </div>

    </div>


    <!-- =====================================================
         RESUMEN DEL AÑO
    ====================================================== -->

    <div class="row g-3 mb-4">

        <!-- VENTAS -->

        <div class="col-12 col-md-4">

            <div class="card border-0 shadow-sm h-100">

                <div class="card-body">

                    <div class="small text-muted">
                        Ventas del año
                    </div>

                    <div
                        class="fs-4 fw-bold mt-1"
                        id="totalVentasAnio"
                    >
                        S/ 0.00
                    </div>

                </div>

            </div>

        </div>


        <!-- UTILIDAD -->

        <div class="col-12 col-md-4">

            <div class="card border-0 shadow-sm h-100">

                <div class="card-body">

                    <div class="small text-muted">
                        Utilidad bruta
                    </div>

                    <div
                        class="fs-4 fw-bold text-success mt-1"
                        id="totalUtilidadAnio"
                    >
                        S/ 0.00
                    </div>

                </div>

            </div>

        </div>


        <!-- GANANCIA -->

        <div class="col-12 col-md-4">

            <div class="card border-0 shadow-sm h-100">

                <div class="card-body">

                    <div class="small text-muted">
                        Ganancia después de gastos
                    </div>

                    <div
                        class="fs-4 fw-bold mt-1"
                        id="totalGananciaAnio"
                    >
                        S/ 0.00
                    </div>

                </div>

            </div>

        </div>

    </div>


    <!-- =====================================================
         GRÁFICO 1
    ====================================================== -->

    <div class="card border-0 shadow-sm">

        <div class="card-header bg-white border-0 pt-4 px-4">

            <h5 class="fw-bold mb-1">
                Evolución mensual
            </h5>

            <p class="text-muted small mb-0">
                Ventas, utilidad bruta y ganancia después de gastos.
            </p>

        </div>


        <div class="card-body">

            <!--
                Este contenedor evita que Chart.js
                crezca indefinidamente.
            -->

            <div
                class="position-relative"
                style="height: 420px;"
            >

                <canvas id="graficoMensual"></canvas>

            </div>

        </div>

    </div>


    <!-- =====================================================
         GRÁFICO SEMANAL
    ====================================================== -->

    <div class="card border-0 shadow-sm mt-4">

        <div class="card-header bg-white border-0 pt-4 px-4">

            <div
                class="
                    d-flex
                    flex-column
                    flex-lg-row
                    justify-content-between
                    gap-3
                "
            >

                <div>

                    <h5 class="fw-bold mb-1">
                        Análisis por período
                    </h5>

                    <p class="text-muted small mb-0">
                        Analiza ventas, productos y clientes en un período específico.
                    </p>

                </div>

            </div>

        </div>


        <div class="card-body">

            <!-- FILTROS -->

            <div class="row g-3 mb-4">

                <!-- PERIODO -->

                <div class="col-12 col-md-6 col-xl-3">

                    <label class="form-label small text-muted">
                        Período
                    </label>

                    <select
                        class="form-select"
                        id="filtroPeriodoGrafico"
                    >

                        <option value="HOY">
                            Hoy
                        </option>

                        <option value="AYER">
                            Ayer
                        </option>

                        <option value="SEMANA" selected>
                            Esta semana
                        </option>

                        <option value="MES">
                            Este mes
                        </option>

                        <option value="ANIO">
                            Este año
                        </option>

                        <option value="PERSONALIZADO">
                            Personalizado
                        </option>

                    </select>

                </div>


                <!-- PRODUCTO -->

                <div class="col-12 col-md-6 col-xl-3">

                    <label class="form-label small text-muted">
                        Producto
                    </label>

                    <select
                        class="form-select"
                        id="filtroProductoPeriodo"
                    >
                    </select>

                </div>


                <!-- CLIENTE -->

                <div class="col-12 col-md-6 col-xl-3">

                    <label class="form-label small text-muted">
                        Cliente
                    </label>

                    <select
                        class="form-select"
                        id="filtroClientePeriodo"
                    >
                    </select>

                </div>


                <!-- MÉTRICA -->

                <div class="col-12 col-md-6 col-xl-3">

                    <label class="form-label small text-muted">
                        Ver
                    </label>

                    <select
                        class="form-select"
                        id="filtroMetricaPeriodo"
                    >

                        <option value="VENTAS">
                            Ventas
                        </option>

                        <option value="UTILIDAD">
                            Utilidad bruta
                        </option>

                        <option value="UNIDADES">
                            Unidades vendidas
                        </option>

                        <option value="CANTIDAD_VENTAS">
                            Número de ventas
                        </option>

                    </select>

                </div>

            </div>


            <!-- FECHAS PERSONALIZADAS -->

            <div
                class="row g-3 mb-4 d-none"
                id="contenedorFechasPeriodo"
            >

                <div class="col-12 col-md-6">

                    <label class="form-label small text-muted">
                        Desde
                    </label>

                    <input
                        type="date"
                        class="form-control"
                        id="fechaDesdePeriodo"
                    >

                </div>


                <div class="col-12 col-md-6">

                    <label class="form-label small text-muted">
                        Hasta
                    </label>

                    <input
                        type="date"
                        class="form-control"
                        id="fechaHastaPeriodo"
                    >

                </div>

            </div>


            <!-- TÍTULO DINÁMICO -->

            <div class="mb-3">

                <h6
                    class="fw-bold mb-1"
                    id="tituloGraficoPeriodo"
                >
                    Ventas de esta semana
                </h6>

                <div
                    class="text-muted small"
                    id="descripcionGraficoPeriodo"
                >
                    Evolución diaria durante la semana actual.
                </div>

            </div>


            <!-- GRÁFICO -->

            <div
                class="position-relative"
                style="height: 420px;"
            >

                <canvas id="graficoPeriodo"></canvas>

            </div>

        </div>

    </div>    








    <!-- =========================================================
        FILTRO DEL MES
    ========================================================= -->

    <div
        class="
            d-flex
            flex-column
            flex-sm-row
            justify-content-between
            align-items-sm-center
            gap-3
            mt-4
            mb-3
        "
    >

        <div>
            <h5 class="fw-bold mb-1">
                Análisis mensual
            </h5>
            <div class="text-muted small">
                Gastos y productos del mes seleccionado.
            </div>
        </div>


        <div style="min-width: 180px;">
            <label
                for="filtroMesDetalle"
                class="form-label small text-muted mb-1"
            >
                Mes
            </label>
            <select
                class="form-select"
                id="filtroMesDetalle"
            >
                <option value="1">Enero</option>
                <option value="2">Febrero</option>
                <option value="3">Marzo</option>
                <option value="4">Abril</option>
                <option value="5">Mayo</option>
                <option value="6">Junio</option>
                <option value="7">Julio</option>
                <option value="8">Agosto</option>
                <option value="9">Septiembre</option>
                <option value="10">Octubre</option>
                <option value="11">Noviembre</option>
                <option value="12">Diciembre</option>
            </select>
        </div>
    </div>
    

    <!-- =========================================================
        SEGUNDA FILA DE GRÁFICOS
    ========================================================= -->

    <div class="row g-4 mt-1">


        <!-- =====================================================
            DISTRIBUCIÓN DE GASTOS
        ====================================================== -->

        <div class="col-12 col-xl-5">

            <div class="card border-0 shadow-sm h-100">

                <div class="card-header bg-white border-0 pt-4 px-4">

                    <h5 class="fw-bold mb-1">
                        Distribución de gastos
                    </h5>

                    <p class="text-muted small mb-0">
                        En qué se está gastando el dinero.
                    </p>

                </div>


                <div class="card-body">

                    <div class="text-center mb-3">

                        <div class="small text-muted">
                            Total de gastos
                        </div>

                        <div
                            class="fs-4 fw-bold text-danger"
                            id="totalGastosGrafico"
                        >
                            S/ 0.00
                        </div>

                    </div>


                    <div
                        class="position-relative"
                        style="height: 330px;"
                    >
                        <canvas id="graficoGastos"></canvas>
                    </div>


                    <div
                        class="text-center text-muted py-5 d-none"
                        id="sinGastos"
                    >
                        <i class="fa-solid fa-chart-pie fs-2 mb-3 d-block"></i>

                        No hay gastos registrados para este año.
                    </div>

                </div>

            </div>

        </div>






        <!-- =====================================================
            TOP PRODUCTOS
        ====================================================== -->

        <div class="col-12 col-xl-7">

            <div class="card border-0 shadow-sm h-100">

                <div class="card-header bg-white border-0 pt-4 px-4">

                    <h5 class="fw-bold mb-1">
                        Productos más vendidos
                    </h5>

                    <p class="text-muted small mb-0">
                        Top 5 según cantidad vendida.
                    </p>

                </div>


                <div class="card-body">

                    <div
                        class="position-relative"
                        style="height: 390px;"
                    >
                        <canvas id="graficoProductos"></canvas>
                    </div>


                    <div
                        class="text-center text-muted py-5 d-none"
                        id="sinProductos"
                    >
                        <i class="fa-solid fa-box-open fs-2 mb-3 d-block"></i>

                        No hay ventas registradas para este año.
                    </div>

                </div>

            </div>

        </div>

    </div>


    <!-- =========================================================
        GRAFICO DE CONSUMO POR CLIENTE
    ========================================================= -->
    <div class="card border-0 shadow-sm mt-4">

        <div class="card-header bg-white border-0 pt-4 px-4">

            <div
                class="
                    d-flex
                    flex-column
                    flex-md-row
                    justify-content-between
                    gap-3
                "
            >

                <div>

                    <h5
                        class="fw-bold mb-1"
                        id="tituloGraficoCliente"
                    >
                        Clientes que más consumen
                    </h5>

                    <p
                        class="text-muted small mb-0"
                        id="descripcionGraficoCliente"
                    >
                        Top 5 clientes con mayor consumo durante el año actual.
                    </p>

                </div>


                <div style="min-width: 280px;">

                    <label
                        class="form-label small text-muted mb-1"
                    >
                        Cliente
                    </label>

                    <select
                        class="form-select"
                        id="filtroClienteGrafico"
                    >
                    </select>

                </div>

            </div>

        </div>


        <div class="card-body">

            <div
                class="position-relative"
                style="height: 400px;"
            >

                <canvas id="graficoCliente"></canvas>

            </div>

        </div>

    </div>

</main>


<!-- =========================================================
     CHART.JS
========================================================= -->

<script
    src="https://cdn.jsdelivr.net/npm/chart.js@4.4.7/dist/chart.umd.min.js"
></script>


<script>

document.addEventListener( 'DOMContentLoaded', function () {
    let graficoMensual = null;
    let graficoGastos = null;
    let graficoProductos = null;
    let graficoCliente = null;
    let graficoPeriodo = null;

    $('#filtroClienteGrafico').on(
        'change',
        function () {
            cargarGraficoClientes();
        }
    );

    $('#filtroMesDetalle') .val(
        <?= (int) date('n') ?>
    );

    $('#filtroMesDetalle').on(
        'change',
        function () {
            cargarGraficoGastos();
            cargarGraficoProductos();
        }
    );



    $('#filtroClienteGrafico') .select2({
            theme:
                'bootstrap-5',
            width:
                '100%',
            placeholder:
                'Top 5 clientes',
            allowClear:
                true,
            minimumInputLength:
                0,
            ajax: {
                url:
                    '<?= BASE_URL ?>ajax/clientes/buscar.php',
                dataType:
                    'json',
                delay:
                    250,
                data:
                    function (params) {
                        return {
                            q:
                                params.term
                                || ''
                        };
                    },
                processResults:
                    function (data) {
                        return data;
                    }
                }
    });
        
    // ====================================================
    // GRAFICO DE CLIENTE
    // ====================================================    

    function cargarGraficoCliente(clienteId) {

            $.ajax({

                url:
                    '<?= BASE_URL ?>ajax/reportes/cliente_mensual.php',

                type:
                    'GET',

                dataType:
                    'json',

                data: {
                    cliente_id:
                        clienteId
                },


                success:
                    function (response) {

                        if (
                            !response.success
                        ) {

                            Swal.fire({
                                icon:
                                    'error',

                                title:
                                    'Error',

                                text:
                                    response.message
                            });

                            return;
                        }


                        pintarGraficoCliente(
                            response
                        );

                    }

            });

    }

    function cargarGraficoClientes() {

            const clienteId =
                Number(
                    $('#filtroClienteGrafico').val()
                    || 0
                );


            if (
                clienteId > 0
            ) {

                cargarGraficoClienteIndividual(
                    clienteId
                );

            } else {

                cargarTopClientes();

            }

    }



    function cargarTopClientes() {

            $.ajax({

                url:
                    '<?= BASE_URL ?>ajax/reportes/top_clientes.php',

                type:
                    'GET',

                dataType:
                    'json',


                success:
                    function (response) {

                        if (
                            !response.success
                        ) {

                            console.error(
                                response.message
                            );

                            return;
                        }


                        $('#tituloGraficoCliente')
                            .text(
                                'Clientes que más consumen'
                            );


                        $('#descripcionGraficoCliente')
                            .text(
                                'Top 5 clientes con mayor consumo durante el año actual.'
                            );


                        pintarTopClientes(
                            response.data
                        );

                    },


                error:
                    function (xhr) {

                        console.error(
                            xhr.responseText
                        );

                    }

            });

    }


    function pintarTopClientes(data) {

            const canvas =
                document.getElementById(
                    'graficoCliente'
                );


            if (
                graficoCliente
            ) {

                graficoCliente.destroy();

                graficoCliente = null;
            }


            const nombres =
                data.map(
                    fila =>
                        fila.cliente
                );


            const consumos =
                data.map(
                    fila =>
                        Number(
                            fila.consumo
                        )
                );


            graficoCliente =
                new Chart(
                    canvas,
                    {

                        type:
                            'bar',


                        data: {

                            labels:
                                nombres,


                            datasets: [

                                {

                                    label:
                                        'Consumo',

                                    data:
                                        consumos,

                                    borderWidth:
                                        1

                                }

                            ]

                        },


                        options: {

                            indexAxis:
                                'y',


                            responsive:
                                true,

                            maintainAspectRatio:
                                false,


                            plugins: {

                                legend: {

                                    display:
                                        false

                                },


                                tooltip: {

                                    callbacks: {

                                        label:
                                            function (
                                                context
                                            ) {

                                                const fila =
                                                    data[
                                                        context
                                                            .dataIndex
                                                    ];


                                                return [

                                                    'Consumo: '
                                                    +
                                                    moneda(
                                                        fila.consumo
                                                    ),

                                                    'Compras: '
                                                    +
                                                    fila.compras

                                                ];

                                            }

                                    }

                                }

                            },


                            scales: {

                                x: {

                                    beginAtZero:
                                        true,

                                    ticks: {

                                        callback:
                                            function (
                                                value
                                            ) {

                                                return (
                                                    'S/ '
                                                    +
                                                    Number(
                                                        value
                                                    )
                                                    .toLocaleString(
                                                        'es-PE'
                                                    )
                                                );

                                            }

                                    }

                                }

                            }

                        }

                    }
                );
    }


    function cargarGraficoClienteIndividual(clienteId) {

            $.ajax({

                url:
                    '<?= BASE_URL ?>ajax/reportes/cliente_mensual.php',

                type:
                    'GET',

                dataType:
                    'json',

                data: {
                    cliente_id:
                        clienteId
                },


                success:
                    function (response) {

                        if (
                            !response.success
                        ) {

                            Swal.fire({
                                icon:
                                    'error',

                                title:
                                    'Error',

                                text:
                                    response.message
                            });

                            return;
                        }


                        $('#tituloGraficoCliente')
                            .text(
                                response
                                    .cliente
                                    .nombre
                            );


                        $('#descripcionGraficoCliente')
                            .text(
                                'Consumo mensual del cliente durante '
                                +
                                response.anio
                                +
                                '.'
                            );


                        pintarClienteMensual(
                            response
                        );

                    }

            });

    }



    function pintarClienteMensual( response ) {

            const canvas =
                document.getElementById(
                    'graficoCliente'
                );


            if (
                graficoCliente
            ) {

                graficoCliente.destroy();

                graficoCliente = null;
            }


            graficoCliente =
                new Chart(
                    canvas,
                    {

                        type:
                            'line',


                        data: {

                            labels:
                                response.data.map(
                                    fila =>
                                        fila.nombre
                                ),


                            datasets: [

                                {

                                    label:
                                        'Consumo',

                                    data:
                                        response.data.map(
                                            fila =>
                                                Number(
                                                    fila.total
                                                )
                                        ),

                                    tension:
                                        0.3,

                                    borderWidth:
                                        3,

                                    pointRadius:
                                        4,

                                    pointHoverRadius:
                                        6

                                }

                            ]

                        },


                        options: {

                            responsive:
                                true,

                            maintainAspectRatio:
                                false,


                            interaction: {

                                mode:
                                    'index',

                                intersect:
                                    false

                            },


                            plugins: {

                                legend: {

                                    display:
                                        false

                                },


                                tooltip: {

                                    callbacks: {

                                        label:
                                            function (
                                                context
                                            ) {

                                                const fila =
                                                    response
                                                        .data[
                                                            context
                                                                .dataIndex
                                                        ];


                                                return [

                                                    'Consumo: '
                                                    +
                                                    moneda(
                                                        fila.total
                                                    ),

                                                    'Compras: '
                                                    +
                                                    fila.compras

                                                ];

                                            }

                                    }

                                }

                            },


                            scales: {

                                y: {

                                    beginAtZero:
                                        true,

                                    ticks: {

                                        callback:
                                            function (
                                                value
                                            ) {

                                                return (
                                                    'S/ '
                                                    +
                                                    Number(
                                                        value
                                                    )
                                                    .toLocaleString(
                                                        'es-PE'
                                                    )
                                                );

                                            }

                                    }

                                }

                            }

                        }

                    }
                );
    }


    function pintarGraficoCliente( response ) {

            const canvas =
                document.getElementById(
                    'graficoCliente'
                );


            if (
                graficoCliente
            ) {

                graficoCliente.destroy();

            }


            $('#mensajeSeleccionCliente')
                .addClass(
                    'd-none'
                );


            $('#contenedorGraficoCliente')
                .removeClass(
                    'd-none'
                );


            graficoCliente =
                new Chart(
                    canvas,
                    {

                        type:
                            'line',


                        data: {

                            labels:
                                response.data.map(
                                    fila =>
                                        fila.nombre
                                ),


                            datasets: [

                                {

                                    label:
                                        'Consumo',

                                    data:
                                        response.data.map(
                                            fila =>
                                                Number(
                                                    fila.total
                                                )
                                        ),

                                    tension:
                                        0.3,

                                    borderWidth:
                                        3,

                                    pointRadius:
                                        4,

                                    pointHoverRadius:
                                        6

                                }

                            ]

                        },


                        options: {

                            responsive:
                                true,

                            maintainAspectRatio:
                                false,


                            plugins: {

                                legend: {

                                    display:
                                        false

                                },


                                tooltip: {

                                    callbacks: {

                                        label:
                                            function (
                                                context
                                            ) {

                                                const fila =
                                                    response.data[
                                                        context
                                                            .dataIndex
                                                    ];


                                                return [
                                                    'Consumo: '
                                                    +
                                                    moneda(
                                                        fila.total
                                                    ),

                                                    'Compras: '
                                                    +
                                                    fila.compras
                                                ];

                                            }

                                    }

                                }

                            },


                            scales: {

                                y: {

                                    beginAtZero:
                                        true,

                                    ticks: {

                                        callback:
                                            function (
                                                value
                                            ) {

                                                return (
                                                    'S/ '
                                                    +
                                                    Number(
                                                        value
                                                    ).toLocaleString(
                                                        'es-PE'
                                                    )
                                                );

                                            }

                                    }

                                }

                            }

                        }

                    }
                );

    }


    // ====================================================
    // PINTAR GRAFICO PERIODO
    // ====================================================
    function cargarGraficoPeriodo() {

            const periodo =
                $('#filtroPeriodoGrafico')
                    .val();


            const productoId =
                Number(
                    $('#filtroProductoPeriodo')
                        .val()
                    || 0
                );


            const clienteId =
                Number(
                    $('#filtroClientePeriodo')
                        .val()
                    || 0
                );


            const metrica =
                $('#filtroMetricaPeriodo')
                    .val();


            const desde =
                $('#fechaDesdePeriodo')
                    .val();


            const hasta =
                $('#fechaHastaPeriodo')
                    .val();


            /*
            * Si es personalizado esperamos a que existan
            * ambas fechas.
            */

            if (
                periodo === 'PERSONALIZADO'
                &&
                (
                    !desde
                    ||
                    !hasta
                )
            ) {

                return;
            }


            $.ajax({

                url:
                    '<?= BASE_URL ?>ajax/reportes/grafico_periodo.php',

                type:
                    'GET',

                dataType:
                    'json',

                data: {

                    periodo:
                        periodo,

                    producto_id:
                        productoId,

                    cliente_id:
                        clienteId,

                    metrica:
                        metrica,

                    desde:
                        desde,

                    hasta:
                        hasta

                },


                success:
                    function (
                        response
                    ) {

                        if (
                            !response.success
                        ) {

                            Swal.fire({

                                icon:
                                    'error',

                                title:
                                    'Error',

                                text:
                                    response.message

                            });

                            return;
                        }


                        actualizarTituloGraficoPeriodo(
                            response
                        );


                        pintarGraficoPeriodo(
                            response
                        );

                    },


                error:
                    function (
                        xhr
                    ) {

                        console.error(
                            xhr.responseText
                        );


                        Swal.fire({

                            icon:
                                'error',

                            title:
                                'Error',

                            text:
                                'No se pudo cargar el análisis.'

                        });

                    }

            });

    }


    function actualizarTituloGraficoPeriodo( response ) {

            let titulo =
                response.metrica_nombre;


            /*
            * Nombre del producto seleccionado.
            */

            const productoTexto =
                $('#filtroProductoPeriodo')
                    .find(
                        ':selected'
                    )
                    .text()
                    .trim();


            /*
            * Nombre cliente.
            */

            const clienteTexto =
                $('#filtroClientePeriodo')
                    .find(
                        ':selected'
                    )
                    .text()
                    .trim();


            if (
                Number(
                    response.producto_id
                ) > 0
                &&
                productoTexto
            ) {

                titulo +=
                    ' - '
                    +
                    productoTexto;
            }


            if (
                Number(
                    response.cliente_id
                ) > 0
                &&
                clienteTexto
            ) {

                titulo +=
                    ' - '
                    +
                    clienteTexto;
            }


            $('#tituloGraficoPeriodo')
                .text(
                    titulo
                );


            let descripcion =
                response.periodo_nombre;


            descripcion +=
                ' · '
                +
                response.desde
                +
                ' al '
                +
                response.hasta;


            $('#descripcionGraficoPeriodo')
                .text(
                    descripcion
                );

    }
        

    function pintarGraficoPeriodo( response ) {

            const canvas =
                document.getElementById(
                    'graficoPeriodo'
                );


            if (
                graficoPeriodo
            ) {

                graficoPeriodo.destroy();

                graficoPeriodo = null;
            }


            const labels =
                response.data.map(
                    fila =>
                        fila.label
                );


            const valores =
                response.data.map(
                    fila =>
                        Number(
                            fila.valor
                        )
                );


            graficoPeriodo =
                new Chart(
                    canvas,
                    {

                        type:
                            'line',


                        data: {

                            labels:
                                labels,


                            datasets: [

                                {

                                    label:
                                        response.metrica_nombre,

                                    data:
                                        valores,

                                    tension:
                                        0.3,

                                    borderWidth:
                                        3,

                                    pointRadius:
                                        4,

                                    pointHoverRadius:
                                        6

                                }

                            ]

                        },


                        options: {

                            responsive:
                                true,

                            maintainAspectRatio:
                                false,


                            interaction: {

                                mode:
                                    'index',

                                intersect:
                                    false

                            },


                            plugins: {

                                legend: {

                                    position:
                                        'bottom'

                                },


                                tooltip: {

                                    callbacks: {

                                        label:
                                            function (
                                                context
                                            ) {

                                                if (
                                                    response.formato
                                                    ===
                                                    'MONEDA'
                                                ) {

                                                    return (
                                                        response.metrica_nombre
                                                        +
                                                        ': '
                                                        +
                                                        moneda(
                                                            context.raw
                                                        )
                                                    );

                                                }


                                                return (
                                                    response.metrica_nombre
                                                    +
                                                    ': '
                                                    +
                                                    Number(
                                                        context.raw
                                                    )
                                                    .toLocaleString(
                                                        'es-PE'
                                                    )
                                                );

                                            }

                                    }

                                }

                            },


                            scales: {

                                y: {

                                    beginAtZero:
                                        true,

                                    ticks: {

                                        callback:
                                            function (
                                                value
                                            ) {

                                                if (
                                                    response.formato
                                                    ===
                                                    'MONEDA'
                                                ) {

                                                    return (
                                                        'S/ '
                                                        +
                                                        Number(
                                                            value
                                                        )
                                                        .toLocaleString(
                                                            'es-PE'
                                                        )
                                                    );

                                                }


                                                return Number(
                                                    value
                                                )
                                                .toLocaleString(
                                                    'es-PE'
                                                );

                                            }

                                    }

                                }

                            }

                        }

                    }
                );

    }

    $('#filtroProductoPeriodo') .select2({
        theme:
            'bootstrap-5',

        width:
            '100%',

        placeholder:
            'Todos los productos',

        allowClear:
            true,

        ajax: {
            url:
                '<?= BASE_URL ?>ajax/productos/buscar.php',

            dataType:
                'json',

            delay:
                250,

            data:
                function (params) {
                    return {
                        q:
                            params.term
                            || ''
                    };
                },

            processResults:
                function (data) {
                    return data;
                }

        }

    });


    // ====================================================
    // FORMATO MONEDA
    // ====================================================
    function moneda(valor) {
        return new Intl.NumberFormat(
            'es-PE',
            {
                style:
                    'currency',

                currency:
                    'PEN',

                minimumFractionDigits:
                    2
            }
        ).format(
            Number(valor || 0)
        );
    }


    // ====================================================
    // CARGAR GRÁFICO
    // ====================================================
    function cargarGraficoMensual() {
                    const anio =
                        $('#filtroAnio').val();


                    $.ajax({

                        url:
                            '<?= BASE_URL ?>ajax/reportes/graficos_mensuales.php',

                        type:
                            'GET',

                        dataType:
                            'json',

                        data: {
                            anio: anio
                        },


                        success:
                            function (response) {

                                if (
                                    !response.success
                                ) {

                                    Swal.fire({
                                        icon:
                                            'error',

                                        title:
                                            'Error',

                                        text:
                                            response.message
                                    });

                                    return;
                                }


                                pintarResumen(
                                    response.resumen
                                );


                                pintarGrafico(
                                    response.data
                                );

                            },


                        error:
                            function (xhr) {

                                console.error(
                                    xhr.responseText
                                );


                                Swal.fire({
                                    icon:
                                        'error',

                                    title:
                                        'Error',

                                    text:
                                        'No se pudo cargar la información de los gráficos.'
                                });

                            }

                    });

    }


    // ====================================================
    // RESUMEN
    // ====================================================
    function pintarResumen(resumen) {

                    $('#totalVentasAnio')
                        .text(
                            moneda(
                                resumen.ventas
                            )
                        );


                    $('#totalUtilidadAnio')
                        .text(
                            moneda(
                                resumen.utilidad_bruta
                            )
                        );


                    $('#totalGananciaAnio')
                        .text(
                            moneda(
                                resumen.ganancia
                            )
                        );


                    /*
                    * Si la ganancia es negativa,
                    * la mostramos en rojo.
                    */

                    const ganancia =
                        Number(
                            resumen.ganancia
                            || 0
                        );


                    $('#totalGananciaAnio')
                        .removeClass(
                            'text-success text-danger'
                        )
                        .addClass(
                            ganancia >= 0
                                ? 'text-success'
                                : 'text-danger'
                        );

    }


    // ====================================================
    // PINTAR CHART
    // ====================================================
    function pintarGrafico(data) {

                    const contexto =
                        document
                            .getElementById(
                                'graficoMensual'
                            )
                            .getContext('2d');


                    /*
                    * Si ya existe un gráfico,
                    * lo destruimos antes de generar
                    * el nuevo.
                    */

                    if (
                        graficoMensual
                    ) {

                        graficoMensual.destroy();
                    }


                    const labels =
                        data.map(
                            fila =>
                                fila.mes_nombre
                        );


                    const ventas =
                        data.map(
                            fila =>
                                Number(
                                    fila.ventas
                                )
                        );


                    const utilidad =
                        data.map(
                            fila =>
                                Number(
                                    fila.utilidad_bruta
                                )
                        );


                    const ganancia =
                        data.map(
                            fila =>
                                Number(
                                    fila.ganancia
                                )
                        );


                    graficoMensual =
                        new Chart(
                            contexto,
                            {

                                type:
                                    'line',


                                data: {

                                    labels:
                                        labels,


                                    datasets: [

                                        {
                                            label:
                                                'Ventas',

                                            data:
                                                ventas,

                                            tension:
                                                0.3,

                                            borderWidth:
                                                3,

                                            pointRadius:
                                                4,

                                            pointHoverRadius:
                                                6
                                        },


                                        {
                                            label:
                                                'Utilidad bruta',

                                            data:
                                                utilidad,

                                            tension:
                                                0.3,

                                            borderWidth:
                                                3,

                                            pointRadius:
                                                4,

                                            pointHoverRadius:
                                                6
                                        },


                                        {
                                            label:
                                                'Ganancia después de gastos',

                                            data:
                                                ganancia,

                                            tension:
                                                0.3,

                                            borderWidth:
                                                3,

                                            pointRadius:
                                                4,

                                            pointHoverRadius:
                                                6
                                        }

                                    ]

                                },


                                options: {

                                    responsive:
                                        true,

                                    maintainAspectRatio:
                                        false,


                                    interaction: {

                                        mode:
                                            'index',

                                        intersect:
                                            false

                                    },


                                    plugins: {

                                        legend: {

                                            position:
                                                'bottom'

                                        },


                                        tooltip: {

                                            callbacks: {

                                                label:
                                                    function (
                                                        context
                                                    ) {

                                                        return (
                                                            context
                                                                .dataset
                                                                .label
                                                            +
                                                            ': '
                                                            +
                                                            moneda(
                                                                context.raw
                                                            )
                                                        );
                                                    }

                                            }

                                        }

                                    },


                                    scales: {

                                        y: {

                                            beginAtZero:
                                                true,

                                            ticks: {

                                                callback:
                                                    function (
                                                        value
                                                    ) {

                                                        return (
                                                            'S/ '
                                                            +
                                                            Number(
                                                                value
                                                            ).toLocaleString(
                                                                'es-PE'
                                                            )
                                                        );
                                                    }

                                            }

                                        }

                                    }

                                }

                            }
                        );

    }


    // ====================================================
    // CAMBIO DE AÑO
    // ====================================================
    $('#filtroAnio').on(
                    'change',
                    function () {

                        cargarGraficos();

                    }
    );


    // ============================================================
    // DISTRIBUCIÓN DE GASTOS
    // ============================================================
    function cargarGraficoGastos() {

                    const mes =
                        Number(
                            $('#filtroMesDetalle').val()
                        );


                    $.ajax({

                        url:
                            '<?= BASE_URL ?>ajax/reportes/gastos_categoria.php',

                        type:
                            'GET',

                        dataType:
                            'json',

                        data: {
                            mes: mes
                        },

                        success:
                            function (response) {

                                if (!response.success) {

                                    console.error(
                                        response.message
                                    );

                                    return;
                                }


                                $('#totalGastosGrafico')
                                    .text(
                                        moneda(
                                            response.total
                                        )
                                    );


                                pintarGraficoGastos(
                                    response.data
                                );

                            },

                        error:
                            function (xhr) {

                                console.error(
                                    xhr.responseText
                                );

                            }

                    });

    }

    // ============================================================
    // PINTAR GRÁFICO DE GASTOS
    // ============================================================
    function pintarGraficoGastos(data) {

                    const canvas =
                        document.getElementById(
                            'graficoGastos'
                        );


                    const sinDatos =
                        $('#sinGastos');


                    if (
                        graficoGastos
                    ) {

                        graficoGastos.destroy();

                        graficoGastos = null;
                    }


                    if (
                        !data
                        ||
                        data.length === 0
                    ) {

                        $(canvas)
                            .addClass(
                                'd-none'
                            );


                        sinDatos
                            .removeClass(
                                'd-none'
                            );


                        return;
                    }


                    $(canvas)
                        .removeClass(
                            'd-none'
                        );


                    sinDatos
                        .addClass(
                            'd-none'
                        );


                    const labels =
                        data.map(
                            fila =>
                                fila.nombre
                        );


                    const valores =
                        data.map(
                            fila =>
                                Number(
                                    fila.total
                                )
                        );


                    graficoGastos =
                        new Chart(
                            canvas,
                            {

                                type:
                                    'doughnut',


                                data: {

                                    labels:
                                        labels,


                                    datasets: [

                                        {

                                            data:
                                                valores,

                                            borderWidth:
                                                2

                                        }

                                    ]

                                },


                                options: {

                                    responsive:
                                        true,

                                    maintainAspectRatio:
                                        false,


                                    cutout:
                                        '60%',


                                    plugins: {

                                        legend: {

                                            position:
                                                'bottom'

                                        },


                                        tooltip: {

                                            callbacks: {

                                                label:
                                                    function (
                                                        context
                                                    ) {

                                                        const valor =
                                                            Number(
                                                                context.raw
                                                            );


                                                        const total =
                                                            context
                                                                .dataset
                                                                .data
                                                                .reduce(
                                                                    (
                                                                        suma,
                                                                        item
                                                                    ) =>
                                                                        suma
                                                                        +
                                                                        Number(
                                                                            item
                                                                        ),
                                                                    0
                                                                );


                                                        const porcentaje =
                                                            total > 0

                                                                ? (
                                                                    valor
                                                                    /
                                                                    total
                                                                    *
                                                                    100
                                                                )

                                                                : 0;


                                                        return (
                                                            context.label
                                                            +
                                                            ': '
                                                            +
                                                            moneda(
                                                                valor
                                                            )
                                                            +
                                                            ' ('
                                                            +
                                                            porcentaje.toFixed(
                                                                1
                                                            )
                                                            +
                                                            '%)'
                                                        );
                                                    }

                                            }

                                        }

                                    }

                                }

                            }
                        );
    }

                

    // ============================================================
    // TOP PRODUCTOS
    // ============================================================
    function cargarGraficoProductos() {

                    const mes =
                        Number(
                            $('#filtroMesDetalle').val()
                        );


                    $.ajax({

                        url:
                            '<?= BASE_URL ?>ajax/reportes/top_productos.php',

                        type:
                            'GET',

                        dataType:
                            'json',

                        data: {
                            mes: mes
                        },

                        success:
                            function (response) {

                                if (!response.success) {

                                    console.error(
                                        response.message
                                    );

                                    return;
                                }


                                pintarGraficoProductos(
                                    response.data
                                );

                            },

                        error:
                            function (xhr) {

                                console.error(
                                    xhr.responseText
                                );

                            }

                    });

    }


    // ============================================================
    // PINTAR GRAFICO PRODUCTOS
    // ============================================================
    function pintarGraficoProductos(data) {

                    const canvas =
                        document.getElementById(
                            'graficoProductos'
                        );


                    const sinDatos =
                        $('#sinProductos');


                    if (
                        graficoProductos
                    ) {

                        graficoProductos.destroy();

                        graficoProductos = null;
                    }


                    if (
                        !data
                        ||
                        data.length === 0
                    ) {

                        $(canvas)
                            .addClass(
                                'd-none'
                            );


                        sinDatos
                            .removeClass(
                                'd-none'
                            );


                        return;
                    }


                    $(canvas)
                        .removeClass(
                            'd-none'
                        );


                    sinDatos
                        .addClass(
                            'd-none'
                        );


                    const labels =
                        data.map(
                            fila =>
                                fila.producto
                        );


                    const cantidades =
                        data.map(
                            fila =>
                                Number(
                                    fila.cantidad
                                )
                        );


                    graficoProductos =
                        new Chart(
                            canvas,
                            {

                                type:
                                    'bar',


                                data: {

                                    labels:
                                        labels,


                                    datasets: [

                                        {

                                            label:
                                                'Unidades vendidas',

                                            data:
                                                cantidades,

                                            borderWidth:
                                                1

                                        }

                                    ]

                                },


                                options: {

                                    indexAxis:
                                        'y',


                                    responsive:
                                        true,

                                    maintainAspectRatio:
                                        false,


                                    plugins: {

                                        legend: {

                                            display:
                                                false

                                        },


                                        tooltip: {

                                            callbacks: {

                                                label:
                                                    function (
                                                        context
                                                    ) {

                                                        return (
                                                            Number(
                                                                context.raw
                                                            )
                                                            .toLocaleString(
                                                                'es-PE'
                                                            )
                                                            +
                                                            ' unidades'
                                                        );
                                                    }

                                            }

                                        }

                                    },


                                    scales: {

                                        x: {

                                            beginAtZero:
                                                true,

                                            ticks: {

                                                precision:
                                                    0

                                            }

                                        }

                                    }

                                }

                            }
                        );
    }


    // ====================================================
    // INICIAL
    // ====================================================
    function cargarGraficos() {

                    cargarGraficoMensual();

                    cargarGraficoGastos();

                    cargarGraficoProductos();

                    cargarGraficoClientes();

                    cargarGraficoPeriodo();
    }

    cargarGraficos();

        $('#filtroClientePeriodo').select2({

            theme:
                'bootstrap-5',
            width:
                '100%',
            placeholder:
                'Todos los clientes',
            allowClear:
                true,
            minimumInputLength:
                0,
            ajax: {
                url: '<?= BASE_URL ?>ajax/clientes/buscar.php',
                dataType: 'json',
                delay: 250,
                data: function (params) {
                    return {
                         q: params.term || ''
                    };
                },
                processResults: function (data) {
                    return data;
                }
            }
        });        
    
        $('#filtroProductoPeriodo').on(
            'change',
            function () {

                const productoId =
                    Number(
                        $(this).val()
                        || 0
                    );


                /*
                * Cuando seleccionamos un producto,
                * por defecto queremos saber
                * cuántas unidades vendió.
                */

                if (
                    productoId > 0
                ) {

                    $('#filtroMetricaPeriodo')
                        .val(
                            'UNIDADES'
                        );

                }


                cargarGraficoPeriodo();

            }
        );


        $('#filtroPeriodoGrafico') .on( 'change', function () {

                const personalizado =
                    $(this).val()
                    ===
                    'PERSONALIZADO';


                $('#contenedorFechasPeriodo')
                    .toggleClass(
                        'd-none',
                        !personalizado
                    );


                if (
                    !personalizado
                ) {

                    cargarGraficoPeriodo();

                }

            }
        );


        $('#filtroMetricaPeriodo').on(
            'change',
            function () {

                cargarGraficoPeriodo();

            }
        );


        $('#filtroClientePeriodo').on(
            'change',
            function () {

                cargarGraficoPeriodo();

            }
        );


        $( '#fechaDesdePeriodo, #fechaHastaPeriodo' ).on( 'change',
            function () {

                const desde =
                    $('#fechaDesdePeriodo')
                        .val();


                const hasta =
                    $('#fechaHastaPeriodo')
                        .val();


                if (
                    desde
                    &&
                    hasta
                ) {

                    cargarGraficoPeriodo();

                }

            }
        );

    });
            

</script>


<?php

require_once __DIR__ . '/../../includes/footer.php';

?>