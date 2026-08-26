<?php

$pageTitle = 'Promoción';

require_once __DIR__ . '/../../includes/header.php';
require_once __DIR__ . '/../../includes/navbar.php';

$promocionId =
    isset($_GET['id'])
        ? (int) $_GET['id']
        : 0;

?>

<main class="container-fluid py-3 py-md-4">

    <div class="row justify-content-center">

        <div class="col-12 col-xl-10">

            <div class="d-flex justify-content-between align-items-center mb-3">

                <div>

                    <h1
                        class="h3 fw-bold mb-0"
                        id="tituloPagina"
                    >
                        Nueva promoción
                    </h1>

                    <small class="text-muted">
                        Configuración de descuentos y ofertas
                    </small>

                </div>

                <a
                    href="<?= BASE_URL ?>modules/promociones/index.php"
                    class="btn btn-outline-secondary"
                >
                    <i class="fa-solid fa-arrow-left me-1"></i>
                    Volver
                </a>

            </div>


            <!-- =================================================
                 DATOS GENERALES
            ================================================== -->

            <div class="card border-0 shadow-sm mb-3">

                <div class="card-body">

                    <input
                        type="hidden"
                        id="promocion_id"
                        value="<?= $promocionId ?>"
                    >


                    <div class="row g-3">

                        <div class="col-12 col-md-12">

                            <label class="form-label fw-semibold">
                                Nombre
                            </label>

                            <input
                                type="text"
                                class="form-control"
                                id="nombre"
                                maxlength="150"
                                placeholder="Ej. Jueves de Patas"
                            >

                        </div>


                        <div class="col-12">

                            <label class="form-label">
                                Descripción
                            </label>

                            <textarea
                                class="form-control"
                                id="descripcion"
                                rows="2"
                                placeholder="Ej. 3 cervezas Pilsen por S/20"
                            ></textarea>

                        </div>


                        <div class="col-12 col-md-6">

                            <label class="form-label fw-semibold">
                                Fecha inicio
                            </label>

                            <input
                                type="date"
                                class="form-control"
                                id="fecha_inicio"
                            >

                        </div>


                        <div class="col-12 col-md-6">

                            <label class="form-label fw-semibold">
                                Fecha fin
                            </label>

                            <input
                                type="date"
                                class="form-control"
                                id="fecha_fin"
                            >

                        </div>


                        <div class="col-12">

                            <div class="form-check form-switch">

                                <input
                                    class="form-check-input"
                                    type="checkbox"
                                    id="acumulable"
                                >

                                <label
                                    class="form-check-label"
                                    for="acumulable"
                                >
                                    Permitir acumulación con otras promociones
                                </label>

                            </div>

                            <div class="form-text">
                                Normalmente debe quedar desactivado.
                            </div>

                        </div>

                    </div>

                </div>

            </div>


            <!-- =================================================
                 DÍAS
            ================================================== -->

            <div class="card border-0 shadow-sm mb-3">

                <div class="card-body">

                    <div class="d-flex justify-content-between align-items-center mb-3">

                        <div>
                            <h5 class="fw-bold mb-0">
                                Días de aplicación
                            </h5>

                            <small class="text-muted">
                                Si no seleccionas ninguno, aplica todos los días.
                            </small>
                        </div>

                        <button
                            type="button"
                            class="btn btn-outline-secondary btn-sm"
                            id="btnTodosDias"
                        >
                            Todos los días
                        </button>

                    </div>


                    <div class="row g-2">

                        <?php

                        $dias = [
                            1 => 'Lunes',
                            2 => 'Martes',
                            3 => 'Miércoles',
                            4 => 'Jueves',
                            5 => 'Viernes',
                            6 => 'Sábado',
                            7 => 'Domingo'
                        ];

                        foreach ($dias as $numero => $nombreDia):
                        ?>

                            <div class="col-6 col-md">

                                <input
                                    type="checkbox"
                                    class="btn-check dia-promocion"
                                    id="dia<?= $numero ?>"
                                    value="<?= $numero ?>"
                                >

                                <label
                                    class="btn btn-outline-dark w-100"
                                    for="dia<?= $numero ?>"
                                >
                                    <?= $nombreDia ?>
                                </label>

                            </div>

                        <?php endforeach; ?>

                    </div>

                </div>

            </div>


            <!-- =================================================
                 REGLAS
            ================================================== -->

            <div class="card border-0 shadow-sm mb-3">

                <div class="card-header bg-white border-0 pt-3">

                    <div class="d-flex justify-content-between align-items-center">

                        <div>

                            <h5 class="fw-bold mb-0">
                                Productos y beneficios
                            </h5>

                            <small class="text-muted">
                                Una promoción puede afectar varios productos.
                            </small>

                        </div>


                        <button
                            type="button"
                            class="btn btn-dark btn-sm"
                            id="btnAgregarRegla"
                        >
                            <i class="fa-solid fa-plus me-1"></i>
                            Agregar producto
                        </button>

                    </div>

                </div>


                <div class="card-body">

                    <div id="listaReglas"></div>


                    <div
                        id="sinReglas"
                        class="text-center text-muted py-4"
                    >
                        <i class="fa-solid fa-tags fa-2x mb-2"></i>

                        <div>
                            Agrega al menos un producto.
                        </div>
                    </div>

                </div>

            </div>


            <!-- =================================================
                 GUARDAR
            ================================================== -->

            <div class="card border-0 shadow-sm">

                <div class="card-body d-flex justify-content-end gap-2">

                    <a
                        href="<?= BASE_URL ?>modules/promociones/index.php"
                        class="btn btn-light"
                    >
                        Cancelar
                    </a>


                    <button
                        type="button"
                        class="btn btn-dark"
                        id="btnGuardarPromocion"
                    >
                        <i class="fa-solid fa-floppy-disk me-2"></i>
                        Guardar promoción
                    </button>

                </div>

            </div>

        </div>

    </div>

</main>


<script>
document.addEventListener('DOMContentLoaded', function () {

    const promocionId =
        Number(
            $('#promocion_id').val()
        );


    let contadorReglas = 0;


    // ========================================================
    // DÍAS
    // ========================================================

    $('#btnTodosDias').on(
        'click',
        function () {

            $('.dia-promocion')
                .prop(
                    'checked',
                    false
                );
        }
    );


    // ========================================================
    // AGREGAR REGLA
    // ========================================================

    $('#btnAgregarRegla').on(
        'click',
        function () {

            agregarRegla();
        }
    );


    function agregarRegla(data = null) {

        contadorReglas++;


        const id =
            contadorReglas;


        const html = `

            <div
                class="border rounded p-3 mb-3 regla-promocion"
                data-regla-id="${id}"
            >

                <div class="d-flex justify-content-between align-items-center mb-3">

                    <strong>
                        Producto
                    </strong>

                    <button
                        type="button"
                        class="btn btn-outline-danger btn-sm btn-eliminar-regla"
                    >
                        <i class="fa-solid fa-trash"></i>
                    </button>

                </div>


                <div class="row g-3">

                    <!-- PRODUCTO -->
                    <div class="col-12 col-lg-6">

                        <label class="form-label">
                            Producto
                        </label>

                        <select
                            class="form-select select-producto-promocion"
                            data-regla-id="${id}"
                        >
                        </select>

                    </div>


                    <!-- TIPO -->
                    <div class="col-12 col-lg-6">

                        <label class="form-label">
                            Tipo de beneficio
                        </label>

                        <select
                            class="form-select tipo-beneficio"
                            data-regla-id="${id}"
                        >

                            <option value="PRECIO_ESPECIAL">
                                Precio especial
                            </option>

                            <option value="PORCENTAJE">
                                Descuento porcentual
                            </option>

                            <option value="DESCUENTO_FIJO">
                                Descuento fijo
                            </option>

                            <option value="UNIDAD_N_PRECIO_ESPECIAL">
                                Cada N unidades: una a precio especial
                            </option>

                            <option value="UNIDAD_N_PORCENTAJE">
                                Cada N unidades: una con descuento %
                            </option>

                            <option value="CANTIDAD_POR_PRECIO">
                                N unidades por precio total
                            </option>

                        </select>

                    </div>


                    <!-- CANTIDAD MÍNIMA -->
                    <div class="col-6 col-lg-3">

                        <label class="form-label">
                            Cantidad mínima
                        </label>

                        <input
                            type="number"
                            class="form-control cantidad-minima"
                            value="1"
                            min="1"
                            step="1"
                        >

                    </div>


                    <!-- UNIDAD / GRUPO -->
                    <div
                        class="col-6 col-lg-3 campo-unidad"
                        style="display:none;"
                    >

                        <label
                            class="form-label label-unidad"
                        >
                            Unidad beneficiada
                        </label>

                        <input
                            type="number"
                            class="form-control unidad-beneficiada"
                            min="1"
                            step="1"
                        >

                    </div>


                    <!-- PRECIO -->
                    <div
                        class="col-6 col-lg-3 campo-precio"
                        style="display:none;"
                    >

                        <label
                            class="form-label label-precio"
                        >
                            Precio promocional
                        </label>

                        <div class="input-group">

                            <span class="input-group-text">
                                S/
                            </span>

                            <input
                                type="number"
                                class="form-control precio-promocional"
                                min="0"
                                step="0.01"
                            >

                        </div>

                    </div>


                    <!-- PORCENTAJE -->
                    <div
                        class="col-6 col-lg-3 campo-porcentaje"
                        style="display:none;"
                    >

                        <label class="form-label">
                            Descuento %
                        </label>

                        <div class="input-group">

                            <input
                                type="number"
                                class="form-control porcentaje-descuento"
                                min="0"
                                max="100"
                                step="0.01"
                            >

                            <span class="input-group-text">
                                %
                            </span>

                        </div>

                    </div>


                    <!-- DESCUENTO FIJO -->
                    <div
                        class="col-6 col-lg-3 campo-descuento"
                        style="display:none;"
                    >

                        <label class="form-label">
                            Descuento
                        </label>

                        <div class="input-group">

                            <span class="input-group-text">
                                S/
                            </span>

                            <input
                                type="number"
                                class="form-control monto-descuento"
                                min="0"
                                step="0.01"
                            >

                        </div>

                    </div>


                    <!-- LÍMITE -->
                    <div class="col-6 col-lg-3">

                        <label class="form-label">
                            Máx. aplicaciones
                        </label>

                        <input
                            type="number"
                            class="form-control max-aplicaciones"
                            min="1"
                            step="1"
                            placeholder="Sin límite"
                        >

                    </div>

                </div>


                <div
                    class="alert alert-light border mt-3 mb-0 ayuda-regla"
                >
                </div>

            </div>

        `;


        $('#listaReglas')
            .append(html);


        $('#sinReglas')
            .hide();


        const $regla =
            $(
                `.regla-promocion[data-regla-id="${id}"]`
            );


        const $producto =
            $regla.find(
                '.select-producto-promocion'
            );


        $producto.select2({

            width:
                '100%',

            placeholder:
                'Buscar producto...',

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


        if (data) {

            if (
                data.producto_id
                &&
                data.producto_nombre
            ) {

                const texto =
                    data.producto_nombre
                    +
                    (
                        data.presentacion
                            ? ' - ' + data.presentacion
                            : ''
                    );


                const option =
                    new Option(
                        texto,
                        data.producto_id,
                        true,
                        true
                    );


                $producto
                    .append(option)
                    .trigger('change');

            }


            $regla
                .find('.tipo-beneficio')
                .val(
                    data.tipo_beneficio
                );


            $regla
                .find('.cantidad-minima')
                .val(
                    data.cantidad_minima
                );


            $regla
                .find('.unidad-beneficiada')
                .val(
                    data.unidad_beneficiada ?? ''
                );


            $regla
                .find('.precio-promocional')
                .val(
                    data.precio_promocional ?? ''
                );


            $regla
                .find('.porcentaje-descuento')
                .val(
                    data.porcentaje_descuento ?? ''
                );


            $regla
                .find('.monto-descuento')
                .val(
                    data.monto_descuento ?? ''
                );


            $regla
                .find('.max-aplicaciones')
                .val(
                    data.max_aplicaciones_por_venta ?? ''
                );

        }


        actualizarCamposRegla(
            $regla
        );
    }


    // ========================================================
    // CAMBIO DE TIPO
    // ========================================================

    $('#listaReglas').on(
        'change',
        '.tipo-beneficio',
        function () {

            actualizarCamposRegla(
                $(this)
                    .closest(
                        '.regla-promocion'
                    )
            );
        }
    );


    function actualizarCamposRegla($regla) {

        const tipo =
            $regla
                .find('.tipo-beneficio')
                .val();


        $regla
            .find(
                '.campo-unidad, .campo-precio, .campo-porcentaje, .campo-descuento'
            )
            .hide();


        let ayuda = '';


        switch (tipo) {

            case 'PRECIO_ESPECIAL':

                $regla
                    .find('.campo-precio')
                    .show();


                ayuda =
                    'Todas las unidades se venden al precio promocional.';

                break;


            case 'PORCENTAJE':

                $regla
                    .find('.campo-porcentaje')
                    .show();


                ayuda =
                    'Aplica el porcentaje de descuento a las unidades compradas.';

                break;


            case 'DESCUENTO_FIJO':

                $regla
                    .find('.campo-descuento')
                    .show();


                ayuda =
                    'Resta un monto fijo al subtotal del producto.';

                break;


            case 'UNIDAD_N_PRECIO_ESPECIAL':

                $regla
                    .find(
                        '.campo-unidad, .campo-precio'
                    )
                    .show();


                $regla
                    .find('.label-unidad')
                    .text(
                        'Cada N unidades'
                    );


                $regla
                    .find('.label-precio')
                    .text(
                        'Precio de la unidad promocionada'
                    );


                ayuda =
                    'Ejemplo: cada segunda unidad a S/27 → N = 2.';

                break;


            case 'UNIDAD_N_PORCENTAJE':

                $regla
                    .find(
                        '.campo-unidad, .campo-porcentaje'
                    )
                    .show();


                $regla
                    .find('.label-unidad')
                    .text(
                        'Cada N unidades'
                    );


                ayuda =
                    'Ejemplo: cada segunda unidad con 20% de descuento → N = 2.';

                break;


            case 'CANTIDAD_POR_PRECIO':

                $regla
                    .find(
                        '.campo-unidad, .campo-precio'
                    )
                    .show();


                $regla
                    .find('.label-unidad')
                    .text(
                        'Cantidad del grupo'
                    );


                $regla
                    .find('.label-precio')
                    .text(
                        'Precio total del grupo'
                    );


                ayuda =
                    'Ejemplo: 3 Pilsen por S/20 → grupo = 3 y precio = S/20.';

                break;
        }


        $regla
            .find('.ayuda-regla')
            .text(
                ayuda
            );
    }


    // ========================================================
    // ELIMINAR REGLA
    // ========================================================

    $('#listaReglas').on(
        'click',
        '.btn-eliminar-regla',
        function () {

            $(this)
                .closest(
                    '.regla-promocion'
                )
                .remove();


            if (
                $('.regla-promocion')
                    .length === 0
            ) {

                $('#sinReglas')
                    .show();
            }
        }
    );


    // ========================================================
    // OBTENER REGLAS
    // ========================================================

    function obtenerReglas() {

        const reglas = [];


        $('.regla-promocion')
            .each(
                function () {

                    const $regla =
                        $(this);


                    reglas.push({

                        producto_id:
                            Number(
                                $regla
                                    .find(
                                        '.select-producto-promocion'
                                    )
                                    .val()
                                || 0
                            ),

                        tipo_beneficio:
                            $regla
                                .find(
                                    '.tipo-beneficio'
                                )
                                .val(),

                        cantidad_minima:
                            Number(
                                $regla
                                    .find(
                                        '.cantidad-minima'
                                    )
                                    .val()
                                || 1
                            ),

                        unidad_beneficiada:
                            valorNullable(
                                $regla
                                    .find(
                                        '.unidad-beneficiada'
                                    )
                                    .val()
                            ),

                        precio_promocional:
                            valorNullable(
                                $regla
                                    .find(
                                        '.precio-promocional'
                                    )
                                    .val()
                            ),

                        porcentaje_descuento:
                            valorNullable(
                                $regla
                                    .find(
                                        '.porcentaje-descuento'
                                    )
                                    .val()
                            ),

                        monto_descuento:
                            valorNullable(
                                $regla
                                    .find(
                                        '.monto-descuento'
                                    )
                                    .val()
                            ),

                        max_aplicaciones_por_venta:
                            valorNullable(
                                $regla
                                    .find(
                                        '.max-aplicaciones'
                                    )
                                    .val()
                            )
                    });

                }
            );


        return reglas;
    }


    function valorNullable(valor) {

        if (
            valor === ''
            ||
            valor === null
            ||
            valor === undefined
        ) {
            return null;
        }


        return Number(valor);
    }


    // ========================================================
    // GUARDAR
    // ========================================================

    $('#btnGuardarPromocion').on(
        'click',
        function () {

            const nombre =
                $('#nombre')
                    .val()
                    .trim();


            const fechaInicio =
                $('#fecha_inicio')
                    .val();


            const fechaFin =
                $('#fecha_fin')
                    .val();


            const reglas =
                obtenerReglas();


            if (nombre === '') {

                Swal.fire({
                    icon: 'warning',
                    title: 'Falta el nombre',
                    text:
                        'Ingrese el nombre de la promoción.'
                });

                return;
            }


            if (
                !fechaInicio
                ||
                !fechaFin
            ) {

                Swal.fire({
                    icon: 'warning',
                    title: 'Falta la vigencia',
                    text:
                        'Seleccione las fechas de inicio y fin.'
                });

                return;
            }


            if (
                fechaFin
                <
                fechaInicio
            ) {

                Swal.fire({
                    icon: 'warning',
                    title: 'Fechas inválidas',
                    text:
                        'La fecha final no puede ser anterior a la inicial.'
                });

                return;
            }


            if (reglas.length === 0) {

                Swal.fire({
                    icon: 'warning',
                    title: 'Sin productos',
                    text:
                        'Agregue al menos un producto a la promoción.'
                });

                return;
            }


            for (
                const regla
                of reglas
            ) {

                if (
                    regla.producto_id <= 0
                ) {

                    Swal.fire({
                        icon: 'warning',
                        title: 'Producto faltante',
                        text:
                            'Seleccione el producto de todas las reglas.'
                    });

                    return;
                }
            }


            const dias =
                $('.dia-promocion:checked')
                    .map(
                        function () {
                            return Number(
                                this.value
                            );
                        }
                    )
                    .get();


            const $btn =
                $(this);


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
                    '<?= BASE_URL ?>ajax/promociones/guardar.php',

                type:
                    'POST',

                dataType:
                    'json',

                data: {

                    id:
                        promocionId || '',

                    nombre:
                        nombre,

                    descripcion:
                        $('#descripcion').val(),

                    fecha_inicio:
                        fechaInicio,

                    fecha_fin:
                        fechaFin,

                    prioridad: 0,

                    acumulable:
                        $('#acumulable')
                            .is(':checked')
                            ? 1
                            : 0,

                    dias:
                        JSON.stringify(
                            dias
                        ),

                    reglas:
                        JSON.stringify(
                            reglas
                        )
                },

                success: function (response) {

                    if (!response.success) {

                        Swal.fire({
                            icon: 'error',
                            title: 'No se pudo guardar',
                            text: response.message
                        });

                        return;
                    }


                    Swal.fire({

                        icon:
                            'success',

                        title:
                            'Promoción guardada',

                        text:
                            response.message,

                        confirmButtonText:
                            'Aceptar'

                    }).then(function () {

                        window.location.href =
                            '<?= BASE_URL ?>modules/promociones/index.php';

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
                            'No se pudo guardar la promoción.'
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
                            Guardar promoción
                        `);
                }

            });

        }
    );


    // ========================================================
    // EDITAR
    // ========================================================

    if (promocionId > 0) {

        cargarPromocion();

    } else {

        /*
         * Para nueva promoción comenzamos
         * con una regla vacía.
         */
        agregarRegla();
    }


    function cargarPromocion() {

        $.ajax({

            url:
                '<?= BASE_URL ?>ajax/promociones/obtener.php',

            type:
                'GET',

            dataType:
                'json',

            data: {
                id:
                    promocionId
            },

            success: function (response) {

                if (!response.success) {

                    Swal.fire({
                        icon: 'error',
                        title: 'Error',
                        text: response.message
                    }).then(function () {

                        window.location.href =
                            '<?= BASE_URL ?>modules/promociones/index.php';

                    });

                    return;
                }


                const data =
                    response.data;


                $('#tituloPagina')
                    .text(
                        'Editar promoción'
                    );


                $('#nombre')
                    .val(
                        data.promocion.nombre
                    );


                $('#descripcion')
                    .val(
                        data.promocion.descripcion
                        ?? ''
                    );


                $('#fecha_inicio')
                    .val(
                        data.promocion.fecha_inicio
                    );


                $('#fecha_fin')
                    .val(
                        data.promocion.fecha_fin
                    );


                $('#prioridad')
                    .val(
                        data.promocion.prioridad
                    );


                $('#acumulable')
                    .prop(
                        'checked',
                        Number(
                            data.promocion.acumulable
                        ) === 1
                    );


                $('.dia-promocion')
                    .prop(
                        'checked',
                        false
                    );


                data.dias.forEach(
                    function (dia) {

                        $(
                            '#dia'
                            +
                            dia
                        )
                            .prop(
                                'checked',
                                true
                            );
                    }
                );


                $('#listaReglas')
                    .empty();


                data.reglas.forEach(
                    function (regla) {

                        agregarRegla(
                            regla
                        );
                    }
                );

            },

            error: function (xhr) {

                console.error(
                    xhr.responseText
                );
            }

        });
    }

});
</script>

<?php
require_once __DIR__ . '/../../includes/footer.php';
?>