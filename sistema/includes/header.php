<?php

require_once __DIR__ . '/auth.php';

$pageTitle = $pageTitle ?? APP_NAME;

?>

<!doctype html>
<html lang="es">

<head>

    <meta charset="UTF-8">

    <meta
        name="viewport"
        content="width=device-width, initial-scale=1"
    >

    <meta
        name="robots"
        content="noindex,nofollow"
    >

    <title>
        <?= htmlspecialchars($pageTitle) ?> | <?= APP_NAME ?>
    </title>

    <link rel="shortcut icon" href="<?= BASE_URL ?>/assets/img/favicon.webp" type="image/x-icon">

    <!-- =====================================================
         CSS
    ====================================================== -->

    <!-- Bootstrap -->
    <link
        href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.8/dist/css/bootstrap.min.css"
        rel="stylesheet"
        integrity="sha384-sRIl4kxILFvY47J16cr9ZwB07vP4J8+LH7qKQnuqkuIAvNWLzeN8tE5YBujZqJLB"
        crossorigin="anonymous"
    >


    <!-- Font Awesome -->
    <link
        href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/7.0.0/css/all.min.css"
        rel="stylesheet"
    >


    <!-- DataTables Bootstrap -->
    <link
        href="https://cdn.datatables.net/2.3.3/css/dataTables.bootstrap5.min.css"
        rel="stylesheet"
    >


    <!-- Select2 -->
    <link
        href="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/css/select2.min.css"
        rel="stylesheet"
    >


    <!-- Select2 Bootstrap 5 Theme -->
    <link
        href="https://cdn.jsdelivr.net/npm/select2-bootstrap-5-theme@1.3.0/dist/select2-bootstrap-5-theme.min.css"
        rel="stylesheet"
    >


    <!-- CSS propio -->
    <link
        href="<?= BASE_URL ?>assets/css/app.css"
        rel="stylesheet"
    >


    <!-- =====================================================
         JAVASCRIPT BASE
         
         Se cargan aquí porque los módulos tienen scripts
         propios antes del footer.
    ====================================================== -->


    <!-- jQuery -->
    <script
        src="https://code.jquery.com/jquery-3.7.1.min.js"
    ></script>


    <!-- Bootstrap -->
    <script
        src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.8/dist/js/bootstrap.bundle.min.js"
        integrity="sha384-FKyoEForCGlyvwx9Hj09JcYn3nv7wiPVlz7YYwJrWVcXK/BmnVDxM+D2scQbITxI"
        crossorigin="anonymous"
    ></script>


    <!-- SweetAlert2 -->
    <script
        src="https://cdn.jsdelivr.net/npm/sweetalert2@11"
    ></script>


    <!-- DataTables -->
    <script
        src="https://cdn.datatables.net/2.3.3/js/dataTables.min.js"
    ></script>


    <script
        src="https://cdn.datatables.net/2.3.3/js/dataTables.bootstrap5.min.js"
    ></script>


    <!-- Select2 -->
    <script
        src="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/js/select2.min.js"
    ></script>

</head>


<body class="bg-light">