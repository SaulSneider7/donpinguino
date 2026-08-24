<?php

$pageTitle = 'Nueva venta';

require_once __DIR__ . '/../../includes/header.php';
require_once __DIR__ . '/../../includes/navbar.php';

?>

<main class="container-fluid py-3 py-md-4">

    <div class="row justify-content-center">

        <div class="col-12 col-xl-10">

            <!-- ENCABEZADO -->
            <div class="d-flex align-items-center justify-content-between mb-3">

                <div>

                    <h1 class="h3 fw-bold mb-0">
                        Nueva venta
                    </h1>

                    <small class="text-muted">
                        Registro rápido
                    </small>

                </div>

                <a
                    href="<?= BASE_URL ?>modules/ventas/index.php"
                    class="btn btn-outline-secondary"
                >
                    <i class="fa-solid fa-list me-1"></i>
                    Ventas
                </a>

            </div>


            <div class="row g-3">

                <!-- ==========================================
                     IZQUIERDA
                =========================================== -->

                <div class="col-12 col-lg-8">

                    <!-- CLIENTE -->
                    <div class="card border-0 shadow-sm mb-3">

                        <div class="card-body">

                            <label class="form-label fw-semibold">
                                Cliente
                            </label>

                            <div class="d-flex gap-2">

                                <div class="flex-grow-1">

                                    <select
                                        id="cliente_id"
                                        class="form-select"
                                    >
                                        <option value="">
                                            Cliente ocasional
                                        </option>
                                    </select>

                                </div>


                                <button
                                    type="button"
                                    class="btn btn-outline-dark"
                                    id="btnNuevoClienteRapido"
                                    title="Crear cliente"
                                >
                                    <i class="fa-solid fa-user-plus"></i>
                                </button>

                            </div>

                            <div class="form-text">
                                Es obligatorio si queda deuda o envases pendientes.
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
                                id="buscarProducto"
                                class="form-select"
                            >
                            </select>

                        </div>


                        <div
                            class="list-group list-group-flush"
                            id="listaProductos"
                        >

                            <div
                                class="text-center text-muted py-5"
                                id="carritoVacio"
                            >
                                <i class="fa-solid fa-cart-shopping fa-2x mb-2"></i>

                                <div>
                                    Agrega productos a la venta.
                                </div>
                            </div>

                        </div>

                    </div>

                </div>


                <!-- ==========================================
                     DERECHA
                =========================================== -->

                <div class="col-12 col-lg-4">

                    <div
                        class="card border-0 shadow-sm position-lg-sticky"
                        style="top: 15px;"
                    >

                        <div class="card-body">

                            <h5 class="fw-bold mb-3">
                                Resumen
                            </h5>


                            <!-- PRECIO FINAL DE PRODUCTOS -->

                            <div class="mb-3">

                                <label class="form-label small text-muted mb-1">
                                    Productos
                                </label>

                                <div class="input-group">

                                    <span class="input-group-text">
                                        S/
                                    </span>

                                    <input
                                        type="number"
                                        class="form-control fw-semibold"
                                        id="resumenSubtotal"
                                        value="0.00"
                                        min="0"
                                        step="0.01"
                                        readonly
                                    >

                                    <button
                                        type="button"
                                        class="btn btn-outline-secondary"
                                        id="btnEditarSubtotal"
                                        title="Modificar precio final"
                                    >
                                        <i class="fa-solid fa-pen"></i>
                                    </button>

                                </div>

                                <div
                                    class="form-text"
                                    id="textoPrecioManual"
                                    style="display:none;"
                                >
                                    Precio modificado manualmente.
                                </div>

                            </div>


                            <div
                                class="d-flex justify-content-between mb-2 text-success"
                                id="filaDescuento"
                                style="display:none !important;"
                            >

                                <span>
                                    Promociones
                                </span>

                                <span id="resumenDescuento">
                                    - S/ 0.00
                                </span>

                            </div>


                            <hr>


                            <div class="mb-3">

                                <label
                                    for="delivery"
                                    class="form-label small text-muted mb-1"
                                >
                                    Delivery
                                </label>

                                <div class="input-group">

                                    <span class="input-group-text">
                                        S/
                                    </span>

                                    <input
                                        type="number"
                                        class="form-control"
                                        id="delivery"
                                        min="0"
                                        step="0.50"
                                        value="0.00"
                                    >

                                </div>

                            </div>

                            <hr>



                            <div class="mb-3">

                                <label
                                    for="observacionVenta"
                                    class="form-label small text-muted mb-1"
                                >
                                    Comentario
                                    <span class="text-muted fw-normal">
                                        (opcional)
                                    </span>
                                </label>

                                <textarea
                                    class="form-control"
                                    id="observacionVenta"
                                    rows="2"
                                    maxlength="500"
                                    placeholder="Ej. Precio acordado con cliente..."
                                ></textarea>

                            </div>

                            <div class="d-flex justify-content-between align-items-center mb-4">

                                <span class="fw-semibold">
                                    TOTAL
                                </span>

                                <span
                                    class="fs-3 fw-bold"
                                    id="resumenTotal"
                                >
                                    S/ 0.00
                                </span>

                            </div>


                            <!-- ENVASES -->
                            <div
                                id="bloqueEnvases"
                                class="mb-4"
                                style="display:none;"
                            >

                                <div class="alert alert-warning mb-0">

                                    <div class="fw-semibold mb-2">

                                        <i class="fa-solid fa-bottle-water me-1"></i>

                                        Envases

                                    </div>

                                    <div id="listaEnvases"></div>

                                </div>

                            </div>


                            <!-- PAGO -->
                            <label class="form-label fw-semibold">
                                Pago
                            </label>


                            <div
                                class="btn-group w-100 mb-3"
                                role="group"
                            >

                                <input
                                    type="radio"
                                    class="btn-check"
                                    name="tipo_pago"
                                    id="pagoCompleto"
                                    value="COMPLETO"
                                    checked
                                >

                                <label
                                    class="btn btn-outline-success"
                                    for="pagoCompleto"
                                >
                                    Pagó
                                </label>


                                <input
                                    type="radio"
                                    class="btn-check"
                                    name="tipo_pago"
                                    id="pagoParcial"
                                    value="PARCIAL"
                                >

                                <label
                                    class="btn btn-outline-warning"
                                    for="pagoParcial"
                                >
                                    Parcial
                                </label>


                                <input
                                    type="radio"
                                    class="btn-check"
                                    name="tipo_pago"
                                    id="pagoPendiente"
                                    value="PENDIENTE"
                                >

                                <label
                                    class="btn btn-outline-danger"
                                    for="pagoPendiente"
                                >
                                    Debe
                                </label>

                            </div>


                            <div
                                id="bloqueMontoPagado"
                                class="mb-3"
                                style="display:none;"
                            >

                                <label class="form-label">
                                    ¿Cuánto pagó?
                                </label>

                                <div class="input-group">

                                    <span class="input-group-text">
                                        S/
                                    </span>

                                    <input
                                        type="number"
                                        class="form-control"
                                        id="monto_pagado"
                                        min="0"
                                        step="0.01"
                                    >

                                </div>

                            </div>


                            <div
                                id="bloqueMetodoPago"
                                class="mb-3"
                            >

                                <label class="form-label">
                                    Método
                                </label>

                                <select
                                    id="metodo_pago"
                                    class="form-select"
                                >

                                    <option value="YAPE">
                                        Yape
                                    </option>

                                    <option value="PLIN">
                                        Plin
                                    </option>

                                    <option value="EFECTIVO">
                                        Efectivo
                                    </option>

                                    <option value="OTRO">
                                        Otro
                                    </option>

                                </select>

                            </div>


                            <div
                                class="alert alert-light border"
                                id="resumenPago"
                            >
                                <div class="d-flex justify-content-between">

                                    <span>Pagado</span>

                                    <strong id="textoPagado">
                                        S/ 0.00
                                    </strong>

                                </div>

                                <div class="d-flex justify-content-between">

                                    <span>Pendiente</span>

                                    <strong
                                        class="text-danger"
                                        id="textoPendiente"
                                    >
                                        S/ 0.00
                                    </strong>

                                </div>

                            </div>


                            <button
                                type="button"
                                class="btn btn-dark btn-lg w-100"
                                id="btnGuardarVenta"
                                disabled
                            >

                                <i class="fa-solid fa-check me-2"></i>
                                REGISTRAR VENTA

                            </button>

                        </div>

                    </div>

                </div>

            </div>

        </div>

    </div>

</main>


<div
    class="modal fade"
    id="modalClienteRapido"
    tabindex="-1"
    aria-hidden="true"
>

    <div class="modal-dialog modal-dialog-centered">

        <div class="modal-content">

            <form id="formClienteRapido">

                <div class="modal-header">

                    <div>

                        <h5 class="modal-title fw-bold mb-0">
                            Nuevo cliente
                        </h5>

                        <small class="text-muted">
                            Registro rápido
                        </small>

                    </div>


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
                            <span class="text-danger">*</span>
                        </label>

                        <input
                            type="text"
                            class="form-control form-control-lg"
                            name="nombre"
                            id="clienteRapidoNombre"
                            maxlength="150"
                            autocomplete="off"
                            required
                        >

                    </div>


                    <div class="mb-3">

                        <label class="form-label">
                            Teléfono
                        </label>

                        <div class="input-group">

                            <span class="input-group-text">
                                <i class="fa-solid fa-phone"></i>
                            </span>

                            <input
                                type="text"
                                class="form-control"
                                name="telefono"
                                id="clienteRapidoTelefono"
                                maxlength="30"
                                autocomplete="off"
                            >

                        </div>

                    </div>


                    <div class="mb-0">

                        <label class="form-label">
                            Dirección
                        </label>

                        <input
                            type="text"
                            class="form-control"
                            name="direccion"
                            id="clienteRapidoDireccion"
                            maxlength="255"
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
                        id="btnGuardarClienteRapido"
                    >
                        <i class="fa-solid fa-user-plus me-2"></i>
                        Crear cliente
                    </button>

                </div>

            </form>

        </div>

    </div>

</div>

<script>
document.addEventListener('DOMContentLoaded', function () {

    const modalClienteRapidoElement =
        document.getElementById(
            'modalClienteRapido'
        );


    const modalClienteRapido =
        new bootstrap.Modal(
            modalClienteRapidoElement
        );

    let carrito = [];

    let calculoActual = null;

    let timerCalculo = null;

    /*
    * Indica si el administrador modificó manualmente
    * el precio final de los productos.
    */
    let precioManualEditado = false;


    // ========================================================
    // NUEVO CLIENTE RÁPIDO
    // ========================================================

    $('#btnNuevoClienteRapido').on(
        'click',
        function () {

            $('#formClienteRapido')[0]
                .reset();


            modalClienteRapido.show();


            setTimeout(function () {

                $('#clienteRapidoNombre')
                    .trigger('focus');

            }, 200);
        }
    );


    // ========================================================
    // CLIENTES
    // ========================================================

    $('#cliente_id').select2({

        width: '100%',

        placeholder:
            'Buscar cliente...',

        allowClear: true,

        ajax: {

            url:
                '<?= BASE_URL ?>ajax/clientes/buscar.php',

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
            },

            cache: true
        }
    });




    $('#formClienteRapido').on(
        'submit',
        function (e) {

            e.preventDefault();


            const $btn =
                $('#btnGuardarClienteRapido');


            $btn
                .prop(
                    'disabled',
                    true
                )
                .html(`

                    <span
                        class="spinner-border spinner-border-sm me-2"
                    ></span>

                    Creando...

                `);


            $.ajax({

                url:
                    '<?= BASE_URL ?>ajax/clientes/guardar_rapido.php',

                type:
                    'POST',

                dataType:
                    'json',

                data:
                    $(this).serialize(),

                success: function (response) {

                    if (!response.success) {

                        /*
                        * Si ya existe alguien con ese
                        * teléfono, podemos ofrecer
                        * seleccionarlo directamente.
                        */
                        if (
                            response.cliente_existente
                        ) {

                            const existente =
                                response
                                    .cliente_existente;


                            Swal.fire({

                                icon:
                                    'info',

                                title:
                                    'Cliente existente',

                                text:
                                    'Ya existe '
                                    +
                                    existente.nombre
                                    +
                                    ' con ese teléfono.',

                                showCancelButton:
                                    true,

                                confirmButtonText:
                                    'Usar este cliente',

                                cancelButtonText:
                                    'Cancelar'

                            }).then(
                                function (result) {

                                    if (
                                        !result.isConfirmed
                                    ) {
                                        return;
                                    }


                                    seleccionarClienteRapido(
                                        existente.id,
                                        existente.nombre,
                                        existente.telefono
                                    );


                                    cerrarModalClienteRapido();

                                }
                            );


                            return;
                        }


                        Swal.fire({
                            icon: 'error',
                            title: 'No se pudo crear',
                            text: response.message
                        });


                        return;
                    }


                    seleccionarClienteRapido(
                        response.id,
                        response.nombre,
                        response.telefono
                    );


                    cerrarModalClienteRapido();


                    Swal.fire({

                        icon:
                            'success',

                        title:
                            'Cliente creado',

                        timer:
                            900,

                        showConfirmButton:
                            false

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
                            'No se pudo registrar el cliente.'
                    });

                },

                complete: function () {

                    $btn
                        .prop(
                            'disabled',
                            false
                        )
                        .html(`

                            <i class="fa-solid fa-user-plus me-2"></i>
                            Crear cliente

                        `);
                }

            });

        }
    );


    // ========================================================
    // PRODUCTOS
    // ========================================================

    $('#buscarProducto').select2({

        width: '100%',

        placeholder:
            'Buscar producto...',

        minimumInputLength:
            0,

        ajax: {

            url:
                '<?= BASE_URL ?>ajax/productos/buscar.php',

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


    $('#buscarProducto').on(
        'select2:select',
        function (e) {

            const producto =
                e.params.data;


            agregarProducto(producto);


            $(this)
                .val(null)
                .trigger('change');


            setTimeout(function () {

                $('#buscarProducto')
                    .select2('open');

            }, 100);

        }
    );


    // ========================================================
    // AGREGAR PRODUCTO
    // ========================================================

    function agregarProducto(producto) {

        const existente =
            carrito.find(
                item =>
                    Number(item.producto_id)
                    ===
                    Number(producto.id)
            );


        if (existente) {

            existente.cantidad += 1;

        } else {

            carrito.push({
                producto_id:
                    Number(producto.id),

                nombre:
                    producto.nombre,

                presentacion:
                    producto.presentacion,

                cantidad:
                    1,

                precio_venta:
                    Number(
                        producto.precio_venta
                    )
            });
        }


        renderCarrito();

        solicitarCalculo();
    }


    // ========================================================
    // RENDER CARRITO
    // ========================================================

    function renderCarrito() {

        const $lista =
            $('#listaProductos');


        $lista.empty();


        if (carrito.length === 0) {

            $lista.html(`

                <div class="text-center text-muted py-5">

                    <i class="fa-solid fa-cart-shopping fa-2x mb-2"></i>

                    <div>
                        Agrega productos a la venta.
                    </div>

                </div>

            `);


            $('#btnGuardarVenta')
                .prop('disabled', true);


            return;
        }


        carrito.forEach(function (item) {

            const html = `

                <div
                    class="list-group-item py-3"
                    data-producto-id="${item.producto_id}"
                >

                    <div class="d-flex gap-3 align-items-center">

                        <div class="flex-grow-1">

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

                            <div
                                class="mt-1 small detalle-precio"
                                id="detallePrecio${item.producto_id}"
                            >
                                S/ ${item.precio_venta.toFixed(2)}
                            </div>

                        </div>


                        <div
                            class="input-group input-group-sm"
                            style="width:130px;"
                        >

                            <button
                                type="button"
                                class="btn btn-outline-secondary btn-restar"
                                data-id="${item.producto_id}"
                            >
                                <i class="fa-solid fa-minus"></i>
                            </button>


                            <input
                                type="number"
                                class="form-control text-center cantidad-producto"
                                data-id="${item.producto_id}"
                                value="${item.cantidad}"
                                min="1"
                                step="1"
                            >


                            <button
                                type="button"
                                class="btn btn-outline-secondary btn-sumar"
                                data-id="${item.producto_id}"
                            >
                                <i class="fa-solid fa-plus"></i>
                            </button>

                        </div>


                        <button
                            type="button"
                            class="btn btn-outline-danger btn-sm btn-eliminar"
                            data-id="${item.producto_id}"
                        >
                            <i class="fa-solid fa-trash"></i>
                        </button>

                    </div>

                </div>

            `;


            $lista.append(html);
        });


        $('#btnGuardarVenta')
            .prop('disabled', false);
    }


    // ========================================================
    // + CANTIDAD
    // ========================================================

    $('#listaProductos').on(
        'click',
        '.btn-sumar',
        function () {

            const id =
                Number($(this).data('id'));


            const item =
                carrito.find(
                    p =>
                        p.producto_id === id
                );


            if (!item) {
                return;
            }


            item.cantidad += 1;


            renderCarrito();

            solicitarCalculo();
        }
    );


    // ========================================================
    // - CANTIDAD
    // ========================================================

    $('#listaProductos').on(
        'click',
        '.btn-restar',
        function () {

            const id =
                Number($(this).data('id'));


            const item =
                carrito.find(
                    p =>
                        p.producto_id === id
                );


            if (!item) {
                return;
            }


            if (item.cantidad <= 1) {

                carrito =
                    carrito.filter(
                        p =>
                            p.producto_id !== id
                    );

            } else {

                item.cantidad -= 1;
            }


            renderCarrito();

            solicitarCalculo();
        }
    );


    // ========================================================
    // INPUT CANTIDAD
    // ========================================================

    $('#listaProductos').on(
        'change',
        '.cantidad-producto',
        function () {

            const id =
                Number($(this).data('id'));


            let cantidad =
                Number($(this).val());


            if (
                !Number.isFinite(cantidad)
                ||
                cantidad <= 0
            ) {
                cantidad = 1;
            }


            const item =
                carrito.find(
                    p =>
                        p.producto_id === id
                );


            if (item) {
                item.cantidad = cantidad;
            }


            renderCarrito();

            solicitarCalculo();
        }
    );


    // ========================================================
    // ELIMINAR
    // ========================================================

    $('#listaProductos').on(
        'click',
        '.btn-eliminar',
        function () {

            const id =
                Number($(this).data('id'));


            carrito =
                carrito.filter(
                    p =>
                        p.producto_id !== id
                );


            renderCarrito();

            solicitarCalculo();
        }
    );


    // ========================================================
    // CALCULAR
    // ========================================================

    function solicitarCalculo() {

        clearTimeout(timerCalculo);


        if (carrito.length === 0) {

            calculoActual = null;

            pintarResumenVacio();

            return;
        }


        timerCalculo =
            setTimeout(
                calcularVenta,
                120
            );
    }


    function calcularVenta() {

        const items =
            carrito.map(
                item => ({
                    producto_id:
                        item.producto_id,

                    cantidad:
                        item.cantidad
                })
            );


        $.ajax({

            url:
                '<?= BASE_URL ?>ajax/ventas/calcular.php',

            type:
                'POST',

            dataType:
                'json',

            data: {
                items:
                    JSON.stringify(items)
            },

            success: function (response) {

                if (!response.success) {

                    Swal.fire({
                        icon: 'warning',
                        title: 'No se pudo calcular',
                        text: response.message
                    });

                    return;
                }


                calculoActual =
                    response;


                pintarCalculo(response);
            },

            error: function (xhr) {

                console.error(
                    xhr.responseText
                );
            }
        });
    }


    // ========================================================
    // PINTAR CÁLCULO
    // ========================================================

    function pintarCalculo(data) {

        /*
        * Cada vez que cambia el carrito,
        * volvemos al cálculo automático.
        */
        precioManualEditado = false;

        $('#resumenSubtotal')
            .val(
                Number(
                    data.total
                ).toFixed(2)
            )
            .prop(
                'readonly',
                true
            );


        $('#textoPrecioManual')
            .hide();


        $('#btnEditarSubtotal')
            .html(
                '<i class="fa-solid fa-pen"></i>'
            );


        $('#resumenTotal')
            .text(
                'S/ '
                +
                Number(
                    data.total
                ).toFixed(2)
            );


        if (
            Number(
                data.descuento_promociones
            ) > 0
        ) {

            $('#filaDescuento')
                .attr(
                    'style',
                    'display:flex !important;'
                );


            $('#resumenDescuento')
                .text(
                    '- S/ '
                    +
                    Number(
                        data.descuento_promociones
                    ).toFixed(2)
                );

        } else {

            $('#filaDescuento')
                .attr(
                    'style',
                    'display:none !important;'
                );
        }


        // PROMOCIONES POR PRODUCTO

        data.detalle.forEach(
            function (detalle) {

                const $detalle =
                    $(
                        '#detallePrecio'
                        +
                        detalle.producto_id
                    );


                if (
                    detalle.promocion_nombre
                ) {

                    $detalle.html(`

                        <span>
                            S/
                            ${Number(detalle.subtotal_final).toFixed(2)}
                        </span>

                        <span class="badge text-bg-success ms-1">

                            <i class="fa-solid fa-tag me-1"></i>

                            ${escapeHtml(detalle.promocion_nombre)}

                        </span>

                    `);

                } else {

                    $detalle.text(
                        'S/ '
                        +
                        Number(
                            detalle.subtotal_final
                        ).toFixed(2)
                    );
                }
            }
        );


        pintarEnvases(
            data.envases || []
        );


        actualizarPago();
    }


    // ========================================================
    // ENVASES
    // ========================================================

    function pintarEnvases(envases) {

        const $lista =
            $('#listaEnvases');


        $lista.empty();


        if (envases.length === 0) {

            $('#bloqueEnvases').hide();

            return;
        }


        $('#bloqueEnvases').show();


        envases.forEach(
            function (envase) {

                const requerido =
                    Number(
                        envase.cantidad_requerida
                    );


                $lista.append(`

                    <div class="mb-3">

                        <div class="small fw-semibold mb-1">
                            ${escapeHtml(envase.nombre)}
                        </div>

                        <div class="small mb-2">
                            Requeridos:
                            <strong>
                                ${requerido}
                            </strong>
                        </div>

                        <label class="form-label small mb-1">
                            Entregó
                        </label>

                        <input
                            type="number"
                            class="form-control form-control-sm envase-entregado"
                            data-tipo-envase-id="${envase.tipo_envase_id}"
                            data-requerido="${requerido}"
                            value="${requerido}"
                            min="0"
                            max="${requerido}"
                            step="1"
                        >

                    </div>

                `);
            }
        );
    }


    // ========================================================
    // EDITAR PRECIO FINAL DE PRODUCTOS
    // ========================================================

    $('#btnEditarSubtotal').on(
        'click',
        function () {

            const $input =
                $('#resumenSubtotal');


            if (
                $input.prop('readonly')
            ) {

                $input
                    .prop(
                        'readonly',
                        false
                    )
                    .trigger('focus')
                    .select();


                $(this).html(
                    '<i class="fa-solid fa-check"></i>'
                );

            } else {

                finalizarEdicionPrecio();
            }
        }
    );



    function finalizarEdicionPrecio() {

        const $input =
            $('#resumenSubtotal');


        let valor =
            Number(
                $input.val()
            );


        if (
            !Number.isFinite(valor)
            ||
            valor < 0
        ) {

            valor =
                Number(
                    calculoActual?.total
                    || 0
                );
        }


        $input
            .val(
                valor.toFixed(2)
            )
            .prop(
                'readonly',
                true
            );


        precioManualEditado = true;


        $('#textoPrecioManual')
            .show();


        $('#btnEditarSubtotal')
            .html(
                '<i class="fa-solid fa-pen"></i>'
            );


        actualizarTotalFinal();
    }


    $('#resumenSubtotal').on(
        'input',
        function () {

            precioManualEditado = true;

            actualizarTotalFinal();
        }
    );

    $('#resumenSubtotal').on(
        'blur',
        function () {

            if (
                !$(this).prop('readonly')
            ) {

                finalizarEdicionPrecio();
            }
        }
    );

    $('#delivery').on(
        'input',
        function () {

            actualizarTotalFinal();
        }
    );



    function obtenerTotalProductos() {

        let valor =
            Number(
                $('#resumenSubtotal').val()
                || 0
            );


        if (
            !Number.isFinite(valor)
            ||
            valor < 0
        ) {
            valor = 0;
        }


        return valor;
    }


    function obtenerDelivery() {

        let valor =
            Number(
                $('#delivery').val()
                || 0
            );


        if (
            !Number.isFinite(valor)
            ||
            valor < 0
        ) {
            valor = 0;
        }


        return valor;
    }


    function obtenerTotalFinal() {

        return (
            obtenerTotalProductos()
            +
            obtenerDelivery()
        );
    }


    function actualizarTotalFinal() {

        const total =
            obtenerTotalFinal();


        $('#resumenTotal')
            .text(
                'S/ '
                +
                total.toFixed(2)
            );


        actualizarPago();
    }



    // ========================================================
    // PAGO
    // ========================================================

    $('input[name="tipo_pago"]')
        .on(
            'change',
            function () {

                const tipo =
                    $(
                        'input[name="tipo_pago"]:checked'
                    ).val();


                if (tipo === 'PARCIAL') {

                    $('#bloqueMontoPagado')
                        .show();

                    $('#bloqueMetodoPago')
                        .show();

                } else if (
                    tipo === 'PENDIENTE'
                ) {

                    $('#bloqueMontoPagado')
                        .hide();

                    $('#bloqueMetodoPago')
                        .hide();

                } else {

                    $('#bloqueMontoPagado')
                        .hide();

                    $('#bloqueMetodoPago')
                        .show();
                }


                actualizarPago();
            }
        );


    $('#monto_pagado')
        .on(
            'input',
            actualizarPago
        );


    function actualizarPago() {

        const total =
            obtenerTotalFinal();


        const tipo =
            $(
                'input[name="tipo_pago"]:checked'
            ).val();


        let pagado = 0;


        if (tipo === 'COMPLETO') {

            pagado =
                total;

        } else if (
            tipo === 'PARCIAL'
        ) {

            pagado =
                Number(
                    $('#monto_pagado').val()
                    || 0
                );


            if (pagado > total) {
                pagado = total;
            }

        } else {

            pagado = 0;
        }


        const pendiente =
            Math.max(
                0,
                total - pagado
            );


        $('#textoPagado')
            .text(
                'S/ '
                +
                pagado.toFixed(2)
            );


        $('#textoPendiente')
            .text(
                'S/ '
                +
                pendiente.toFixed(2)
            );
    }


    // ========================================================
    // RESUMEN VACÍO
    // ========================================================

    function pintarResumenVacio() {

        $('#resumenSubtotal')
            .text('S/ 0.00');

        $('#resumenTotal')
            .text('S/ 0.00');

        $('#filaDescuento')
            .attr(
                'style',
                'display:none !important;'
            );

        $('#bloqueEnvases')
            .hide();

        actualizarPago();
    }


    // ========================================================
    // GUARDAR
    // ========================================================

    $('#btnGuardarVenta')
        .on(
            'click',
            function () {

                if (
                    !calculoActual
                    ||
                    carrito.length === 0
                ) {

                    return;
                }


                const tipoPago =
                    $(
                        'input[name="tipo_pago"]:checked'
                    ).val();


                let montoPagado = 0;


                if (
                    tipoPago === 'COMPLETO'
                ) {

                    montoPagado = obtenerTotalFinal()

                } else if (
                    tipoPago === 'PARCIAL'
                ) {

                    montoPagado =
                        Number(
                            $('#monto_pagado').val()
                            || 0
                        );

                }


                const clienteId =
                    $('#cliente_id').val();


                const envases = [];


                $('.envase-entregado')
                    .each(
                        function () {

                            envases.push({

                                tipo_envase_id:
                                    Number(
                                        $(this)
                                            .data(
                                                'tipo-envase-id'
                                            )
                                    ),

                                cantidad_entregada:
                                    Number(
                                        $(this)
                                            .val()
                                        || 0
                                    )

                            });

                        }
                    );


                const existeEnvasePendiente =
                    $('.envase-entregado')
                        .toArray()
                        .some(
                            input =>
                                Number(input.value)
                                <
                                Number(
                                    $(input)
                                        .data('requerido')
                                )
                        );


                const existeDeuda =
                    tipoPago !== 'COMPLETO';


                if (
                    (
                        existeDeuda
                        ||
                        existeEnvasePendiente
                    )
                    &&
                    !clienteId
                ) {

                    Swal.fire({
                        icon: 'warning',
                        title: 'Seleccione un cliente',
                        text:
                            'Las ventas con deuda o envases pendientes necesitan un cliente.'
                    });

                    return;
                }


                if (
                    tipoPago === 'PARCIAL'
                    &&
                    (
                        montoPagado <= 0
                        ||
                        montoPagado >= obtenerTotalFinal()
                    )
                ) {

                    Swal.fire({
                        icon: 'warning',
                        title: 'Monto inválido',
                        text:
                            'Para un pago parcial, el monto debe ser mayor a 0 y menor al total.'
                    });

                    return;
                }


                guardarVenta({
                    cliente_id:
                        clienteId,

                    tipo_pago:
                        tipoPago,

                    monto_pagado:
                        montoPagado,

                    metodo_pago:
                        $('#metodo_pago').val(),

                    items:
                        carrito.map(
                            p => ({
                                producto_id:
                                    p.producto_id,

                                cantidad:
                                    p.cantidad
                            })
                        ),

                    envases:
                        envases,

                    total_manual:
                        precioManualEditado
                            ? obtenerTotalProductos()
                            : null,

                    delivery:
                        obtenerDelivery(),

                    observacion:
                        $('#observacionVenta')
                            .val()
                            .trim(),
                });

            }
        );


    function guardarVenta(data) {

        const $btn =
            $('#btnGuardarVenta');


        $btn
            .prop(
                'disabled',
                true
            )
            .html(`

                <span
                    class="spinner-border spinner-border-sm me-2"
                ></span>

                Registrando...

            `);


        $.ajax({

            url:
                '<?= BASE_URL ?>ajax/ventas/guardar.php',

            type:
                'POST',

            dataType:
                'json',

            data: {
                cliente_id:
                    data.cliente_id,

                tipo_pago:
                    data.tipo_pago,

                monto_pagado:
                    data.monto_pagado,

                metodo_pago:
                    data.metodo_pago,

                total_manual:
                    data.total_manual === null
                        ? ''
                        : data.total_manual,

                delivery:
                    data.delivery,

                observacion:
                    data.observacion,

                items:
                    JSON.stringify(
                        data.items
                    ),

                envases:
                    JSON.stringify(
                        data.envases
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
                        'Venta registrada',

                    html:
                        `
                        Venta
                        <strong>#${response.venta_id}</strong>
                        <br>
                        Total:
                        <strong>
                            S/
                            ${Number(response.total).toFixed(2)}
                        </strong>
                        `,

                    confirmButtonText:
                        'Nueva venta',

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
                        'Ocurrió un error al registrar la venta.'
                });

            },

            complete: function () {

                $btn
                    .prop(
                        'disabled',
                        false
                    )
                    .html(`

                        <i class="fa-solid fa-check me-2"></i>
                        REGISTRAR VENTA

                    `);
            }

        });
    }


    function escapeHtml(text) {

        return $('<div>')
            .text(text ?? '')
            .html();
    }
    

    function seleccionarClienteRapido(
        id,
        nombre,
        telefono
    ) {

        let texto =
            nombre;


        if (telefono) {

            texto +=
                ' - '
                +
                telefono;
        }


        /*
        * Select2 usa AJAX.
        * Como el cliente recién creado todavía
        * no existe entre las opciones cargadas,
        * insertamos manualmente una opción.
        */
        const option =
            new Option(
                texto,
                id,
                true,
                true
            );


        $('#cliente_id')
            .append(option)
            .trigger('change');
    }


    function cerrarModalClienteRapido() {

        const activeElement =
            document.activeElement;


        if (
            activeElement
            &&
            modalClienteRapidoElement
                .contains(
                    activeElement
                )
        ) {

            activeElement.blur();
        }


        modalClienteRapido.hide();
    }

});
</script>

<?php
require_once __DIR__ . '/../../includes/footer.php';
?>