<?php

$pageTitle = 'Productos';

require_once __DIR__ . '/../../includes/header.php';
require_once __DIR__ . '/../../includes/navbar.php';

?>

<main class="container-fluid py-4">

    <div class="d-flex flex-column flex-md-row justify-content-between align-items-md-center gap-3 mb-4">

        <div>
            <h1 class="h3 fw-bold mb-1">
                Productos
            </h1>

            <p class="text-muted mb-0">
                Administra productos, precios, stock y catálogo.
            </p>
        </div>

        <button
            type="button"
            class="btn btn-dark"
            id="btnNuevoProducto"
        >
            <i class="fa-solid fa-plus me-2"></i>
            Nuevo producto
        </button>

    </div>


    <div class="card border-0 shadow-sm">

        <div class="card-body">

            <div class="table-responsive">

                <table
                    id="tablaProductos"
                    class="table table-hover align-middle w-100"
                >

                    <thead>
                    <tr>
                        <th>ID</th>
                        <th>Producto</th>
                        <th>Categoría</th>
                        <th>Precio</th>
                        <th>Stock</th>
                        <th>Catálogo</th>
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
     MODAL PRODUCTO
========================================================= -->

<div
    class="modal fade"
    id="modalProducto"
    tabindex="-1"
    aria-hidden="true"
>

    <div class="modal-dialog modal-xl modal-dialog-centered modal-dialog-scrollable">

        <div class="modal-content">

            <form id="formProducto">

                <div class="modal-header">

                    <h5 class="modal-title" id="tituloModalProducto">
                        Nuevo producto
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
                        id="productoId"
                    >


                    <div class="row g-3">

                        <!-- Nombre -->
                        <div class="col-12 col-md-8">

                            <label class="form-label">
                                Nombre
                                <span class="text-danger">*</span>
                            </label>

                            <input
                                type="text"
                                class="form-control"
                                name="nombre"
                                id="nombre"
                                maxlength="180"
                                required
                            >

                        </div>


                        <!-- Categoría -->
                        <div class="col-12 col-md-4">

                            <label class="form-label">
                                Categoría
                            </label>

                            <select
                                class="form-select"
                                name="categoria_id"
                                id="categoria_id"
                            >
                                <option value="">
                                    Sin categoría
                                </option>
                            </select>

                        </div>


                        <!-- Presentación -->
                        <div class="col-12 col-md-4">

                            <label class="form-label">
                                Presentación
                            </label>

                            <input
                                type="text"
                                class="form-control"
                                name="presentacion"
                                id="presentacion"
                                placeholder="Ej. 630 ml"
                            >

                        </div>


                        <!-- Tipo -->
                        <div class="col-12 col-md-4">

                            <label class="form-label">
                                Tipo de producto
                            </label>

                            <select
                                class="form-select"
                                name="tipo_producto"
                                id="tipo_producto"
                            >
                                <option value="SIMPLE">
                                    Producto
                                </option>

                                <option value="COMBO">
                                    Combo
                                </option>
                            </select>

                        </div>


                        <!-- Maneja stock -->
                        <div class="col-12 col-md-4">

                            <label class="form-label d-block">
                                Inventario
                            </label>

                            <div class="form-check form-switch mt-2">

                                <input
                                    class="form-check-input"
                                    type="checkbox"
                                    name="maneja_stock"
                                    id="maneja_stock"
                                    value="1"
                                    checked
                                >

                                <label
                                    class="form-check-label"
                                    for="maneja_stock"
                                >
                                    Maneja stock
                                </label>

                            </div>

                        </div>


                        <!-- Descripción -->
                        <div class="col-12">

                            <label class="form-label">
                                Descripción
                            </label>

                            <textarea
                                class="form-control"
                                name="descripcion"
                                id="descripcion"
                                rows="3"
                            ></textarea>

                        </div>


                        <div class="col-12">
                            <hr>
                            <h6 class="fw-bold mb-0">
                                Precios
                            </h6>
                        </div>


                        <!-- Costo -->
                        <div class="col-12 col-md-4">

                            <label class="form-label">
                                Costo referencia
                            </label>

                            <div class="input-group">

                                <span class="input-group-text">
                                    S/
                                </span>

                                <input
                                    type="number"
                                    class="form-control"
                                    name="costo_referencia"
                                    id="costo_referencia"
                                    min="0"
                                    step="0.01"
                                    value="0"
                                >

                            </div>

                        </div>


                        <!-- Precio regular -->
                        <div class="col-12 col-md-4">

                            <label class="form-label">
                                Precio regular
                            </label>

                            <div class="input-group">

                                <span class="input-group-text">
                                    S/
                                </span>

                                <input
                                    type="number"
                                    class="form-control"
                                    name="precio_regular"
                                    id="precio_regular"
                                    min="0"
                                    step="0.01"
                                    value="0"
                                >

                            </div>

                            <div class="form-text">
                                Precio referencial mostrado al cliente.
                            </div>

                        </div>


                        <!-- Precio venta -->
                        <div class="col-12 col-md-4">

                            <label class="form-label">
                                Precio de venta
                            </label>

                            <div class="input-group">

                                <span class="input-group-text">
                                    S/
                                </span>

                                <input
                                    type="number"
                                    class="form-control"
                                    name="precio_venta"
                                    id="precio_venta"
                                    min="0"
                                    step="0.01"
                                    value="0"
                                >

                            </div>

                        </div>


                        <!-- STOCK -->
                        <div
                            class="col-12"
                            id="bloqueStock"
                        >

                            <hr>

                            <h6 class="fw-bold">
                                Stock
                            </h6>

                            <div class="row g-3">

                                <div class="col-12 col-md-6">

                                    <label class="form-label">
                                        Stock inicial / actual
                                    </label>

                                    <input
                                        type="number"
                                        class="form-control"
                                        name="stock_actual"
                                        id="stock_actual"
                                        min="0"
                                        value="0"
                                        readonly
                                    >
                                    <div class="form-text">
                                        El stock se modifica desde Compras, Ventas o Ajustes de inventario.
                                    </div>

                                </div>

                                <div class="col-12 col-md-6">

                                    <label class="form-label">
                                        Stock mínimo
                                    </label>

                                    <input
                                        type="number"
                                        class="form-control"
                                        name="stock_minimo"
                                        id="stock_minimo"
                                        min="0"
                                        step="0.001"
                                        value="0"
                                    >

                                </div>

                            </div>

                        </div>


                        <div class="col-12">

                            <div class="accordion" id="accordionOpcionesProducto">

                                <!-- ENVASES -->
                                <div class="accordion-item">

                                    <h2 class="accordion-header">

                                        <button
                                            class="accordion-button collapsed"
                                            type="button"
                                            data-bs-toggle="collapse"
                                            data-bs-target="#collapseEnvases"
                                        >
                                            <i class="fa-solid fa-bottle-water me-2"></i>
                                            Envases retornables
                                        </button>

                                    </h2>

                                    <div
                                        id="collapseEnvases"
                                        class="accordion-collapse collapse"
                                        data-bs-parent="#accordionOpcionesProducto"
                                    >

                                        <div class="accordion-body">

                                            <div class="form-check form-switch mb-3">

                                                <input
                                                    class="form-check-input"
                                                    type="checkbox"
                                                    name="controla_envase"
                                                    id="controla_envase"
                                                    value="1"
                                                >

                                                <label
                                                    class="form-check-label"
                                                    for="controla_envase"
                                                >
                                                    Este producto requiere envases
                                                </label>

                                            </div>


                                            <div
                                                id="bloqueEnvase"
                                                style="display:none;"
                                            >

                                                <div class="row g-3">

                                                    <div class="col-12 col-md-8">

                                                        <label class="form-label">
                                                            Tipo de envase
                                                        </label>

                                                        <select
                                                            class="form-select"
                                                            name="tipo_envase_id"
                                                            id="tipo_envase_id"
                                                        >

                                                            <option value="">
                                                                Seleccione
                                                            </option>

                                                        </select>

                                                    </div>


                                                    <div class="col-12 col-md-4">

                                                        <label class="form-label">
                                                            Envases por unidad
                                                        </label>

                                                        <input
                                                            type="number"
                                                            class="form-control"
                                                            name="envases_por_unidad"
                                                            id="envases_por_unidad"
                                                            min="0"
                                                            step="1"
                                                            value="1"
                                                        >

                                                    </div>

                                                </div>

                                            </div>

                                        </div>

                                    </div>

                                </div>


                                <!-- CATÁLOGO -->
                                <div class="accordion-item">

                                    <h2 class="accordion-header">

                                        <button
                                            class="accordion-button collapsed"
                                            type="button"
                                            data-bs-toggle="collapse"
                                            data-bs-target="#collapseCatalogo"
                                        >
                                            <i class="fa-solid fa-store me-2"></i>
                                            Catálogo virtual
                                        </button>

                                    </h2>

                                    <div
                                        id="collapseCatalogo"
                                        class="accordion-collapse collapse"
                                        data-bs-parent="#accordionOpcionesProducto"
                                    >

                                        <div class="accordion-body">

                                            <div class="row g-3">

                                                <div class="col-12 col-md-6">

                                                    <div class="form-check form-switch">

                                                        <input
                                                            class="form-check-input"
                                                            type="checkbox"
                                                            name="publicar_catalogo"
                                                            id="publicar_catalogo"
                                                            value="1"
                                                            checked
                                                        >

                                                        <label
                                                            class="form-check-label"
                                                            for="publicar_catalogo"
                                                        >
                                                            Mostrar en catálogo
                                                        </label>

                                                    </div>

                                                </div>


                                                <div class="col-12 col-md-6">

                                                    <div class="form-check form-switch">

                                                        <input
                                                            class="form-check-input"
                                                            type="checkbox"
                                                            name="destacado_catalogo"
                                                            id="destacado_catalogo"
                                                            value="1"
                                                        >

                                                        <label
                                                            class="form-check-label"
                                                            for="destacado_catalogo"
                                                        >
                                                            Producto destacado
                                                        </label>

                                                    </div>

                                                </div>


                                                <div class="col-12">

                                                    <label class="form-label">
                                                        URL de imagen
                                                    </label>

                                                    <input
                                                        type="text"
                                                        class="form-control"
                                                        name="imagen_url"
                                                        id="imagen_url"
                                                    >

                                                </div>

                                            </div>

                                        </div>

                                    </div>

                                </div>

                            </div>

                        </div>

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
                        id="btnGuardarProducto"
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

    const modalProducto = new bootstrap.Modal(
        document.getElementById('modalProducto')
    );


    function cerrarModalProducto() {

        const activeElement = document.activeElement;

        if (
            activeElement
            && document
                .getElementById('modalProducto')
                .contains(activeElement)
        ) {
            activeElement.blur();
        }

        modalProducto.hide();
    }


    // ========================================================
    // SELECT2
    // ========================================================

    $('#categoria_id').select2({
        dropdownParent: $('#modalProducto'),
        width: '100%',
        placeholder: 'Seleccione categoría'
    });

    $('#tipo_envase_id').select2({
        dropdownParent: $('#modalProducto'),
        width: '100%',
        placeholder: 'Seleccione tipo de envase'
    });


    // ========================================================
    // CARGAR CATEGORÍAS
    // ========================================================

    function cargarCategorias() {

        $.ajax({
            url: '<?= BASE_URL ?>ajax/productos/categorias.php',
            type: 'GET',
            dataType: 'json',

            success: function (response) {

                const $select = $('#categoria_id');

                $select.empty();

                $select.append(
                    '<option value="">Sin categoría</option>'
                );

                response.forEach(function (item) {

                    $select.append(
                        new Option(
                            item.text,
                            item.id,
                            false,
                            false
                        )
                    );

                });

            }
        });

    }


    // ========================================================
    // CARGAR ENVASES
    // ========================================================

    function cargarEnvases() {

        $.ajax({
            url: '<?= BASE_URL ?>ajax/productos/envases.php',
            type: 'GET',
            dataType: 'json',

            success: function (response) {

                const $select = $('#tipo_envase_id');

                $select.empty();

                $select.append(
                    '<option value="">Seleccione</option>'
                );

                response.forEach(function (item) {

                    $select.append(
                        new Option(
                            item.text,
                            item.id,
                            false,
                            false
                        )
                    );

                });

            }
        });

    }


    cargarCategorias();
    cargarEnvases();


    // ========================================================
    // DATATABLE
    // ========================================================

    const tabla = $('#tablaProductos').DataTable({

        processing: true,
        serverSide: true,

        ajax: {
            url: '<?= BASE_URL ?>ajax/productos/listar.php',
            type: 'POST'
        },

        order: [[0, 'desc']],

        pageLength: 10,

        columns: [
            { data: 'id' },
            { data: 'producto' },
            { data: 'categoria' },
            { data: 'precio' },
            { data: 'stock' },
            { data: 'catalogo' },
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


    // ========================================================
    // LIMPIAR FORMULARIO
    // ========================================================

    function limpiarFormulario() {

        $('#formProducto')[0].reset();

        $('#productoId').val('');

        $('#categoria_id')
            .val('')
            .trigger('change');

        $('#tipo_envase_id')
            .val('')
            .trigger('change');

        $('#maneja_stock').prop('checked', true);
        $('#publicar_catalogo').prop('checked', true);
        $('#destacado_catalogo').prop('checked', false);
        $('#controla_envase').prop('checked', false);

        $('#bloqueEnvase').hide();
        $('#bloqueStock').show();

        $('#tipo_producto').val('SIMPLE');

        $('#costo_referencia').val('0');
        $('#precio_regular').val('0');
        $('#precio_venta').val('0');
        $('#stock_actual').val('0');
        $('#stock_minimo').val('0');
        $('#envases_por_unidad').val('1');

    }


    // ========================================================
    // NUEVO PRODUCTO
    // ========================================================

    $('#btnNuevoProducto').on('click', function () {

        limpiarFormulario();

        $('#tituloModalProducto')
            .text('Nuevo producto');

        modalProducto.show();

    });


    // ========================================================
    // CONTROL DE ENVASE
    // ========================================================

    $('#controla_envase').on('change', function () {

        if (this.checked) {

            $('#bloqueEnvase').slideDown(150);

        } else {

            $('#bloqueEnvase').slideUp(150);

            $('#tipo_envase_id')
                .val('')
                .trigger('change');

        }

    });


    // ========================================================
    // MANEJO DE STOCK
    // ========================================================

    $('#maneja_stock').on('change', function () {

        if (this.checked) {
            $('#bloqueStock').slideDown(150);
        } else {
            $('#bloqueStock').slideUp(150);
        }

    });


    // ========================================================
    // SI ES COMBO NO MANEJA STOCK PROPIO
    // ========================================================

    $('#tipo_producto').on('change', function () {

        if ($(this).val() === 'COMBO') {

            $('#maneja_stock')
                .prop('checked', false)
                .trigger('change');

        }

    });


    // ========================================================
    // GUARDAR PRODUCTO
    // ========================================================

    $('#formProducto').on('submit', function (e) {

        e.preventDefault();

        const $btn = $('#btnGuardarProducto');

        $btn.prop('disabled', true);

        $btn.html(`
            <span class="spinner-border spinner-border-sm me-2"></span>
            Guardando...
        `);


        $.ajax({

            url: '<?= BASE_URL ?>ajax/productos/guardar.php',
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

                cerrarModalProducto();

                tabla.ajax.reload(null, false);

                Swal.fire({
                    icon: 'success',
                    title: 'Producto guardado',
                    text: response.message,
                    timer: 1500,
                    showConfirmButton: false
                });

            },

            error: function () {

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
    // EDITAR
    // ========================================================

    $('#tablaProductos').on(
        'click',
        '.btn-editar-producto',
        function () {

            const id = $(this).data('id');

            limpiarFormulario();


            $.ajax({

                url: '<?= BASE_URL ?>ajax/productos/obtener.php',
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

                    const p = response.data;


                    $('#productoId').val(p.id);

                    $('#nombre').val(p.nombre);

                    $('#descripcion').val(
                        p.descripcion ?? ''
                    );

                    $('#presentacion').val(
                        p.presentacion ?? ''
                    );

                    $('#categoria_id')
                        .val(p.categoria_id ?? '')
                        .trigger('change');

                    $('#tipo_producto')
                        .val(p.tipo_producto);

                    $('#costo_referencia')
                        .val(p.costo_referencia);

                    $('#precio_regular')
                        .val(p.precio_regular);

                    $('#precio_venta')
                        .val(p.precio_venta);

                    $('#stock_actual')
                        .val(p.stock_actual);

                    $('#stock_minimo')
                        .val(p.stock_minimo);

                    $('#maneja_stock')
                        .prop(
                            'checked',
                            Number(p.maneja_stock) === 1
                        )
                        .trigger('change');

                    $('#controla_envase')
                        .prop(
                            'checked',
                            Number(p.controla_envase) === 1
                        )
                        .trigger('change');

                    $('#tipo_envase_id')
                        .val(p.tipo_envase_id ?? '')
                        .trigger('change');

                    $('#envases_por_unidad')
                        .val(p.envases_por_unidad);

                    $('#publicar_catalogo')
                        .prop(
                            'checked',
                            Number(p.publicar_catalogo) === 1
                        );

                    $('#destacado_catalogo')
                        .prop(
                            'checked',
                            Number(p.destacado_catalogo) === 1
                        );

                    $('#imagen_url')
                        .val(p.imagen_url ?? '');

                    $('#tituloModalProducto')
                        .text('Editar producto');

                    modalProducto.show();

                }

            });

        }
    );


    // ========================================================
    // ACTIVAR / DESACTIVAR
    // ========================================================

    $('#tablaProductos').on(
        'click',
        '.btn-estado-producto',
        function () {

            const id = $(this).data('id');
            const activo = $(this).data('activo');

            const accion = activo == 1
                ? 'desactivar'
                : 'activar';


            Swal.fire({

                icon: 'question',

                title:
                    accion === 'activar'
                        ? '¿Activar producto?'
                        : '¿Desactivar producto?',

                text:
                    accion === 'desactivar'
                        ? 'El producto dejará de estar disponible para nuevas operaciones.'
                        : 'El producto volverá a estar disponible.',

                showCancelButton: true,

                confirmButtonText:
                    accion === 'activar'
                        ? 'Sí, activar'
                        : 'Sí, desactivar',

                cancelButtonText: 'Cancelar'

            }).then(function (result) {

                if (!result.isConfirmed) {
                    return;
                }


                $.ajax({

                    url: '<?= BASE_URL ?>ajax/productos/cambiar_estado.php',

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

                        tabla.ajax.reload(null, false);

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

<?php require_once __DIR__ . '/../../includes/footer.php'; ?>