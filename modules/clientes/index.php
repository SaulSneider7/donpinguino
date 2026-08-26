<?php

$pageTitle = 'Clientes';

require_once __DIR__ . '/../../includes/header.php';
require_once __DIR__ . '/../../includes/navbar.php';



$clienteDeudaInicial =
    isset($_GET['deudas_cliente'])
        ? (int) $_GET['deudas_cliente']
        : 0;


?>

<main class="container-fluid py-4">

    <div class="d-flex flex-column flex-md-row justify-content-between align-items-md-center gap-3 mb-4">

        <div>
            <h1 class="h3 fw-bold mb-1">
                Clientes
            </h1>

            <p class="text-muted mb-0">
                Administra los clientes de Don Pingüino.
            </p>
        </div>

        <button
            type="button"
            class="btn btn-dark"
            id="btnNuevoCliente"
        >
            <i class="fa-solid fa-user-plus me-2"></i>
            Nuevo cliente
        </button>

    </div>


    <div class="card border-0 shadow-sm">

        <div class="card-body">

            <div class="table-responsive">

                <table
                    id="tablaClientes"
                    class="table table-hover align-middle w-100"
                >

                    <thead>
                    <tr>
                        <th>ID</th>
                        <th>Cliente</th>
                        <th>Teléfono</th>
                        <th>Nacimiento</th>
                        <th>Dirección</th>
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
     MODAL CLIENTE
========================================================= -->

<div
    class="modal fade"
    id="modalCliente"
    tabindex="-1"
    aria-hidden="true"
>

    <div class="modal-dialog modal-dialog-centered">

        <div class="modal-content">

            <form id="formCliente">

                <div class="modal-header">

                    <h5
                        class="modal-title"
                        id="tituloModalCliente"
                    >
                        Nuevo cliente
                    </h5>

                    <button
                        type="button"
                        class="btn-close"
                        data-bs-dismiss="modal"
                    ></button>

                </div>


                <div class="modal-body">

                    <input
                        type="hidden"
                        name="id"
                        id="clienteId"
                    >


                    <div class="mb-3">

                        <label class="form-label">
                            Nombre
                            <span class="text-danger">*</span>
                        </label>

                        <input
                            type="text"
                            class="form-control"
                            name="nombre"
                            id="nombre"
                            maxlength="150"
                            required
                            autocomplete="off"
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
                                id="telefono"
                                maxlength="30"
                                autocomplete="off"
                            >

                        </div>

                    </div>


                    <div class="mb-3">

                        <label class="form-label">
                            Dirección
                        </label>

                        <input
                            type="text"
                            class="form-control"
                            name="direccion"
                            id="direccion"
                            maxlength="255"
                        >

                    </div>


                    <div class="mb-3">

                        <label
                            for="fecha_nacimiento"
                            class="form-label"
                        >
                            Fecha de nacimiento
                            <span class="text-muted fw-normal">
                                (opcional)
                            </span>
                        </label>

                        <input
                            type="date"
                            class="form-control"
                            name="fecha_nacimiento"
                            id="fecha_nacimiento"
                        >

                    </div>

                    <div class="mb-0" hidden>

                        <label class="form-label">
                            Observación
                        </label>

                        <textarea
                            class="form-control"
                            name="observacion"
                            id="observacion"
                            rows="2"
                        ></textarea>

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
                        id="btnGuardarCliente"
                    >
                        <i class="fa-solid fa-floppy-disk me-2"></i>
                        Guardar
                    </button>

                </div>

            </form>

        </div>

    </div>

</div>



<!-- =========================================================
     MODAL DEUDAS
========================================================= -->
<div
    class="modal fade"
    id="modalDeudas"
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
                        Deudas
                    </h5>

                    <small
                        class="text-muted"
                        id="nombreClienteDeuda"
                    ></small>

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
                            Deuda total
                        </div>

                        <div
                            class="fs-3 fw-bold text-danger"
                            id="totalDeudaCliente"
                        >
                            S/ 0.00
                        </div>

                    </div>

                </div>


                <div id="listaVentasPendientes"></div>

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



<!-- =========================================================
     MODAL PAGOS
========================================================= -->
<div
    class="modal fade"
    id="modalRegistrarPago"
    tabindex="-1"
    aria-hidden="true"
>

    <div class="modal-dialog modal-dialog-centered">

        <div class="modal-content">

            <form id="formRegistrarPago">

                <div class="modal-header">

                    <h5 class="modal-title fw-bold">
                        Registrar pago
                    </h5>

                    <button
                        type="button"
                        class="btn-close"
                        data-bs-dismiss="modal"
                    ></button>

                </div>


                <div class="modal-body">

                    <input
                        type="hidden"
                        id="pago_venta_id"
                        name="venta_id"
                    >


                    <div class="alert alert-light border">

                        <div class="d-flex justify-content-between">

                            <span>
                                Venta
                            </span>

                            <strong id="pagoVentaNumero"></strong>

                        </div>


                        <div class="d-flex justify-content-between">

                            <span>
                                Pendiente
                            </span>

                            <strong
                                class="text-danger"
                                id="pagoSaldoActual"
                            >
                                S/ 0.00
                            </strong>

                        </div>

                    </div>


                    <div class="mb-3">

                        <label class="form-label">
                            Monto
                        </label>

                        <div class="input-group">

                            <span class="input-group-text">
                                S/
                            </span>

                            <input
                                type="number"
                                class="form-control"
                                id="pago_monto"
                                name="monto"
                                min="0.01"
                                step="0.01"
                                required
                            >

                        </div>

                    </div>


                    <div class="mb-3">

                        <label class="form-label">
                            Método de pago
                        </label>

                        <select
                            class="form-select"
                            id="pago_metodo"
                            name="metodo_pago"
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


                    <div class="mb-0">

                        <label class="form-label">
                            Observación
                        </label>

                        <textarea
                            class="form-control"
                            id="pago_observacion"
                            name="observacion"
                            rows="2"
                        ></textarea>

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
                        class="btn btn-success"
                        id="btnConfirmarPago"
                    >
                        <i class="fa-solid fa-money-bill-wave me-2"></i>
                        Registrar pago
                    </button>

                </div>

            </form>

        </div>

    </div>

</div>



<script>

const clienteDeudaInicial =
    <?= $clienteDeudaInicial ?>;

document.addEventListener('DOMContentLoaded', function () {

    const modalDeudas =
        new bootstrap.Modal(
            document.getElementById('modalDeudas')
    );

    const modalRegistrarPago =
        new bootstrap.Modal(
            document.getElementById('modalRegistrarPago')
    );

    let clienteDeudaActual = null;

    const modalElement =
        document.getElementById('modalCliente');

    const modalCliente =
        new bootstrap.Modal(modalElement);


    // ========================================================
    // DATATABLE
    // ========================================================

    const tabla = $('#tablaClientes').DataTable({

        processing: true,
        serverSide: true,

        ajax: {
            url: '<?= BASE_URL ?>ajax/clientes/listar.php',
            type: 'POST'
        },

        order: [[0, 'desc']],

        pageLength: 10,

        columns: [
            { data: 'id' },
            { data: 'cliente' },
            { data: 'telefono' },
            { data: 'fecha_nacimiento' },
            { data: 'direccion' },
            { data: 'estado' },
            {
                data: 'acciones',
                orderable: false,
                searchable: false
            }
        ],

        language: {
            url: 'https://cdn.datatables.net/plug-ins/2.3.3/i18n/es-ES.json'
        }

    });

    if (clienteDeudaInicial > 0) {

        $.ajax({

            url:
                '<?= BASE_URL ?>ajax/clientes/obtener.php',

            type:
                'GET',

            dataType:
                'json',

            data: {
                id:
                    clienteDeudaInicial
            },

            success: function (response) {

                if (!response.success) {
                    return;
                }


                clienteDeudaActual =
                    clienteDeudaInicial;


                $('#nombreClienteDeuda')
                    .text(
                        response.data.nombre
                    );


                cargarDeudasCliente(
                    clienteDeudaInicial
                );


                modalDeudas.show();
            }

        });
    }


    // ========================================================
    // LIMPIAR FORMULARIO
    // ========================================================

    function limpiarFormulario() {

        $('#formCliente')[0].reset();

        $('#clienteId').val('');

        $('#tituloModalCliente')
            .text('Nuevo cliente');

    }


    // ========================================================
    // CERRAR MODAL
    // ========================================================

    function cerrarModalCliente() {

        const activeElement =
            document.activeElement;

        if (
            activeElement
            && modalElement.contains(activeElement)
        ) {
            activeElement.blur();
        }

        modalCliente.hide();
    }


    // ========================================================
    // NUEVO
    // ========================================================

    $('#btnNuevoCliente').on('click', function () {

        limpiarFormulario();

        modalCliente.show();

        setTimeout(function () {
            $('#nombre').trigger('focus');
        }, 200);

    });


    // ========================================================
    // GUARDAR
    // ========================================================

    $('#formCliente').on('submit', function (e) {

        e.preventDefault();

        const $btn =
            $('#btnGuardarCliente');

        $btn.prop('disabled', true);

        $btn.html(`
            <span class="spinner-border spinner-border-sm me-2"></span>
            Guardando...
        `);


        $.ajax({

            url: '<?= BASE_URL ?>ajax/clientes/guardar.php',

            type: 'POST',

            dataType: 'json',

            data: $(this).serialize(),

            success: function (response) {

                if (!response.success) {

                    Swal.fire({
                        icon: 'error',
                        title: 'Error',
                        text: response.message
                    });

                    return;
                }

                cerrarModalCliente();

                tabla.ajax.reload(null, false);

                Swal.fire({
                    icon: 'success',
                    title: 'Cliente guardado',
                    text: response.message,
                    timer: 1300,
                    showConfirmButton: false
                });

            },

            error: function (xhr) {

                console.error(
                    'Error AJAX clientes:',
                    xhr.responseText
                );

                Swal.fire({
                    icon: 'error',
                    title: 'Error',
                    text: 'No se pudo procesar la solicitud.'
                });

            },

            complete: function () {

                $btn.prop('disabled', false);

                $btn.html(`
                    <i class="fa-solid fa-floppy-disk me-2"></i>
                    Guardar
                `);

            }

        });

    });



    // ========================================================
    // VER DEUDAS DEL CLIENTE
    // ========================================================

    $('#tablaClientes').on(
        'click',
        '.btn-deudas-cliente',
        function () {

            const clienteId =
                Number(
                    $(this).data('id')
                );

            const nombre =
                $(this).data('nombre');


            clienteDeudaActual =
                clienteId;


            $('#nombreClienteDeuda')
                .text(nombre);


            cargarDeudasCliente(
                clienteId
            );


            modalDeudas.show();
        }
    );


    function cargarDeudasCliente(clienteId) {

        $('#listaVentasPendientes')
            .html(`
                <div class="text-center py-4">

                    <div
                        class="spinner-border"
                        role="status"
                    ></div>

                </div>
            `);


        $.ajax({

            url:
                '<?= BASE_URL ?>ajax/pagos/ventas_pendientes.php',

            type:
                'GET',

            dataType:
                'json',

            data: {
                cliente_id:
                    clienteId
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


                $('#totalDeudaCliente')
                    .text(
                        'S/ '
                        +
                        Number(
                            response.total_deuda
                        ).toFixed(2)
                    );


                pintarVentasPendientes(
                    response.ventas
                );
            },

            error: function (xhr) {

                console.error(
                    xhr.responseText
                );
            }

        });
    }


    function pintarVentasPendientes(ventas) {

        const $lista =
            $('#listaVentasPendientes');


        $lista.empty();


        if (ventas.length === 0) {

            $lista.html(`

                <div class="text-center py-4 text-success">

                    <i class="fa-solid fa-circle-check fa-2x mb-2"></i>

                    <div class="fw-semibold">
                        Este cliente no tiene deudas.
                    </div>

                </div>

            `);

            return;
        }


        ventas.forEach(
            function (venta) {

                $lista.append(`

                    <div class="border rounded p-3 mb-2">

                        <div
                            class="d-flex flex-column flex-sm-row
                                justify-content-between
                                align-items-sm-center
                                gap-3"
                        >

                            <div>

                                <div class="fw-bold">
                                    Venta #${venta.id}
                                </div>

                                <small class="text-muted">
                                    ${venta.fecha_formateada}
                                </small>

                                <div class="mt-2 small">

                                    Total:
                                    <strong>
                                        S/
                                        ${Number(venta.total).toFixed(2)}
                                    </strong>

                                    · Pagado:
                                    <strong class="text-success">
                                        S/
                                        ${Number(venta.total_pagado).toFixed(2)}
                                    </strong>

                                </div>

                            </div>


                            <div class="text-sm-end">

                                <div class="small text-muted">
                                    Pendiente
                                </div>

                                <div class="fs-5 fw-bold text-danger mb-2">

                                    S/
                                    ${Number(venta.saldo_pendiente).toFixed(2)}

                                </div>


                                <button
                                    type="button"
                                    class="btn btn-success btn-sm btn-registrar-pago"
                                    data-venta-id="${venta.id}"
                                    data-saldo="${venta.saldo_pendiente}"
                                >
                                    <i class="fa-solid fa-money-bill-wave me-1"></i>
                                    Registrar pago
                                </button>

                            </div>

                        </div>

                    </div>

                `);
            }
        );
    }


    // ========================================================
    // ABRIR REGISTRO DE PAGO
    // ========================================================
    $('#listaVentasPendientes').on(
        'click',
        '.btn-registrar-pago',
        function () {

            const ventaId =
                Number(
                    $(this).data('venta-id')
                );

            const saldo =
                Number(
                    $(this).data('saldo')
                );


            $('#formRegistrarPago')[0]
                .reset();


            $('#pago_venta_id')
                .val(
                    ventaId
                );


            $('#pagoVentaNumero')
                .text(
                    '#' + ventaId
                );


            $('#pagoSaldoActual')
                .text(
                    'S/ '
                    + saldo.toFixed(2)
                );


            $('#pago_monto')
                .attr(
                    'max',
                    saldo
                )
                .val(
                    saldo.toFixed(2)
                );


            $('#pago_metodo')
                .val('YAPE');


            modalRegistrarPago.show();


            setTimeout(function () {

                $('#pago_monto')
                    .trigger('focus')
                    .select();

            }, 200);
        }
    );

    // ========================================================
    // GUARDAR PAGO
    // ========================================================
    $('#formRegistrarPago').on(
        'submit',
        function (e) {

            e.preventDefault();


            const $btn =
                $('#btnConfirmarPago');


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
                    '<?= BASE_URL ?>ajax/pagos/registrar.php',

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
                            title: 'No se pudo registrar',
                            text: response.message
                        });

                        return;
                    }


                    modalRegistrarPago.hide();


                    cargarDeudasCliente(
                        clienteDeudaActual
                    );


                    tabla.ajax.reload(
                        null,
                        false
                    );


                    Swal.fire({
                        icon: 'success',
                        title: 'Pago registrado',
                        text: response.message,
                        timer: 1500,
                        showConfirmButton: false
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
                            'No se pudo registrar el pago.'
                    });
                },

                complete: function () {

                    $btn
                        .prop(
                            'disabled',
                            false
                        )
                        .html(`
                            <i class="fa-solid fa-money-bill-wave me-2"></i>
                            Registrar pago
                        `);
                }

            });
        }
    );



    // ========================================================
    // EDITAR
    // ========================================================

    $('#tablaClientes').on(
        'click',
        '.btn-editar-cliente',
        function () {

            const id =
                $(this).data('id');


            $.ajax({

                url: '<?= BASE_URL ?>ajax/clientes/obtener.php',

                type: 'GET',

                dataType: 'json',

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

                    const c =
                        response.data;

                    $('#clienteId').val(c.id);

                    $('#nombre').val(
                        c.nombre ?? ''
                    );

                    $('#telefono').val(
                        c.telefono ?? ''
                    );

                    $('#direccion').val(
                        c.direccion ?? ''
                    );

                    $('#fecha_nacimiento') .val(
                        c.fecha_nacimiento || ''
                    );

                    $('#observacion').val(
                        c.observacion ?? ''
                    );

                    $('#tituloModalCliente')
                        .text('Editar cliente');

                    modalCliente.show();

                    setTimeout(function () {
                        $('#nombre').trigger('focus');
                    }, 200);

                }

            });

        }
    );


    // ========================================================
    // ACTIVAR / DESACTIVAR
    // ========================================================

    $('#tablaClientes').on(
        'click',
        '.btn-estado-cliente',
        function () {

            const id =
                $(this).data('id');

            const activo =
                Number($(this).data('activo'));

            const activar =
                activo === 0;


            Swal.fire({

                icon: 'question',

                title:
                    activar
                        ? '¿Activar cliente?'
                        : '¿Desactivar cliente?',

                text:
                    activar
                        ? 'El cliente volverá a estar disponible.'
                        : 'El cliente dejará de aparecer para nuevas ventas.',

                showCancelButton: true,

                confirmButtonText:
                    activar
                        ? 'Sí, activar'
                        : 'Sí, desactivar',

                cancelButtonText:
                    'Cancelar'

            }).then(function (result) {

                if (!result.isConfirmed) {
                    return;
                }


                $.ajax({

                    url: '<?= BASE_URL ?>ajax/clientes/cambiar_estado.php',

                    type: 'POST',

                    dataType: 'json',

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

                        tabla.ajax.reload(
                            null,
                            false
                        );

                        Swal.fire({
                            icon: 'success',
                            title: 'Actualizado',
                            text: response.message,
                            timer: 1200,
                            showConfirmButton: false
                        });

                    }

                });

            });

        }
    );

});
</script>

<?php
require_once __DIR__ . '/../../includes/footer.php';
?>