<?php

$pageTitle = 'Compras';

require_once __DIR__ . '/../../includes/header.php';
require_once __DIR__ . '/../../includes/navbar.php';

?>

<main class="container-fluid py-4">

    <div class="d-flex flex-column flex-md-row justify-content-between align-items-md-center gap-3 mb-4">

        <div>

            <h1 class="h3 fw-bold mb-1">
                Compras
            </h1>

            <p class="text-muted mb-0">
                Historial de abastecimiento.
            </p>

        </div>


        <a
            href="<?= BASE_URL ?>modules/compras/nueva.php"
            class="btn btn-dark"
        >
            <i class="fa-solid fa-plus me-2"></i>
            Nueva compra
        </a>

    </div>


    <div class="card border-0 shadow-sm">

        <div class="card-body">

            <div class="table-responsive">

                <table
                    id="tablaCompras"
                    class="table table-hover align-middle w-100"
                >

                    <thead>

                    <tr>
                        <th>ID</th>
                        <th>Fecha</th>
                        <th>Proveedor</th>
                        <th>Productos</th>
                        <th>Total</th>
                        <th>Estado</th>
                        <th>Acciones</th>
                    </tr>

                    </thead>

                </table>

            </div>

        </div>

    </div>

</main>


<!-- DETALLE COMPRA -->

<div
    class="modal fade"
    id="modalDetalleCompra"
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
                        id="tituloDetalleCompra"
                    >
                        Compra
                    </h5>

                    <small
                        class="text-muted"
                        id="fechaDetalleCompra"
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

                    <div class="small text-muted">
                        Proveedor
                    </div>

                    <div
                        class="fw-semibold"
                        id="detalleProveedorCompra"
                    ></div>

                </div>


                <h6 class="fw-bold mb-3">
                    Productos
                </h6>


                <div id="detalleProductosCompra"></div>


                <div class="card bg-light border-0 mt-4">

                    <div class="card-body">

                        <div class="d-flex justify-content-between mb-2">

                            <span>
                                Subtotal
                            </span>

                            <span id="detalleCompraSubtotal">
                                S/ 0.00
                            </span>

                        </div>


                        <div class="d-flex justify-content-between mb-2">

                            <span>
                                Descuento
                            </span>

                            <span
                                id="detalleCompraDescuento"
                                class="text-success"
                            >
                                - S/ 0.00
                            </span>

                        </div>


                        <hr>


                        <div class="d-flex justify-content-between">

                            <strong>
                                Total
                            </strong>

                            <strong
                                class="fs-5"
                                id="detalleCompraTotal"
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

    const modalDetalle =
        new bootstrap.Modal(
            document.getElementById(
                'modalDetalleCompra'
            )
        );


    const tabla =
        $('#tablaCompras').DataTable({

            processing: true,
            serverSide: true,

            ajax: {

                url:
                    '<?= BASE_URL ?>ajax/compras/listar.php',

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
                    data: 'proveedor'
                },

                {
                    data: 'productos'
                },

                {
                    data: 'total'
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


    $('#tablaCompras').on(
        'click',
        '.btn-ver-compra',
        function () {

            const id =
                Number(
                    $(this).data('id')
                );


            $.ajax({

                url:
                    '<?= BASE_URL ?>ajax/compras/obtener.php',

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


                    pintarCompra(
                        response.data
                    );


                    modalDetalle.show();

                },

                error: function (xhr) {

                    console.error(
                        xhr.responseText
                    );

                }

            });

        }
    );


    function pintarCompra(data) {

        const compra =
            data.compra;


        $('#tituloDetalleCompra')
            .text(
                'Compra #'
                +
                compra.id
            );


        $('#fechaDetalleCompra')
            .text(
                compra.fecha_formateada
            );


        $('#detalleProveedorCompra')
            .text(
                compra.proveedor
            );


        $('#detalleCompraSubtotal')
            .text(
                'S/ '
                +
                Number(
                    compra.subtotal
                ).toFixed(2)
            );


        $('#detalleCompraDescuento')
            .text(
                '- S/ '
                +
                Number(
                    compra.descuento
                ).toFixed(2)
            );


        $('#detalleCompraTotal')
            .text(
                'S/ '
                +
                Number(
                    compra.total
                ).toFixed(2)
            );


        const $productos =
            $('#detalleProductosCompra');


        $productos.empty();


        data.productos.forEach(
            function (producto) {

                $productos.append(`

                    <div class="border rounded p-3 mb-2">

                        <div class="d-flex justify-content-between">

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
                                    ×
                                    S/
                                    ${Number(producto.costo_unitario).toFixed(2)}
                                </div>

                                <strong>
                                    S/
                                    ${Number(producto.subtotal).toFixed(2)}
                                </strong>

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