<?php

$pageTitle = 'Configurar combo';

require_once __DIR__ . '/../../includes/header.php';
require_once __DIR__ . '/../../includes/navbar.php';

$productoId =
    (int) ($_GET['producto_id'] ?? 0);

?>

<main class="container-fluid py-4">

    <div class="row justify-content-center">

        <div class="col-12 col-xl-9">

            <div class="d-flex justify-content-between align-items-center mb-4">

                <div>

                    <h1 class="h3 fw-bold mb-1">
                        Configurar combo
                    </h1>

                    <div
                        class="text-muted"
                        id="nombreCombo"
                    ></div>

                </div>


                <a
                    href="<?= BASE_URL ?>modules/combos/index.php"
                    class="btn btn-outline-secondary"
                >
                    <i class="fa-solid fa-arrow-left me-1"></i>
                    Volver
                </a>

            </div>


            <input
                type="hidden"
                id="productoComboId"
                value="<?= $productoId ?>"
            >


            <div class="card border-0 shadow-sm mb-3">

                <div class="card-body">

                    <div class="row g-3">

                        <div class="col-12 col-md-6">

                            <div class="small text-muted">
                                Precio de venta
                            </div>

                            <div
                                class="fs-4 fw-bold"
                                id="precioCombo"
                            >
                                S/ 0.00
                            </div>

                        </div>


                        <div class="col-12 col-md-6">

                            <div class="small text-muted">
                                Costo estimado del combo
                            </div>

                            <div
                                class="fs-4 fw-bold"
                                id="costoCombo"
                            >
                                S/ 0.00
                            </div>

                        </div>

                    </div>

                </div>

            </div>


            <div class="card border-0 shadow-sm">

                <div class="card-header bg-white border-0 pt-3">

                    <div class="d-flex justify-content-between align-items-center">

                        <div>

                            <h5 class="fw-bold mb-0">
                                Componentes
                            </h5>

                            <small class="text-muted">
                                Productos descontados al vender una unidad del combo.
                            </small>

                        </div>


                        <button
                            type="button"
                            class="btn btn-dark btn-sm"
                            id="btnAgregarComponente"
                        >
                            <i class="fa-solid fa-plus me-1"></i>
                            Agregar
                        </button>

                    </div>

                </div>


                <div class="card-body">

                    <div id="listaComponentes"></div>


                    <div
                        id="sinComponentes"
                        class="text-center text-muted py-5"
                    >

                        <i class="fa-solid fa-box-open fa-2x mb-2"></i>

                        <div>
                            Agrega los productos del combo.
                        </div>

                    </div>

                </div>


                <div class="card-footer bg-white">

                    <div class="d-flex justify-content-end">

                        <button
                            type="button"
                            class="btn btn-dark"
                            id="btnGuardarCombo"
                        >
                            <i class="fa-solid fa-floppy-disk me-2"></i>
                            Guardar configuración
                        </button>

                    </div>

                </div>

            </div>

        </div>

    </div>

</main>


<script>
document.addEventListener('DOMContentLoaded', function () {

    const productoComboId =
        Number(
            $('#productoComboId').val()
        );


    let componentes = [];

    let combo = null;


    if (productoComboId <= 0) {

        Swal.fire({
            icon: 'error',
            title: 'Combo inválido'
        }).then(function () {

            window.location.href =
                '<?= BASE_URL ?>modules/combos/index.php';

        });

        return;
    }


    // ========================================================
    // CARGAR
    // ========================================================

    cargarCombo();


    function cargarCombo() {

        $.ajax({

            url:
                '<?= BASE_URL ?>ajax/combos/obtener.php',

            type:
                'GET',

            dataType:
                'json',

            data: {
                producto_id:
                    productoComboId
            },

            success: function (response) {

                if (!response.success) {

                    Swal.fire({
                        icon: 'error',
                        title: 'Error',
                        text: response.message
                    }).then(function () {

                        window.location.href =
                            '<?= BASE_URL ?>modules/combos/index.php';

                    });

                    return;
                }


                combo =
                    response.data.combo;


                componentes =
                    response.data.componentes
                        .map(
                            item => ({

                                producto_id:
                                    Number(
                                        item.producto_id
                                    ),

                                nombre:
                                    item.nombre,

                                presentacion:
                                    item.presentacion,

                                cantidad:
                                    Number(
                                        item.cantidad
                                    ),

                                costo_referencia:
                                    Number(
                                        item.costo_referencia
                                    )
                            })
                        );


                $('#nombreCombo')
                    .text(
                        combo.nombre
                    );


                $('#precioCombo')
                    .text(
                        'S/ '
                        +
                        Number(
                            combo.precio_venta
                        ).toFixed(2)
                    );


                render();
            }

        });
    }


    // ========================================================
    // AGREGAR
    // ========================================================

    $('#btnAgregarComponente').on(
        'click',
        function () {

            const idTemporal =
                'nuevo'
                +
                Date.now();


            componentes.push({

                temporal:
                    idTemporal,

                producto_id:
                    null,

                nombre:
                    '',

                presentacion:
                    '',

                cantidad:
                    1,

                costo_referencia:
                    0
            });


            render();
        }
    );


    // ========================================================
    // RENDER
    // ========================================================

    function render() {

        const $lista =
            $('#listaComponentes');


        $lista.empty();


        if (componentes.length === 0) {

            $('#sinComponentes')
                .show();

            actualizarCosto();

            return;
        }


        $('#sinComponentes')
            .hide();


        componentes.forEach(
            function (item, index) {

                const selectId =
                    'componenteProducto'
                    +
                    index;


                $lista.append(`

                    <div
                        class="border rounded p-3 mb-3 componente"
                        data-index="${index}"
                    >

                        <div class="row g-3 align-items-end">

                            <div class="col-12 col-md-7">

                                <label class="form-label">
                                    Producto
                                </label>

                                <select
                                    class="form-select select-componente"
                                    id="${selectId}"
                                    data-index="${index}"
                                ></select>

                            </div>


                            <div class="col-8 col-md-3">

                                <label class="form-label">
                                    Cantidad
                                </label>

                                <input
                                    type="number"
                                    class="form-control cantidad-componente"
                                    data-index="${index}"
                                    min="0.001"
                                    step="0.001"
                                    value="${item.cantidad}"
                                >

                            </div>


                            <div class="col-4 col-md-2">

                                <button
                                    type="button"
                                    class="btn btn-outline-danger w-100 btn-eliminar-componente"
                                    data-index="${index}"
                                >
                                    <i class="fa-solid fa-trash"></i>
                                </button>

                            </div>

                        </div>


                        <div class="small text-muted mt-2">

                            Costo del componente:

                            <strong>
                                S/
                                ${(
                                    item.cantidad
                                    *
                                    item.costo_referencia
                                ).toFixed(2)}
                            </strong>

                        </div>

                    </div>

                `);


                const $select =
                    $('#' + selectId);


                $select.select2({

                    width:
                        '100%',

                    placeholder:
                        'Buscar producto...',

                    ajax: {

                        url:
                            '<?= BASE_URL ?>ajax/productos/buscar_componentes.php',

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


                if (item.producto_id) {

                    const texto =
                        item.nombre
                        +
                        (
                            item.presentacion
                                ? ' - ' + item.presentacion
                                : ''
                        );


                    const option =
                        new Option(
                            texto,
                            item.producto_id,
                            true,
                            true
                        );


                    $select
                        .append(option)
                        .trigger('change');
                }

            }
        );


        actualizarCosto();
    }


    // ========================================================
    // SELECCIONAR PRODUCTO
    // ========================================================

    $('#listaComponentes').on(
        'select2:select',
        '.select-componente',
        function (e) {

            const index =
                Number(
                    $(this).data('index')
                );


            const data =
                e.params.data;


            componentes[index].producto_id =
                Number(data.id);


            componentes[index].nombre =
                data.nombre;


            componentes[index].presentacion =
                data.presentacion;


            componentes[index].costo_referencia =
                Number(
                    data.costo_referencia
                );


            actualizarCosto();
        }
    );


    // ========================================================
    // CANTIDAD
    // ========================================================

    $('#listaComponentes').on(
        'input',
        '.cantidad-componente',
        function () {

            const index =
                Number(
                    $(this).data('index')
                );


            componentes[index].cantidad =
                Number(
                    $(this).val()
                    || 0
                );


            actualizarCosto();
        }
    );


    // ========================================================
    // ELIMINAR
    // ========================================================

    $('#listaComponentes').on(
        'click',
        '.btn-eliminar-componente',
        function () {

            const index =
                Number(
                    $(this).data('index')
                );


            componentes.splice(
                index,
                1
            );


            render();
        }
    );


    // ========================================================
    // COSTO
    // ========================================================

    function actualizarCosto() {

        let costo = 0;


        componentes.forEach(
            function (item) {

                costo +=
                    Number(item.cantidad)
                    *
                    Number(
                        item.costo_referencia
                    );
            }
        );


        $('#costoCombo')
            .text(
                'S/ '
                +
                costo.toFixed(2)
            );
    }


    // ========================================================
    // GUARDAR
    // ========================================================

    $('#btnGuardarCombo').on(
        'click',
        function () {

            if (
                componentes.length === 0
            ) {

                Swal.fire({
                    icon: 'warning',
                    title: 'Sin componentes',
                    text:
                        'Agregue al menos un producto.'
                });

                return;
            }


            const ids = [];


            for (
                const componente
                of componentes
            ) {

                if (
                    !componente.producto_id
                ) {

                    Swal.fire({
                        icon: 'warning',
                        title: 'Producto faltante',
                        text:
                            'Seleccione todos los componentes.'
                    });

                    return;
                }


                if (
                    componente.cantidad <= 0
                ) {

                    Swal.fire({
                        icon: 'warning',
                        title: 'Cantidad inválida'
                    });

                    return;
                }


                if (
                    ids.includes(
                        componente.producto_id
                    )
                ) {

                    Swal.fire({
                        icon: 'warning',
                        title: 'Producto repetido',
                        text:
                            'Un producto no puede aparecer dos veces en el mismo combo.'
                    });

                    return;
                }


                ids.push(
                    componente.producto_id
                );
            }


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
                    '<?= BASE_URL ?>ajax/combos/guardar.php',

                type:
                    'POST',

                dataType:
                    'json',

                data: {

                    producto_id:
                        productoComboId,

                    componentes:
                        JSON.stringify(
                            componentes.map(
                                c => ({
                                    producto_id:
                                        c.producto_id,

                                    cantidad:
                                        c.cantidad
                                })
                            )
                        )
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


                    Swal.fire({

                        icon:
                            'success',

                        title:
                            'Combo configurado',

                        text:
                            response.message,

                        confirmButtonText:
                            'Aceptar'

                    }).then(function () {

                        window.location.href =
                            '<?= BASE_URL ?>modules/combos/index.php';

                    });

                },

                error: function (xhr) {

                    console.error(
                        xhr.responseText
                    );

                },

                complete: function () {

                    $btn
                        .prop(
                            'disabled',
                            false
                        )
                        .html(`
                            <i class="fa-solid fa-floppy-disk me-2"></i>
                            Guardar configuración
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