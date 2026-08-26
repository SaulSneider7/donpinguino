<?php

$pageTitle = 'Ventas';

require_once __DIR__ . '/../../includes/header.php';
require_once __DIR__ . '/../../includes/navbar.php';

?>

<main class="container-fluid py-4">

    <div class="d-flex flex-column flex-md-row justify-content-between align-items-md-center gap-3 mb-4">

        <div>
            <h1 class="h3 fw-bold mb-1">
                Ventas
            </h1>

            <p class="text-muted mb-0">
                Historial de ventas registradas.
            </p>
        </div>

        <a
            href="<?= BASE_URL ?>modules/ventas/nueva.php"
            class="btn btn-dark"
        >
            <i class="fa-solid fa-plus me-2"></i>
            Nueva venta
        </a>

    </div>


    <div class="card border-0 shadow-sm">

        <div class="card-body">

            <div class="table-responsive">

                <table
                    id="tablaVentas"
                    class="table table-hover align-middle w-100"
                >
                    <thead>
                    <tr>
                        <th>Registrado por</th>
                        <th>Fecha</th>
                        <th>Cliente</th>
                        <th>Total</th>
                        <th>Pagado</th>
                        <th>Pendiente</th>
                        <th>Estado</th>
                        <th>Acciones</th>
                    </tr>
                    </thead>

                </table>

            </div>

        </div>

    </div>

</main>


<!-- =========================================================
     MODAL DETALLE
========================================================= -->

<div
    class="modal fade"
    id="modalDetalleVenta"
    tabindex="-1"
    aria-hidden="true"
>

    <div
        class="modal-dialog modal-lg modal-dialog-centered modal-dialog-scrollable modal-fullscreen-md-down"
    >

        <div class="modal-content">

            <div class="modal-header">

                <div>
                    <h5
                        class="modal-title fw-bold"
                        id="tituloDetalleVenta"
                    >
                        Venta
                    </h5>

                    <small
                        class="text-muted"
                        id="fechaDetalleVenta"
                    ></small>
                </div>

                <button
                    type="button"
                    class="btn-close"
                    data-bs-dismiss="modal"
                ></button>

            </div>


            <div class="modal-body">

                <!-- CLIENTE -->
                <div class="card bg-light border-0 mb-3">

                    <div class="card-body">

                        <div class="small text-muted">
                            Cliente
                        </div>

                        <div
                            class="fw-semibold"
                            id="detalleCliente"
                        >
                        </div>

                    </div>

                </div>


                <!-- PRODUCTOS -->
                <h6 class="fw-bold mb-3">
                    Productos
                </h6>

                <div
                    id="detalleProductos"
                    class="mb-4"
                ></div>


                <!-- ENVASES -->
                <div
                    id="bloqueDetalleEnvases"
                    style="display:none;"
                >

                    <h6 class="fw-bold mb-3">
                        Envases
                    </h6>

                    <div
                        id="detalleEnvases"
                        class="mb-4"
                    ></div>

                </div>


                <!-- PAGOS -->
                <h6 class="fw-bold mb-3">
                    Pagos
                </h6>

                <div
                    id="detallePagos"
                    class="mb-4"
                ></div>


                <!-- TOTALES -->
                <div class="card border-0 bg-light">

                    <div class="card-body">

                        <div class="d-flex justify-content-between mb-2">

                            <span class="text-muted">
                                Subtotal
                            </span>

                            <span id="detalleSubtotal">
                                S/ 0.00
                            </span>

                        </div>


                        <div
                            class="d-flex justify-content-between mb-2 text-success"
                            id="filaDetallePromocion"
                        >

                            <span>
                                Promociones
                            </span>

                            <span id="detalleDescuentoPromociones">
                                - S/ 0.00
                            </span>

                        </div>


                        <hr>


                        <div class="d-flex justify-content-between mb-2">

                            <strong>
                                Total
                            </strong>

                            <strong
                                class="fs-5"
                                id="detalleTotal"
                            >
                                S/ 0.00
                            </strong>

                        </div>


                        <div class="d-flex justify-content-between">

                            <span>
                                Pendiente
                            </span>

                            <strong
                                class="text-danger"
                                id="detallePendiente"
                            >
                                S/ 0.00
                            </strong>

                        </div>

                    </div>

                </div>

            </div>


            <div class="modal-footer">

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

    const modalElement =
        document.getElementById('modalDetalleVenta');

    const modalDetalle =
        new bootstrap.Modal(modalElement);


    // ========================================================
    // DATATABLE
    // ========================================================

    const tabla =
        $('#tablaVentas').DataTable({

            processing: true,
            serverSide: true,

            ajax: {
                url:
                    '<?= BASE_URL ?>ajax/ventas/listar.php',

                type:
                    'POST'
            },

            order: [
                [0, 'desc']
            ],

            pageLength:
                10,

            columns: [

                {
                    data: 'usuario'
                },

                {
                    data: 'fecha'
                },

                {
                    data: 'cliente'
                },

                {
                    data: 'total'
                },

                {
                    data: 'pagado'
                },

                {
                    data: 'pendiente'
                },

                {
                    data: 'estado'
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
    // VER DETALLE
    // ========================================================

    $('#tablaVentas').on(
        'click',
        '.btn-ver-venta',
        function () {

            const id =
                Number(
                    $(this).data('id')
                );


            $.ajax({

                url:
                    '<?= BASE_URL ?>ajax/ventas/obtener.php',

                type:
                    'GET',

                dataType:
                    'json',

                data: {
                    id: id
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


                    pintarDetalle(
                        response.data
                    );


                    modalDetalle.show();
                },

                error: function (xhr) {

                    console.error(
                        xhr.responseText
                    );

                    Swal.fire({
                        icon: 'error',
                        title: 'Error',
                        text:
                            'No se pudo obtener la venta.'
                    });
                }

            });

        }
    );


    // ========================================================
    // PINTAR DETALLE
    // ========================================================

    function pintarDetalle(data) {

        const venta =
            data.venta;


        $('#tituloDetalleVenta')
            .text(
                'Venta #' + venta.id
            );


        $('#fechaDetalleVenta')
            .text(
                venta.fecha_formateada
            );


        $('#detalleCliente')
            .text(
                venta.cliente
            );


        $('#detalleSubtotal')
            .text(
                'S/ '
                +
                Number(
                    venta.subtotal
                ).toFixed(2)
            );


        $('#detalleDescuentoPromociones')
            .text(
                '- S/ '
                +
                Number(
                    venta.descuento_promociones
                ).toFixed(2)
            );


        $('#detalleTotal')
            .text(
                'S/ '
                +
                Number(
                    venta.total
                ).toFixed(2)
            );


        $('#detallePendiente')
            .text(
                'S/ '
                +
                Number(
                    venta.saldo_pendiente
                ).toFixed(2)
            );


        if (
            Number(
                venta.descuento_promociones
            ) > 0
        ) {

            $('#filaDetallePromocion')
                .show();

        } else {

            $('#filaDetallePromocion')
                .hide();
        }


        pintarProductos(
            data.productos
        );


        pintarPagos(
            data.pagos
        );


        pintarEnvases(
            data.envases
        );
    }


    function pintarProductos(productos) {

        const $contenedor =
            $('#detalleProductos');


        $contenedor.empty();


        productos.forEach(
            function (p) {

                let promocionHtml = '';


                if (p.promocion_nombre) {

                    promocionHtml = `

                        <div class="mt-2">

                            <span class="badge text-bg-success">

                                <i class="fa-solid fa-tag me-1"></i>

                                ${escapeHtml(p.promocion_nombre)}

                            </span>

                        </div>

                    `;
                }


                const html = `

                    <div class="border rounded p-3 mb-2">

                        <div class="d-flex justify-content-between gap-3">

                            <div>

                                <div class="fw-semibold">
                                    ${escapeHtml(p.nombre_producto)}
                                </div>

                                ${
                                    p.presentacion_producto
                                    ?
                                    `
                                    <small class="text-muted">
                                        ${escapeHtml(p.presentacion_producto)}
                                    </small>
                                    `
                                    :
                                    ''
                                }

                                ${promocionHtml}

                            </div>


                            <div class="text-end">

                                <div>
                                    ${Number(p.cantidad)}
                                    ×
                                    S/
                                    ${Number(p.precio_venta_base).toFixed(2)}
                                </div>

                                ${
                                    Number(p.descuento_promocion) > 0
                                    ?
                                    `
                                    <small class="text-success">
                                        - S/
                                        ${Number(p.descuento_promocion).toFixed(2)}
                                    </small>
                                    `
                                    :
                                    ''
                                }

                                <div class="fw-bold mt-1">

                                    S/
                                    ${Number(p.subtotal_final).toFixed(2)}

                                </div>

                            </div>

                        </div>

                    </div>

                `;


                $contenedor.append(
                    html
                );

            }
        );
    }


    function pintarPagos(pagos) {

        const $contenedor =
            $('#detallePagos');


        $contenedor.empty();


        if (pagos.length === 0) {

            $contenedor.html(`

                <div class="text-muted">
                    No se registraron pagos.
                </div>

            `);

            return;
        }


        pagos.forEach(
            function (pago) {

                $contenedor.append(`

                    <div
                        class="d-flex justify-content-between border-bottom py-2"
                    >

                        <div>

                            <div class="fw-semibold">
                                ${escapeHtml(pago.metodo_pago)}
                            </div>

                            <small class="text-muted">
                                ${escapeHtml(pago.fecha_formateada)}
                            </small>

                        </div>


                        <div class="fw-bold">
                            S/
                            ${Number(pago.monto).toFixed(2)}
                        </div>

                    </div>

                `);

            }
        );
    }


    function pintarEnvases(envases) {

        const $contenedor =
            $('#detalleEnvases');


        $contenedor.empty();


        if (
            !envases
            ||
            envases.length === 0
        ) {

            $('#bloqueDetalleEnvases')
                .hide();

            return;
        }


        $('#bloqueDetalleEnvases')
            .show();


        envases.forEach(
            function (envase) {

                $contenedor.append(`

                    <div class="border rounded p-3 mb-2">

                        <div class="fw-semibold mb-2">
                            ${escapeHtml(envase.tipo_envase)}
                        </div>

                        <div class="row g-2 small">

                            <div class="col-4">

                                <span class="text-muted">
                                    Requeridos
                                </span>

                                <div class="fw-semibold">
                                    ${Number(envase.cantidad_requerida)}
                                </div>

                            </div>


                            <div class="col-4">

                                <span class="text-muted">
                                    Entregados
                                </span>

                                <div class="fw-semibold">
                                    ${Number(envase.cantidad_entregada)}
                                </div>

                            </div>


                            <div class="col-4">

                                <span class="text-muted">
                                    Pendientes generados
                                </span>

                                <div class="fw-semibold">
                                    ${Number(envase.cantidad_pendiente)}
                                </div>

                            </div>

                            <div class="mt-3 pt-3 border-top">

                                ${
                                    Number(envase.saldo_actual_cliente) > 0

                                    ? `
                                        <div class="text-danger">

                                            <i class="fa-solid fa-circle-exclamation me-1"></i>

                                            Saldo actual del cliente:
                                            <strong>
                                                ${Number(envase.saldo_actual_cliente)}
                                            </strong>

                                        </div>
                                    `

                                    : `
                                        <div class="text-success">

                                            <i class="fa-solid fa-circle-check me-1"></i>

                                            Actualmente no tiene deuda de este envase.

                                        </div>
                                    `
                                }

                            </div>

                        </div>

                    </div>

                `);

            }
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