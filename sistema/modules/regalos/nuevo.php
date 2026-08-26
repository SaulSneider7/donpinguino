<?php

$pageTitle = 'Nuevo regalo';

require_once __DIR__ . '/../../includes/header.php';
require_once __DIR__ . '/../../includes/navbar.php';

?>

<main class="container-fluid py-3 py-md-4">

    <div class="row justify-content-center">

        <div class="col-12 col-xl-10">

            <div class="d-flex justify-content-between align-items-center mb-3">

                <div>
                    <h1 class="h3 fw-bold mb-0">
                        Nuevo regalo / premio
                    </h1>

                    <small class="text-muted">
                        Salida de productos sin generar venta
                    </small>
                </div>

                <a
                    href="<?= BASE_URL ?>modules/regalos/index.php"
                    class="btn btn-outline-secondary"
                >
                    <i class="fa-solid fa-list me-1"></i>
                    Historial
                </a>

            </div>


            <div class="row g-3">

                <!-- IZQUIERDA -->
                <div class="col-12 col-lg-8">

                    <div class="card border-0 shadow-sm mb-3">

                        <div class="card-body">

                            <div class="row g-3">

                                <div class="col-12 col-md-6">

                                    <label class="form-label fw-semibold">
                                        Tipo
                                    </label>

                                    <select
                                        id="tipo_regalo"
                                        class="form-select"
                                    >
                                        <option value="REGALO">
                                            Regalo
                                        </option>

                                        <option value="PREMIO">
                                            Premio
                                        </option>

                                        <option value="CORTESIA">
                                            Cortesía
                                        </option>

                                        <option value="OTRO">
                                            Otro
                                        </option>
                                    </select>

                                </div>


                                <div class="col-12 col-md-6">

                                    <label class="form-label fw-semibold">
                                        Cliente / beneficiario
                                    </label>

                                    <select
                                        id="cliente_id"
                                        class="form-select"
                                    >
                                        <option value="">
                                            Sin cliente
                                        </option>
                                    </select>

                                </div>


                                <div class="col-12">

                                    <label class="form-label fw-semibold">
                                        Descripción
                                    </label>

                                    <textarea
                                        id="descripcion"
                                        class="form-control"
                                        rows="2"
                                        placeholder="Ej. Premio por sorteo de aniversario"
                                        required
                                    ></textarea>

                                </div>

                            </div>

                        </div>

                    </div>


                    <div class="card border-0 shadow-sm">

                        <div class="card-body">

                            <label class="form-label fw-semibold">
                                Agregar producto
                            </label>

                            <select
                                id="buscarProductoRegalo"
                                class="form-select"
                            ></select>

                        </div>


                        <div
                            id="listaProductosRegalo"
                            class="list-group list-group-flush"
                        >

                            <div class="text-center text-muted py-5">

                                <i class="fa-solid fa-gift fa-2x mb-2"></i>

                                <div>
                                    Agrega los productos entregados.
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
                                    Productos
                                </span>

                                <strong id="totalUnidadesRegalo">
                                    0
                                </strong>

                            </div>


                            <div class="d-flex justify-content-between mb-4">

                                <span class="text-muted">
                                    Costo estimado
                                </span>

                                <strong id="costoTotalRegalo">
                                    S/ 0.00
                                </strong>

                            </div>


                            <div class="alert alert-warning">

                                <i class="fa-solid fa-circle-info me-1"></i>

                                Este movimiento reduce stock, pero no genera ingreso.

                            </div>


                            <button
                                type="button"
                                class="btn btn-dark btn-lg w-100"
                                id="btnGuardarRegalo"
                                disabled
                            >
                                <i class="fa-solid fa-gift me-2"></i>
                                REGISTRAR
                            </button>

                        </div>

                    </div>

                </div>

            </div>

        </div>

    </div>

</main>


<script>
document.addEventListener('DOMContentLoaded', function () {

    let productos = [];


    // ========================================================
    // CLIENTE
    // ========================================================

    $('#cliente_id').select2({

        width: '100%',

        allowClear: true,

        placeholder: 'Buscar cliente...',

        ajax: {

            url:
                '<?= BASE_URL ?>ajax/clientes/buscar.php',

            dataType:
                'json',

            delay:
                250,

            data: function (params) {

                return {
                    q: params.term || '',
                    page: params.page || 1
                };
            },

            processResults: function (data) {
                return data;
            }
        }

    });


    // ========================================================
    // PRODUCTOS
    // ========================================================

    $('#buscarProductoRegalo').select2({

        width: '100%',

        placeholder: 'Buscar producto...',

        ajax: {

            url:
                '<?= BASE_URL ?>ajax/productos/buscar_compra.php',

            dataType:
                'json',

            delay:
                200,

            data: function (params) {

                return {
                    q: params.term || '',
                    page: params.page || 1
                };
            },

            processResults: function (data) {
                return data;
            }
        }

    });


    $('#buscarProductoRegalo').on(
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
            productos.find(
                p =>
                    p.producto_id
                    === Number(producto.id)
            );


        if (existente) {

            existente.cantidad += 1;

        } else {

            productos.push({

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


        renderProductos();
    }


    // ========================================================
    // RENDER
    // ========================================================

    function renderProductos() {

        const $lista =
            $('#listaProductosRegalo');


        $lista.empty();


        if (productos.length === 0) {

            $lista.html(`

                <div class="text-center text-muted py-5">

                    <i class="fa-solid fa-gift fa-2x mb-2"></i>

                    <div>
                        Agrega los productos entregados.
                    </div>

                </div>

            `);


            $('#btnGuardarRegalo')
                .prop('disabled', true);


            recalcular();

            return;
        }


        productos.forEach(function (item) {

            $lista.append(`

                <div
                    class="list-group-item py-3"
                    data-id="${item.producto_id}"
                >

                    <div class="row g-2 align-items-end">

                        <div class="col">

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

                            <div class="small text-muted mt-1">
                                Costo ref.:
                                S/
                                ${item.costo_unitario.toFixed(2)}
                            </div>

                        </div>


                        <div class="col-5 col-md-3">

                            <label class="form-label small">
                                Cantidad
                            </label>

                            <input
                                type="number"
                                class="form-control cantidad-regalo"
                                data-id="${item.producto_id}"
                                value="${item.cantidad}"
                                min="1"
                                step="1"
                            >

                        </div>


                        <div class="col-auto">

                            <button
                                type="button"
                                class="btn btn-outline-danger btn-eliminar-regalo"
                                data-id="${item.producto_id}"
                            >
                                <i class="fa-solid fa-trash"></i>
                            </button>

                        </div>

                    </div>

                </div>

            `);

        });


        $('#btnGuardarRegalo')
            .prop('disabled', false);


        recalcular();
    }


    // ========================================================
    // CANTIDAD
    // ========================================================

    $('#listaProductosRegalo').on(
        'input',
        '.cantidad-regalo',
        function () {

            const id =
                Number(
                    $(this).data('id')
                );


            const item =
                productos.find(
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


            recalcular();
        }
    );


    // ========================================================
    // ELIMINAR
    // ========================================================

    $('#listaProductosRegalo').on(
        'click',
        '.btn-eliminar-regalo',
        function () {

            const id =
                Number(
                    $(this).data('id')
                );


            productos =
                productos.filter(
                    p =>
                        p.producto_id !== id
                );


            renderProductos();
        }
    );


    // ========================================================
    // RESUMEN
    // ========================================================

    function recalcular() {

        let unidades = 0;
        let costo = 0;


        productos.forEach(function (item) {

            unidades +=
                item.cantidad;


            costo +=
                item.cantidad
                * item.costo_unitario;

        });


        $('#totalUnidadesRegalo')
            .text(
                unidades
            );


        $('#costoTotalRegalo')
            .text(
                'S/ '
                +
                costo.toFixed(2)
            );
    }


    // ========================================================
    // GUARDAR
    // ========================================================

    $('#btnGuardarRegalo').on(
        'click',
        function () {

            if (productos.length === 0) {
                return;
            }


            const descripcion =
                $('#descripcion')
                    .val()
                    .trim();


            if (descripcion === '') {

                Swal.fire({
                    icon: 'warning',
                    title: 'Falta descripción',
                    text:
                        'Indique por qué se están entregando los productos.'
                });

                return;
            }


            for (const item of productos) {

                if (item.cantidad <= 0) {

                    Swal.fire({
                        icon: 'warning',
                        title: 'Cantidad inválida',
                        text:
                            'Revise las cantidades de los productos.'
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
                    '<?= BASE_URL ?>ajax/regalos/guardar.php',

                type:
                    'POST',

                dataType:
                    'json',

                data: {

                    tipo:
                        $('#tipo_regalo').val(),

                    cliente_id:
                        $('#cliente_id').val(),

                    descripcion:
                        descripcion,

                    items:
                        JSON.stringify(
                            productos.map(
                                p => ({
                                    producto_id:
                                        p.producto_id,

                                    cantidad:
                                        p.cantidad
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

                        icon:
                            'success',

                        title:
                            'Registrado correctamente',

                        html: `
                            Movimiento
                            <strong>
                                #${response.regalo_id}
                            </strong>

                            <br>

                            Costo:
                            <strong>
                                S/
                                ${Number(response.costo_total).toFixed(2)}
                            </strong>
                        `,

                        confirmButtonText:
                            'Nuevo registro',

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
                            'No se pudo registrar el movimiento.'
                    });

                },

                complete: function () {

                    $btn
                        .prop('disabled', false)
                        .html(`
                            <i class="fa-solid fa-gift me-2"></i>
                            REGISTRAR
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