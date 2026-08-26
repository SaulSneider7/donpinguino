<?php

$pageTitle = 'Nueva compra';

require_once __DIR__ . '/../../includes/header.php';
require_once __DIR__ . '/../../includes/navbar.php';

?>

<main class="container-fluid py-3 py-md-4">

    <div class="row justify-content-center">

        <div class="col-12 col-xl-10">

            <div class="d-flex justify-content-between align-items-center mb-3">

                <div>

                    <h1 class="h3 fw-bold mb-0">
                        Nueva compra
                    </h1>

                    <small class="text-muted">
                        Entrada de mercadería
                    </small>

                </div>


                <a
                    href="<?= BASE_URL ?>modules/compras/index.php"
                    class="btn btn-outline-secondary"
                >
                    <i class="fa-solid fa-list me-1"></i>
                    Compras
                </a>

            </div>


            <div class="row g-3">

                <!-- IZQUIERDA -->
                <div class="col-12 col-lg-8">

                    <!-- PROVEEDOR -->
                    <div class="card border-0 shadow-sm mb-3">

                        <div class="card-body">

                            <div class="d-flex justify-content-between align-items-end gap-2">

                                <div class="flex-grow-1">

                                    <label class="form-label fw-semibold">
                                        Proveedor
                                    </label>

                                    <select
                                        id="proveedor_id"
                                        class="form-select"
                                    >
                                        <option value="">
                                            Sin proveedor
                                        </option>
                                    </select>

                                </div>


                                <button
                                    type="button"
                                    class="btn btn-outline-dark"
                                    id="btnNuevoProveedor"
                                    title="Nuevo proveedor"
                                >
                                    <i class="fa-solid fa-plus"></i>
                                </button>

                            </div>

                        </div>

                    </div>


                    <!-- PRODUCTOS -->
                    <div class="card border-0 shadow-sm">

                        <div class="card-body">

                            <label class="form-label fw-semibold">
                                Agregar producto
                            </label>

                            <select
                                id="buscarProductoCompra"
                                class="form-select"
                            ></select>

                        </div>


                        <div
                            class="list-group list-group-flush"
                            id="listaProductosCompra"
                        >

                            <div class="text-center text-muted py-5">

                                <i class="fa-solid fa-boxes-stacked fa-2x mb-2"></i>

                                <div>
                                    Agrega los productos comprados.
                                </div>

                            </div>

                        </div>

                    </div>

                </div>


                <!-- DERECHA -->
                <div class="col-12 col-lg-4">

                    <div class="card border-0 shadow-sm">

                        <div class="card-body">

                            <h5 class="fw-bold mb-3">
                                Resumen
                            </h5>


                            <div class="d-flex justify-content-between mb-2">

                                <span class="text-muted">
                                    Subtotal
                                </span>

                                <span id="compraSubtotal">
                                    S/ 0.00
                                </span>

                            </div>


                            <div class="mb-3">

                                <label class="form-label">
                                    Descuento
                                </label>

                                <div class="input-group">

                                    <span class="input-group-text">
                                        S/
                                    </span>

                                    <input
                                        type="number"
                                        class="form-control"
                                        id="compraDescuento"
                                        min="0"
                                        step="0.01"
                                        value="0"
                                    >

                                </div>

                            </div>


                            <hr>


                            <div class="d-flex justify-content-between align-items-center mb-4">

                                <span class="fw-semibold">
                                    TOTAL
                                </span>

                                <span
                                    class="fs-3 fw-bold"
                                    id="compraTotal"
                                >
                                    S/ 0.00
                                </span>

                            </div>


                            <div class="mb-3">

                                <label class="form-label">
                                    Observación
                                </label>

                                <textarea
                                    class="form-control"
                                    id="compraObservacion"
                                    rows="2"
                                ></textarea>

                            </div>


                            <button
                                type="button"
                                class="btn btn-dark btn-lg w-100"
                                id="btnGuardarCompra"
                                disabled
                            >

                                <i class="fa-solid fa-check me-2"></i>
                                REGISTRAR COMPRA

                            </button>

                        </div>

                    </div>

                </div>

            </div>

        </div>

    </div>

</main>


<!-- =========================================================
     NUEVO PROVEEDOR
========================================================= -->

<div
    class="modal fade"
    id="modalNuevoProveedor"
    tabindex="-1"
    aria-hidden="true"
>

    <div class="modal-dialog modal-dialog-centered">

        <div class="modal-content">

            <form id="formNuevoProveedor">

                <div class="modal-header">

                    <h5 class="modal-title fw-bold">
                        Nuevo proveedor
                    </h5>

                    <button
                        type="button"
                        class="btn-close"
                        data-bs-dismiss="modal"
                    ></button>

                </div>


                <div class="modal-body">

                    <div class="mb-3">

                        <label class="form-label">
                            Nombre
                        </label>

                        <input
                            type="text"
                            class="form-control"
                            name="nombre"
                            id="proveedor_nombre"
                            required
                        >

                    </div>


                    <div class="mb-3">

                        <label class="form-label">
                            RUC
                        </label>

                        <input
                            type="text"
                            class="form-control"
                            name="ruc"
                        >

                    </div>


                    <div>

                        <label class="form-label">
                            Teléfono
                        </label>

                        <input
                            type="text"
                            class="form-control"
                            name="telefono"
                        >

                    </div>

                </div>


                <div class="modal-footer">

                    <button
                        type="button"
                        class="btn btn-light"
                        data-bs-dismiss="modal"
                    >
                        Cancelar
                    </button>

                    <button
                        type="submit"
                        class="btn btn-dark"
                        id="btnGuardarProveedor"
                    >
                        Guardar
                    </button>

                </div>

            </form>

        </div>

    </div>

</div>


<script>
document.addEventListener('DOMContentLoaded', function () {

    let carritoCompra = [];


    const modalProveedor =
        new bootstrap.Modal(
            document.getElementById('modalNuevoProveedor')
        );


    // ========================================================
    // PROVEEDOR SELECT2
    // ========================================================

    $('#proveedor_id').select2({

        width: '100%',

        allowClear: true,

        placeholder:
            'Buscar proveedor...',

        ajax: {

            url:
                '<?= BASE_URL ?>ajax/proveedores/buscar.php',

            dataType:
                'json',

            delay:
                250,

            data: function (params) {

                return {
                    q:
                        params.term || '',

                    page:
                        params.page || 1
                };
            },

            processResults: function (data) {
                return data;
            }
        }
    });


    // ========================================================
    // NUEVO PROVEEDOR
    // ========================================================

    $('#btnNuevoProveedor').on(
        'click',
        function () {

            $('#formNuevoProveedor')[0]
                .reset();

            modalProveedor.show();

            setTimeout(function () {

                $('#proveedor_nombre')
                    .trigger('focus');

            }, 200);
        }
    );


    $('#formNuevoProveedor').on(
        'submit',
        function (e) {

            e.preventDefault();


            const $btn =
                $('#btnGuardarProveedor');


            $btn
                .prop('disabled', true)
                .text('Guardando...');


            $.ajax({

                url:
                    '<?= BASE_URL ?>ajax/proveedores/guardar_rapido.php',

                type:
                    'POST',

                dataType:
                    'json',

                data:
                    $(this).serialize(),

                success: function (response) {

                    if (!response.success) {

                        Swal.fire({
                            icon: 'error',
                            title: 'Error',
                            text: response.message
                        });

                        return;
                    }


                    const option =
                        new Option(
                            response.nombre,
                            response.id,
                            true,
                            true
                        );


                    $('#proveedor_id')
                        .append(option)
                        .trigger('change');


                    modalProveedor.hide();


                    Swal.fire({
                        icon: 'success',
                        title: 'Proveedor creado',
                        timer: 1000,
                        showConfirmButton: false
                    });

                },

                error: function (xhr) {

                    console.error(
                        xhr.responseText
                    );

                },

                complete: function () {

                    $btn
                        .prop('disabled', false)
                        .text('Guardar');
                }

            });

        }
    );


    // ========================================================
    // PRODUCTOS
    // ========================================================

    $('#buscarProductoCompra').select2({

        width: '100%',

        placeholder:
            'Buscar producto...',

        ajax: {

            url:
                '<?= BASE_URL ?>ajax/productos/buscar_compra.php',

            dataType:
                'json',

            delay:
                200,

            data: function (params) {

                return {
                    q:
                        params.term || '',

                    page:
                        params.page || 1
                };
            },

            processResults: function (data) {
                return data;
            }
        }
    });


    $('#buscarProductoCompra').on(
        'select2:select',
        function (e) {

            agregarProducto(
                e.params.data
            );


            $(this)
                .val(null)
                .trigger('change');

        }
    );


    function agregarProducto(producto) {

        const existente =
            carritoCompra.find(
                item =>
                    item.producto_id
                    ===
                    Number(producto.id)
            );


        if (existente) {

            existente.cantidad += 1;

        } else {

            carritoCompra.push({

                producto_id:
                    Number(producto.id),

                nombre:
                    producto.nombre,

                presentacion:
                    producto.presentacion,

                cantidad:
                    1,

                costo_unitario:
                    Number(
                        producto.costo_referencia
                    )
            });
        }


        renderCompra();
    }


    // ========================================================
    // RENDER
    // ========================================================

    function renderCompra() {

        const $lista =
            $('#listaProductosCompra');


        $lista.empty();


        if (carritoCompra.length === 0) {

            $lista.html(`

                <div class="text-center text-muted py-5">

                    <i class="fa-solid fa-boxes-stacked fa-2x mb-2"></i>

                    <div>
                        Agrega los productos comprados.
                    </div>

                </div>

            `);


            $('#btnGuardarCompra')
                .prop('disabled', true);


            recalcularCompra();

            return;
        }


        carritoCompra.forEach(
            function (item) {

                $lista.append(`

                    <div
                        class="list-group-item py-3"
                        data-id="${item.producto_id}"
                    >

                        <div class="row g-2 align-items-end">

                            <div class="col-12 col-md">

                                <div class="fw-semibold">
                                    ${escapeHtml(item.nombre)}
                                </div>

                                ${
                                    item.presentacion
                                    ?
                                    `
                                    <small class="text-muted">
                                        ${escapeHtml(item.presentacion)}
                                    </small>
                                    `
                                    :
                                    ''
                                }

                            </div>


                            <div class="col-5 col-md-2">

                                <label class="form-label small">
                                    Cantidad
                                </label>

                                <input
                                    type="number"
                                    class="form-control cantidad-compra"
                                    data-id="${item.producto_id}"
                                    value="${item.cantidad}"
                                    min="0.001"
                                    step="0.001"
                                >

                            </div>


                            <div class="col-5 col-md-3">

                                <label class="form-label small">
                                    Costo unitario
                                </label>

                                <div class="input-group">

                                    <span class="input-group-text">
                                        S/
                                    </span>

                                    <input
                                        type="number"
                                        class="form-control costo-compra"
                                        data-id="${item.producto_id}"
                                        value="${item.costo_unitario.toFixed(2)}"
                                        min="0"
                                        step="0.01"
                                    >

                                </div>

                            </div>


                            <div class="col-2 col-md-auto">

                                <button
                                    type="button"
                                    class="btn btn-outline-danger btn-eliminar-compra"
                                    data-id="${item.producto_id}"
                                >
                                    <i class="fa-solid fa-trash"></i>
                                </button>

                            </div>

                        </div>


                        <div class="text-end mt-2">

                            <small class="text-muted">
                                Subtotal:
                            </small>

                            <strong
                                id="subtotalCompra${item.producto_id}"
                            >
                                S/ 0.00
                            </strong>

                        </div>

                    </div>

                `);

            }
        );


        $('#btnGuardarCompra')
            .prop('disabled', false);


        recalcularCompra();
    }


    // ========================================================
    // CANTIDAD
    // ========================================================

    $('#listaProductosCompra').on(
        'input',
        '.cantidad-compra',
        function () {

            const id =
                Number(
                    $(this).data('id')
                );


            const item =
                carritoCompra.find(
                    p =>
                        p.producto_id === id
                );


            if (!item) {
                return;
            }


            item.cantidad =
                Math.max(
                    0,
                    Number(
                        $(this).val()
                        || 0
                    )
                );


            recalcularCompra();
        }
    );


    // ========================================================
    // COSTO
    // ========================================================

    $('#listaProductosCompra').on(
        'input',
        '.costo-compra',
        function () {

            const id =
                Number(
                    $(this).data('id')
                );


            const item =
                carritoCompra.find(
                    p =>
                        p.producto_id === id
                );


            if (!item) {
                return;
            }


            item.costo_unitario =
                Math.max(
                    0,
                    Number(
                        $(this).val()
                        || 0
                    )
                );


            recalcularCompra();
        }
    );


    // ========================================================
    // ELIMINAR
    // ========================================================

    $('#listaProductosCompra').on(
        'click',
        '.btn-eliminar-compra',
        function () {

            const id =
                Number(
                    $(this).data('id')
                );


            carritoCompra =
                carritoCompra.filter(
                    p =>
                        p.producto_id !== id
                );


            renderCompra();
        }
    );


    $('#compraDescuento').on(
        'input',
        recalcularCompra
    );


    // ========================================================
    // CÁLCULO
    // ========================================================

    function recalcularCompra() {

        let subtotal = 0;


        carritoCompra.forEach(
            function (item) {

                const subtotalItem =
                    item.cantidad
                    *
                    item.costo_unitario;


                subtotal +=
                    subtotalItem;


                $(
                    '#subtotalCompra'
                    +
                    item.producto_id
                )
                    .text(
                        'S/ '
                        +
                        subtotalItem.toFixed(2)
                    );
            }
        );


        const descuento =
            Math.max(
                0,
                Number(
                    $('#compraDescuento').val()
                    || 0
                )
            );


        const total =
            Math.max(
                0,
                subtotal
                - descuento
            );


        $('#compraSubtotal')
            .text(
                'S/ '
                +
                subtotal.toFixed(2)
            );


        $('#compraTotal')
            .text(
                'S/ '
                +
                total.toFixed(2)
            );
    }


    // ========================================================
    // GUARDAR
    // ========================================================

    $('#btnGuardarCompra').on(
        'click',
        function () {

            if (
                carritoCompra.length === 0
            ) {
                return;
            }


            for (
                const item
                of carritoCompra
            ) {

                if (
                    item.cantidad <= 0
                    ||
                    item.costo_unitario < 0
                ) {

                    Swal.fire({
                        icon: 'warning',
                        title: 'Datos inválidos',
                        text:
                            'Verifique cantidades y costos.'
                    });

                    return;
                }
            }


            const $btn =
                $(this);


            $btn
                .prop('disabled', true)
                .html(`

                    <span
                        class="spinner-border spinner-border-sm me-2"
                    ></span>

                    Registrando...

                `);


            $.ajax({

                url:
                    '<?= BASE_URL ?>ajax/compras/guardar.php',

                type:
                    'POST',

                dataType:
                    'json',

                data: {

                    proveedor_id:
                        $('#proveedor_id').val(),

                    descuento:
                        $('#compraDescuento').val(),

                    observacion:
                        $('#compraObservacion').val(),

                    items:
                        JSON.stringify(
                            carritoCompra.map(
                                item => ({

                                    producto_id:
                                        item.producto_id,

                                    cantidad:
                                        item.cantidad,

                                    costo_unitario:
                                        item.costo_unitario
                                })
                            )
                        )
                },

                success: function (response) {

                    if (!response.success) {

                        Swal.fire({
                            icon: 'error',
                            title: 'No se pudo registrar',
                            text: response.message
                        });

                        return;
                    }


                    Swal.fire({

                        icon: 'success',

                        title:
                            'Compra registrada',

                        html: `
                            Compra
                            <strong>
                                #${response.compra_id}
                            </strong>

                            <br>

                            Total:
                            <strong>
                                S/
                                ${Number(response.total).toFixed(2)}
                            </strong>
                        `,

                        confirmButtonText:
                            'Nueva compra',

                        allowOutsideClick:
                            false

                    }).then(function () {

                        window.location.reload();

                    });

                },

                error: function (xhr) {

                    console.error(
                        xhr.responseText
                    );


                    Swal.fire({
                        icon: 'error',
                        title: 'Error',
                        text:
                            'Ocurrió un error al registrar la compra.'
                    });

                },

                complete: function () {

                    $btn
                        .prop('disabled', false)
                        .html(`
                            <i class="fa-solid fa-check me-2"></i>
                            REGISTRAR COMPRA
                        `);
                }

            });

        }
    );


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