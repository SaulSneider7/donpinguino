<?php

$pageTitle = 'Promociones';

require_once __DIR__ . '/../../includes/header.php';
require_once __DIR__ . '/../../includes/navbar.php';

?>

<main class="container-fluid py-4">

    <div class="d-flex flex-column flex-md-row justify-content-between align-items-md-center gap-3 mb-4">

        <div>
            <h1 class="h3 fw-bold mb-1">
                Promociones
            </h1>

            <p class="text-muted mb-0">
                Administra promociones, vigencias y precios especiales.
            </p>
        </div>

        <a
            href="<?= BASE_URL ?>modules/promociones/form.php"
            class="btn btn-dark"
        >
            <i class="fa-solid fa-plus me-2"></i>
            Nueva promoción
        </a>

    </div>


    <div class="card border-0 shadow-sm">

        <div class="card-body">

            <div class="table-responsive">

                <table
                    id="tablaPromociones"
                    class="table table-hover align-middle w-100"
                >

                    <thead>
                    <tr>
                        <th>ID</th>
                        <th>Promoción</th>
                        <th>Vigencia</th>
                        <th>Días</th>
                        <th>Productos</th>
                        <th>Estado</th>
                        <th>Acciones</th>
                    </tr>
                    </thead>

                </table>

            </div>

        </div>

    </div>

</main>


<script>
document.addEventListener('DOMContentLoaded', function () {

    const tabla =
        $('#tablaPromociones').DataTable({

            processing: true,
            serverSide: true,

            ajax: {
                url:
                    '<?= BASE_URL ?>ajax/promociones/listar.php',

                type:
                    'POST'
            },

            order: [
                [0, 'desc']
            ],

            pageLength:
                10,

            columns: [

                { data: 'id' },

                { data: 'promocion' },

                { data: 'vigencia' },

                { data: 'dias' },

                { data: 'productos' },


                { data: 'estado' },

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
    // ACTIVAR / DESACTIVAR
    // ========================================================

    $('#tablaPromociones').on(
        'click',
        '.btn-estado-promocion',
        function () {

            const id =
                Number($(this).data('id'));

            const activo =
                Number($(this).data('activo'));

            const activar =
                activo === 0;


            Swal.fire({

                icon: 'question',

                title:
                    activar
                        ? '¿Activar promoción?'
                        : '¿Desactivar promoción?',

                text:
                    activar
                        ? 'La promoción podrá aplicarse nuevamente si se encuentra vigente.'
                        : 'La promoción dejará de aplicarse inmediatamente.',

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

                    url:
                        '<?= BASE_URL ?>ajax/promociones/cambiar_estado.php',

                    type:
                        'POST',

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

                    },

                    error: function (xhr) {

                        console.error(
                            xhr.responseText
                        );

                        Swal.fire({
                            icon: 'error',
                            title: 'Error',
                            text:
                                'No se pudo actualizar la promoción.'
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
