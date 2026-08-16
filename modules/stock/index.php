<?php

$pageTitle = 'Stock';

require_once __DIR__ . '/../../includes/header.php';
require_once __DIR__ . '/../../includes/navbar.php';

?>

<main class="container-fluid py-4">

    <div class="mb-4">

        <h1 class="h3 fw-bold mb-1">
            Stock
        </h1>

        <p class="text-muted mb-0">
            Inventario actual y movimientos de productos.
        </p>

    </div>


    <div class="card border-0 shadow-sm">

        <div class="card-body">

            <div class="table-responsive">

                <table
                    id="tablaStock"
                    class="table table-hover align-middle w-100"
                >

                    <thead>
                    <tr>
                        <th>Producto</th>
                        <th>Categoría</th>
                        <th>Stock actual</th>
                        <th>Stock mínimo</th>
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
     MODAL AJUSTAR STOCK
========================================================= -->

<div
    class="modal fade"
    id="modalAjusteStock"
    tabindex="-1"
    aria-hidden="true"
>

    <div class="modal-dialog modal-dialog-centered">

        <div class="modal-content">

            <form id="formAjusteStock">

                <div class="modal-header">

                    <h5 class="modal-title fw-bold">
                        Ajustar stock
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
                        name="producto_id"
                        id="ajuste_producto_id"
                    >


                    <div class="alert alert-light border">

                        <div class="small text-muted">
                            Producto
                        </div>

                        <div
                            class="fw-semibold"
                            id="ajuste_producto_nombre"
                        ></div>


                        <div class="small text-muted mt-3">
                            Stock actual
                        </div>

                        <div
                            class="fs-3 fw-bold"
                            id="ajuste_stock_actual"
                        >
                            0
                        </div>

                    </div>


                    <div class="mb-3">

                        <label class="form-label">
                            Tipo de ajuste
                        </label>

                        <div
                            class="btn-group w-100"
                            role="group"
                        >

                            <input
                                type="radio"
                                class="btn-check"
                                name="tipo_ajuste"
                                id="ajusteEntrada"
                                value="ENTRADA"
                                checked
                            >

                            <label
                                class="btn btn-outline-success"
                                for="ajusteEntrada"
                            >
                                <i class="fa-solid fa-plus me-1"></i>
                                Entrada
                            </label>


                            <input
                                type="radio"
                                class="btn-check"
                                name="tipo_ajuste"
                                id="ajusteSalida"
                                value="SALIDA"
                            >

                            <label
                                class="btn btn-outline-danger"
                                for="ajusteSalida"
                            >
                                <i class="fa-solid fa-minus me-1"></i>
                                Salida
                            </label>

                        </div>

                    </div>


                    <div class="mb-3">

                        <label class="form-label">
                            Cantidad
                        </label>

                        <input
                            type="number"
                            class="form-control form-control-lg"
                            name="cantidad"
                            id="ajuste_cantidad"
                            min="0.001"
                            step="0.001"
                            required
                        >

                    </div>


                    <div class="mb-3">

                        <label class="form-label">
                            Motivo
                        </label>

                        <select
                            class="form-select"
                            name="motivo"
                            id="ajuste_motivo"
                            required
                        >

                            <option value="">
                                Seleccione
                            </option>

                            <option value="CONTEO_FISICO">
                                Corrección por conteo físico
                            </option>

                            <option value="ROTURA">
                                Rotura
                            </option>

                            <option value="PERDIDA">
                                Pérdida
                            </option>

                            <option value="ERROR_REGISTRO">
                                Error de registro
                            </option>

                            <option value="OTRO">
                                Otro
                            </option>

                        </select>

                    </div>


                    <div class="mb-0">

                        <label class="form-label">
                            Descripción
                        </label>

                        <textarea
                            class="form-control"
                            name="descripcion"
                            id="ajuste_descripcion"
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
                        class="btn btn-dark"
                        id="btnGuardarAjuste"
                    >
                        <i class="fa-solid fa-floppy-disk me-2"></i>
                        Registrar ajuste
                    </button>

                </div>

            </form>

        </div>

    </div>

</div>


<!-- =========================================================
     MODAL KARDEX
========================================================= -->

<div
    class="modal fade"
    id="modalKardex"
    tabindex="-1"
    aria-hidden="true"
>

    <div
        class="modal-dialog modal-xl modal-dialog-centered modal-dialog-scrollable modal-fullscreen-md-down"
    >

        <div class="modal-content">

            <div class="modal-header">

                <div>

                    <h5 class="modal-title fw-bold">
                        Kardex
                    </h5>

                    <small
                        class="text-muted"
                        id="kardexProductoNombre"
                    ></small>

                </div>

                <button
                    type="button"
                    class="btn-close"
                    data-bs-dismiss="modal"
                ></button>

            </div>


            <div class="modal-body">

                <div class="table-responsive">

                    <table
                        id="tablaKardex"
                        class="table table-sm table-hover align-middle w-100"
                    >

                        <thead>
                        <tr>
                            <th>Fecha</th>
                            <th>Movimiento</th>
                            <th>Cantidad</th>
                            <th>Anterior</th>
                            <th>Nuevo</th>
                            <th>Referencia</th>
                            <th>Descripción</th>
                        </tr>
                        </thead>

                    </table>

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

    const modalAjuste =
        new bootstrap.Modal(
            document.getElementById('modalAjusteStock')
        );


    const modalKardex =
        new bootstrap.Modal(
            document.getElementById('modalKardex')
        );


    let tablaKardex = null;

    let productoKardexActual = null;


    // ========================================================
    // DATATABLE STOCK
    // ========================================================

    const tablaStock =
        $('#tablaStock').DataTable({

            processing: true,
            serverSide: true,

            ajax: {

                url:
                    '<?= BASE_URL ?>ajax/stock/listar.php',

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
                    data: 'producto'
                },

                {
                    data: 'categoria'
                },

                {
                    data: 'stock_actual'
                },

                {
                    data: 'stock_minimo'
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
    // ABRIR AJUSTE
    // ========================================================

    $('#tablaStock').on(
        'click',
        '.btn-ajustar-stock',
        function () {

            const id =
                Number(
                    $(this).data('id')
                );


            $.ajax({

                url:
                    '<?= BASE_URL ?>ajax/stock/obtener_producto.php',

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


                    const p =
                        response.data;


                    $('#formAjusteStock')[0]
                        .reset();


                    $('#ajuste_producto_id')
                        .val(
                            p.id
                        );


                    $('#ajuste_producto_nombre')
                        .text(
                            p.nombre
                            +
                            (
                                p.presentacion
                                    ? ' - ' + p.presentacion
                                    : ''
                            )
                        );


                    $('#ajuste_stock_actual')
                        .text(
                            Number(
                                p.stock_actual
                            )
                        );


                    $('#ajusteEntrada')
                        .prop(
                            'checked',
                            true
                        );


                    modalAjuste.show();


                    setTimeout(function () {

                        $('#ajuste_cantidad')
                            .trigger('focus');

                    }, 200);

                }

            });

        }
    );


    // ========================================================
    // GUARDAR AJUSTE
    // ========================================================

    $('#formAjusteStock').on(
        'submit',
        function (e) {

            e.preventDefault();


            const $btn =
                $('#btnGuardarAjuste');


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
                    '<?= BASE_URL ?>ajax/stock/ajustar.php',

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
                            title: 'No se pudo ajustar',
                            text: response.message
                        });

                        return;
                    }


                    modalAjuste.hide();


                    tablaStock.ajax.reload(
                        null,
                        false
                    );


                    Swal.fire({
                        icon: 'success',
                        title: 'Stock actualizado',
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
                            'No se pudo registrar el ajuste.'
                    });

                },

                complete: function () {

                    $btn
                        .prop(
                            'disabled',
                            false
                        )
                        .html(`
                            <i class="fa-solid fa-floppy-disk me-2"></i>
                            Registrar ajuste
                        `);
                }

            });

        }
    );


    // ========================================================
    // KARDEX
    // ========================================================

    $('#tablaStock').on(
        'click',
        '.btn-kardex',
        function () {

            productoKardexActual =
                Number(
                    $(this).data('id')
                );


            const nombre =
                $(this).data('nombre');


            $('#kardexProductoNombre')
                .text(
                    nombre
                );


            if (tablaKardex) {

                tablaKardex.destroy();
            }


            tablaKardex =
                $('#tablaKardex').DataTable({

                    processing: true,
                    serverSide: true,

                    ajax: {

                        url:
                            '<?= BASE_URL ?>ajax/stock/kardex.php',

                        type:
                            'POST',

                        data: function (d) {

                            d.producto_id =
                                productoKardexActual;
                        }
                    },

                    order: [
                        [0, 'desc']
                    ],

                    pageLength:
                        10,

                    columns: [

                        {
                            data: 'fecha'
                        },

                        {
                            data: 'movimiento'
                        },

                        {
                            data: 'cantidad'
                        },

                        {
                            data: 'stock_anterior'
                        },

                        {
                            data: 'stock_nuevo'
                        },

                        {
                            data: 'referencia'
                        },

                        {
                            data: 'descripcion'
                        }

                    ],

                    language: {

                        url:
                            'https://cdn.datatables.net/plug-ins/2.3.3/i18n/es-ES.json'
                    }

                });


            modalKardex.show();
        }
    );

});
</script>

<?php
require_once __DIR__ . '/../../includes/footer.php';
?>