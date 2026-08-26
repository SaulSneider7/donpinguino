<?php

$pageTitle = 'Envases';

require_once __DIR__ . '/../../includes/header.php';
require_once __DIR__ . '/../../includes/navbar.php';

?>

<main class="container-fluid py-4">

    <div class="d-flex flex-column flex-md-row justify-content-between align-items-md-center gap-3 mb-4">

        <div>
            <h1 class="h3 fw-bold mb-1">
                Envases pendientes
            </h1>

            <p class="text-muted mb-0">
                Control de botellas retornables pendientes por cliente.
            </p>
        </div>

    </div>


    <div class="card border-0 shadow-sm">

        <div class="card-body">

            <div class="table-responsive">

                <table
                    id="tablaEnvases"
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

</main>


<!-- =========================================================
     MODAL DEVOLUCIÓN
========================================================= -->

<div
    class="modal fade"
    id="modalDevolucionEnvase"
    tabindex="-1"
    aria-hidden="true"
>

    <div class="modal-dialog modal-dialog-centered">

        <div class="modal-content">

            <form id="formDevolucionEnvase">

                <div class="modal-header">

                    <h5 class="modal-title fw-bold">
                        Registrar devolución
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
                        name="cliente_id"
                        id="envase_cliente_id"
                    >

                    <input
                        type="hidden"
                        name="tipo_envase_id"
                        id="envase_tipo_id"
                    >


                    <div class="alert alert-light border">

                        <div class="mb-2">

                            <div class="small text-muted">
                                Cliente
                            </div>

                            <div
                                class="fw-semibold"
                                id="envase_cliente_nombre"
                            ></div>

                        </div>


                        <div>

                            <div class="small text-muted">
                                Tipo de envase
                            </div>

                            <div
                                class="fw-semibold"
                                id="envase_tipo_nombre"
                            ></div>

                        </div>

                    </div>


                    <div class="mb-3">

                        <label class="form-label">
                            Pendientes actualmente
                        </label>

                        <div
                            class="fs-3 fw-bold text-danger"
                            id="envase_saldo_actual"
                        >
                            0
                        </div>

                    </div>


                    <div class="mb-3">

                        <label class="form-label">
                            Cantidad que devuelve
                        </label>

                        <input
                            type="number"
                            class="form-control form-control-lg"
                            name="cantidad"
                            id="envase_cantidad"
                            min="1"
                            step="1"
                            required
                        >

                    </div>


                    <div class="mb-0">

                        <label class="form-label">
                            Descripción
                        </label>

                        <textarea
                            class="form-control"
                            name="descripcion"
                            id="envase_descripcion"
                            rows="2"
                            placeholder="Opcional"
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
                        id="btnRegistrarDevolucion"
                    >

                        <i class="fa-solid fa-rotate-left me-2"></i>
                        Registrar devolución

                    </button>

                </div>

            </form>

        </div>

    </div>

</div>


<script>
document.addEventListener('DOMContentLoaded', function () {

    const modalElement =
        document.getElementById('modalDevolucionEnvase');

    const modalDevolucion =
        new bootstrap.Modal(modalElement);


    // ========================================================
    // DATATABLE
    // ========================================================

    const tabla =
        $('#tablaEnvases').DataTable({

            processing: true,
            serverSide: true,

            ajax: {
                url:
                    '<?= BASE_URL ?>ajax/envases/listar.php',

                type:
                    'POST'
            },

            order: [
                [0, 'asc']
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


    // ========================================================
    // ABRIR DEVOLUCIÓN
    // ========================================================

    $('#tablaEnvases').on(
        'click',
        '.btn-devolver-envase',
        function () {

            const clienteId =
                Number(
                    $(this).data('cliente-id')
                );

            const tipoEnvaseId =
                Number(
                    $(this).data('tipo-envase-id')
                );


            $.ajax({

                url:
                    '<?= BASE_URL ?>ajax/envases/obtener_saldo.php',

                type:
                    'GET',

                dataType:
                    'json',

                data: {
                    cliente_id:
                        clienteId,

                    tipo_envase_id:
                        tipoEnvaseId
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


                    const data =
                        response.data;


                    $('#formDevolucionEnvase')[0]
                        .reset();


                    $('#envase_cliente_id')
                        .val(
                            data.cliente_id
                        );


                    $('#envase_tipo_id')
                        .val(
                            data.tipo_envase_id
                        );


                    $('#envase_cliente_nombre')
                        .text(
                            data.cliente
                        );


                    $('#envase_tipo_nombre')
                        .text(
                            data.tipo_envase
                        );


                    $('#envase_saldo_actual')
                        .text(
                            Number(
                                data.saldo
                            )
                        );


                    $('#envase_cantidad')
                        .attr(
                            'max',
                            data.saldo
                        )
                        .val(
                            data.saldo
                        );


                    modalDevolucion.show();


                    setTimeout(function () {

                        $('#envase_cantidad')
                            .trigger('focus')
                            .select();

                    }, 200);

                },

                error: function (xhr) {

                    console.error(
                        xhr.responseText
                    );

                    Swal.fire({
                        icon: 'error',
                        title: 'Error',
                        text:
                            'No se pudo obtener el saldo de envases.'
                    });
                }

            });

        }
    );


    // ========================================================
    // REGISTRAR DEVOLUCIÓN
    // ========================================================

    $('#formDevolucionEnvase').on(
        'submit',
        function (e) {

            e.preventDefault();


            const $btn =
                $('#btnRegistrarDevolucion');


            $btn
                .prop(
                    'disabled',
                    true
                )
                .html(`

                    <span class="spinner-border spinner-border-sm me-2"></span>

                    Registrando...

                `);


            $.ajax({

                url:
                    '<?= BASE_URL ?>ajax/envases/registrar_devolucion.php',

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


                    modalDevolucion.hide();


                    tabla.ajax.reload(
                        null,
                        false
                    );


                    Swal.fire({
                        icon: 'success',
                        title: 'Devolución registrada',
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
                            'No se pudo registrar la devolución.'
                    });

                },

                complete: function () {

                    $btn
                        .prop(
                            'disabled',
                            false
                        )
                        .html(`

                            <i class="fa-solid fa-rotate-left me-2"></i>
                            Registrar devolución

                        `);
                }

            });

        }
    );

});
</script>

<?php
require_once __DIR__ . '/../../includes/footer.php';
?>