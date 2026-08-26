<?php

$pageTitle = 'Regalos y premios';

require_once __DIR__ . '/../../includes/header.php';
require_once __DIR__ . '/../../includes/navbar.php';

?>

<main class="container-fluid py-4">

    <div class="d-flex flex-column flex-md-row justify-content-between align-items-md-center gap-3 mb-4">

        <div>

            <h1 class="h3 fw-bold mb-1">
                Regalos y premios
            </h1>

            <p class="text-muted mb-0">
                Historial de productos entregados sin venta.
            </p>

        </div>


        <a
            href="<?= BASE_URL ?>modules/regalos/nuevo.php"
            class="btn btn-dark"
        >
            <i class="fa-solid fa-plus me-2"></i>
            Nuevo registro
        </a>

    </div>


    <div class="card border-0 shadow-sm">

        <div class="card-body">

            <div class="table-responsive">

                <table
                    id="tablaRegalos"
                    class="table table-hover align-middle w-100"
                >

                    <thead>

                    <tr>
                        <th>ID</th>
                        <th>Fecha</th>
                        <th>Tipo</th>
                        <th>Cliente</th>
                        <th>Descripción</th>
                        <th>Productos</th>
                        <th>Costo</th>
                        <th>Acciones</th>
                    </tr>

                    </thead>

                </table>

            </div>

        </div>

    </div>

</main>


<!-- DETALLE -->

<div
    class="modal fade"
    id="modalDetalleRegalo"
    tabindex="-1"
    aria-hidden="true"
>

    <div
        class="modal-dialog modal-lg modal-dialog-centered modal-dialog-scrollable"
    >

        <div class="modal-content">

            <div class="modal-header">

                <div>

                    <h5
                        class="modal-title fw-bold"
                        id="tituloDetalleRegalo"
                    >
                        Registro
                    </h5>

                    <small
                        class="text-muted"
                        id="fechaDetalleRegalo"
                    ></small>

                </div>


                <button
                    type="button"
                    class="btn-close"
                    data-bs-dismiss="modal"
                ></button>

            </div>


            <div class="modal-body">

                <div class="alert alert-light border">

                    <div class="row g-3">

                        <div class="col-12 col-md-4">

                            <div class="small text-muted">
                                Tipo
                            </div>

                            <div
                                class="fw-semibold"
                                id="detalleTipoRegalo"
                            ></div>

                        </div>


                        <div class="col-12 col-md-4">

                            <div class="small text-muted">
                                Cliente
                            </div>

                            <div
                                class="fw-semibold"
                                id="detalleClienteRegalo"
                            ></div>

                        </div>


                        <div class="col-12 col-md-4">

                            <div class="small text-muted">
                                Costo total
                            </div>

                            <div
                                class="fw-semibold"
                                id="detalleCostoRegalo"
                            ></div>

                        </div>


                        <div class="col-12">

                            <div class="small text-muted">
                                Descripción
                            </div>

                            <div
                                id="detalleDescripcionRegalo"
                            ></div>

                        </div>

                    </div>

                </div>


                <h6 class="fw-bold mb-3">
                    Productos
                </h6>


                <div id="detalleProductosRegalo"></div>

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

    const modalDetalle =
        new bootstrap.Modal(
            document.getElementById(
                'modalDetalleRegalo'
            )
        );


    const tabla =
        $('#tablaRegalos').DataTable({

            processing: true,
            serverSide: true,

            ajax: {

                url:
                    '<?= BASE_URL ?>ajax/regalos/listar.php',

                type:
                    'POST'
            },

            order: [
                [0, 'desc']
            ],

            columns: [

                {
                    data: 'id'
                },

                {
                    data: 'fecha'
                },

                {
                    data: 'tipo'
                },

                {
                    data: 'cliente'
                },

                {
                    data: 'descripcion'
                },

                {
                    data: 'productos'
                },

                {
                    data: 'costo'
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


    $('#tablaRegalos').on(
        'click',
        '.btn-ver-regalo',
        function () {

            const id =
                Number(
                    $(this).data('id')
                );


            $.ajax({

                url:
                    '<?= BASE_URL ?>ajax/regalos/obtener.php',

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

                }

            });

        }
    );


    function pintarDetalle(data) {

        const registro =
            data.registro;


        $('#tituloDetalleRegalo')
            .text(
                registro.tipo
                + ' #'
                + registro.id
            );


        $('#fechaDetalleRegalo')
            .text(
                registro.fecha_formateada
            );


        $('#detalleTipoRegalo')
            .text(
                registro.tipo
            );


        $('#detalleClienteRegalo')
            .text(
                registro.cliente
            );


        $('#detalleDescripcionRegalo')
            .text(
                registro.descripcion
            );


        $('#detalleCostoRegalo')
            .text(
                'S/ '
                +
                Number(
                    registro.costo_total
                ).toFixed(2)
            );


        const $productos =
            $('#detalleProductosRegalo');


        $productos.empty();


        data.productos.forEach(
            function (producto) {

                $productos.append(`

                    <div class="border rounded p-3 mb-2">

                        <div class="d-flex justify-content-between gap-3">

                            <div>

                                <div class="fw-semibold">
                                    ${escapeHtml(producto.nombre)}
                                </div>

                                ${
                                    producto.presentacion
                                    ?
                                    `
                                    <small class="text-muted">
                                        ${escapeHtml(producto.presentacion)}
                                    </small>
                                    `
                                    :
                                    ''
                                }

                            </div>


                            <div class="text-end">

                                <div>
                                    ${Number(producto.cantidad)}
                                    unidades
                                </div>

                                <small class="text-muted">
                                    Costo:
                                    S/
                                    ${Number(producto.costo_unitario).toFixed(2)}
                                </small>

                                <div class="fw-bold">
                                    S/
                                    ${Number(producto.costo_total).toFixed(2)}
                                </div>

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