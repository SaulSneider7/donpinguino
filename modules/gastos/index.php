<?php

$pageTitle = 'Gastos';

require_once __DIR__ . '/../../includes/header.php';
require_once __DIR__ . '/../../includes/navbar.php';

?>

<main class="container-fluid py-4">

    <div class="d-flex flex-column flex-md-row justify-content-between align-items-md-center gap-3 mb-4">

        <div>
            <h1 class="h3 fw-bold mb-1">
                Gastos
            </h1>

            <p class="text-muted mb-0">
                Registra gastos operativos del negocio.
            </p>
        </div>

        <button
            type="button"
            class="btn btn-dark"
            id="btnNuevoGasto"
        >
            <i class="fa-solid fa-plus me-2"></i>
            Nuevo gasto
        </button>

    </div>


    <div class="card border-0 shadow-sm">

        <div class="card-body">

            <div class="table-responsive">

                <table
                    id="tablaGastos"
                    class="table table-hover align-middle w-100"
                >

                    <thead>
                    <tr>
                        <th>ID</th>
                        <th>Fecha</th>
                        <th>Tipo</th>
                        <th>Descripción</th>
                        <th>Monto</th>
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
     MODAL GASTO
========================================================= -->

<div
    class="modal fade"
    id="modalGasto"
    tabindex="-1"
    aria-hidden="true"
>

    <div class="modal-dialog modal-dialog-centered">

        <div class="modal-content">

            <form id="formGasto">

                <div class="modal-header">

                    <h5
                        class="modal-title fw-bold"
                        id="tituloModalGasto"
                    >
                        Nuevo gasto
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
                        id="gastoId"
                    >


                    <div class="mb-3">

                        <label class="form-label">
                            Tipo
                        </label>

                        <select
                            class="form-select"
                            name="tipo"
                            id="tipo"
                            required
                        >

                            <option value="AGUA">
                                Agua
                            </option>

                            <option value="LUZ">
                                Luz
                            </option>

                            <option value="COMIDA">
                                Comida
                            </option>

                            <option value="INSUMOS">
                                Insumos
                            </option>

                            <option value="DELIVERY">
                                Delivery
                            </option>

                            <option value="ALQUILER">
                                Alquiler
                            </option>

                            <option value="OTRO">
                                Otro
                            </option>

                        </select>

                    </div>


                    <div class="mb-3">

                        <label class="form-label">
                            Descripción
                        </label>

                        <input
                            type="text"
                            class="form-control"
                            name="descripcion"
                            id="descripcion"
                            maxlength="255"
                            placeholder="Ej. Recibo de luz agosto"
                            required
                        >

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
                                name="monto"
                                id="monto"
                                min="0.01"
                                step="0.01"
                                required
                            >

                        </div>

                    </div>


                    <div class="mb-3">

                        <label class="form-label">
                            Fecha
                        </label>

                        <input
                            type="date"
                            class="form-control"
                            name="fecha"
                            id="fecha"
                            required
                        >

                    </div>


                    <div class="mb-0">

                        <label class="form-label">
                            Observación
                            <span class="text-muted fw-normal">
                                (opcional)
                            </span>
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
                        id="btnGuardarGasto"
                    >
                        <i class="fa-solid fa-floppy-disk me-2"></i>
                        Guardar
                    </button>

                </div>

            </form>

        </div>

    </div>

</div>


<script>
document.addEventListener('DOMContentLoaded', function () {

    const modalElement =
        document.getElementById('modalGasto');


    const modalGasto =
        new bootstrap.Modal(
            modalElement
        );


    // ========================================================
    // DATATABLE
    // ========================================================

    const tabla =
        $('#tablaGastos').DataTable({

            processing:
                true,

            serverSide:
                true,

            ajax: {

                url:
                    '<?= BASE_URL ?>ajax/gastos/listar.php',

                type:
                    'POST'
            },

            order: [
                [1, 'desc']
            ],

            pageLength:
                10,

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
                    data: 'descripcion'
                },

                {
                    data: 'monto'
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
    // NUEVO
    // ========================================================

    $('#btnNuevoGasto').on(
        'click',
        function () {

            $('#formGasto')[0]
                .reset();


            $('#gastoId')
                .val('');


            $('#tituloModalGasto')
                .text(
                    'Nuevo gasto'
                );


            /*
             * Fecha por defecto: hoy.
             */
            $('#fecha')
                .val(
                    '<?= date('Y-m-d') ?>'
                );


            $('#tipo')
                .val('OTRO');


            modalGasto.show();


            setTimeout(
                function () {

                    $('#descripcion')
                        .trigger('focus');

                },
                200
            );
        }
    );


    // ========================================================
    // CERRAR MODAL
    // ========================================================

    function cerrarModal() {

        const active =
            document.activeElement;


        if (
            active
            &&
            modalElement.contains(
                active
            )
        ) {

            active.blur();
        }


        modalGasto.hide();
    }


    // ========================================================
    // GUARDAR
    // ========================================================

    $('#formGasto').on(
        'submit',
        function (e) {

            e.preventDefault();


            const $btn =
                $('#btnGuardarGasto');


            $btn
                .prop(
                    'disabled',
                    true
                )
                .html(`

                    <span
                        class="spinner-border spinner-border-sm me-2"
                    ></span>

                    Guardando...

                `);


            $.ajax({

                url:
                    '<?= BASE_URL ?>ajax/gastos/guardar.php',

                type:
                    'POST',

                dataType:
                    'json',

                data:
                    $(this)
                        .serialize(),

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


                        cerrarModal();


                        tabla.ajax.reload(
                            null,
                            false
                        );


                        Swal.fire({

                            icon:
                                'success',

                            title:
                                'Gasto guardado',

                            text:
                                response.message,

                            timer:
                                1200,

                            showConfirmButton:
                                false
                        });

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
                                'No se pudo guardar el gasto.'
                        });

                    },

                complete:
                    function () {

                        $btn
                            .prop(
                                'disabled',
                                false
                            )
                            .html(`

                                <i class="fa-solid fa-floppy-disk me-2"></i>
                                Guardar

                            `);
                    }

            });

        }
    );


    // ========================================================
    // EDITAR
    // ========================================================

    $('#tablaGastos').on(
        'click',
        '.btn-editar-gasto',
        function () {

            const id =
                Number(
                    $(this)
                        .data('id')
                );


            $.ajax({

                url:
                    '<?= BASE_URL ?>ajax/gastos/obtener.php',

                type:
                    'GET',

                dataType:
                    'json',

                data: {
                    id: id
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


                        const g =
                            response.data;


                        $('#gastoId')
                            .val(
                                g.id
                            );


                        $('#tipo')
                            .val(
                                g.tipo
                            );


                        $('#descripcion')
                            .val(
                                g.descripcion
                            );


                        $('#monto')
                            .val(
                                g.monto
                            );


                        $('#fecha')
                            .val(
                                g.fecha
                            );


                        $('#observacion')
                            .val(
                                g.observacion
                                ?? ''
                            );


                        $('#tituloModalGasto')
                            .text(
                                'Editar gasto'
                            );


                        modalGasto.show();

                    }

            });

        }
    );


    // ========================================================
    // ACTIVAR / DESACTIVAR
    // ========================================================

    $('#tablaGastos').on(
        'click',
        '.btn-estado-gasto',
        function () {

            const id =
                Number(
                    $(this)
                        .data('id')
                );


            const activo =
                Number(
                    $(this)
                        .data('activo')
                );


            const activar =
                activo === 0;


            Swal.fire({

                icon:
                    'question',

                title:
                    activar
                        ? '¿Activar gasto?'
                        : '¿Desactivar gasto?',

                text:
                    activar
                        ? 'El gasto volverá a considerarse en los reportes.'
                        : 'El gasto dejará de considerarse en los reportes.',

                showCancelButton:
                    true,

                confirmButtonText:
                    activar
                        ? 'Sí, activar'
                        : 'Sí, desactivar',

                cancelButtonText:
                    'Cancelar'

            }).then(
                function (
                    result
                ) {

                    if (
                        !result.isConfirmed
                    ) {
                        return;
                    }


                    $.ajax({

                        url:
                            '<?= BASE_URL ?>ajax/gastos/cambiar_estado.php',

                        type:
                            'POST',

                        dataType:
                            'json',

                        data: {
                            id: id
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


                                tabla.ajax.reload(
                                    null,
                                    false
                                );


                                Swal.fire({

                                    icon:
                                        'success',

                                    title:
                                        'Actualizado',

                                    timer:
                                        1000,

                                    showConfirmButton:
                                        false

                                });

                            }

                    });

                }
            );
        }
    );

});
</script>

<?php
require_once __DIR__ . '/../../includes/footer.php';
?>