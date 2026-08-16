<?php

$pageTitle = 'Combos';

require_once __DIR__ . '/../../includes/header.php';
require_once __DIR__ . '/../../includes/navbar.php';

?>

<main class="container-fluid py-4">

    <div class="d-flex flex-column flex-md-row justify-content-between align-items-md-center gap-3 mb-4">

        <div>
            <h1 class="h3 fw-bold mb-1">
                Combos
            </h1>

            <p class="text-muted mb-0">
                Configura los productos que forman cada combo.
            </p>
        </div>

        <a
            href="<?= BASE_URL ?>modules/productos/index.php"
            class="btn btn-outline-dark"
        >
            <i class="fa-solid fa-plus me-2"></i>
            Crear producto combo
        </a>

    </div>


    <div class="alert alert-info">

        <i class="fa-solid fa-circle-info me-2"></i>

        Primero crea el producto desde
        <strong>Productos</strong>
        seleccionando tipo
        <strong>Combo</strong>.
        Luego configura aquí sus componentes.

    </div>


    <div class="card border-0 shadow-sm">

        <div class="card-body">

            <div class="table-responsive">

                <table
                    id="tablaCombos"
                    class="table table-hover align-middle w-100"
                >

                    <thead>
                    <tr>
                        <th>ID</th>
                        <th>Combo</th>
                        <th>Precio</th>
                        <th>Componentes</th>
                        <th>Disponibles</th>
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

    $('#tablaCombos').DataTable({

        processing: true,
        serverSide: true,

        ajax: {
            url:
                '<?= BASE_URL ?>ajax/combos/listar.php',

            type:
                'POST'
        },

        order: [
            [0, 'desc']
        ],

        pageLength: 10,

        columns: [

            {
                data: 'id'
            },

            {
                data: 'combo'
            },

            {
                data: 'precio'
            },

            {
                data: 'componentes'
            },

            {
                data: 'disponibles'
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

});
</script>

<?php
require_once __DIR__ . '/../../includes/footer.php';
?>