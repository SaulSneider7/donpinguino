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

    <title>
        <?= htmlspecialchars($pageTitle) ?> | <?= APP_NAME ?>
    </title>

    <!-- Bootstrap -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.8/dist/css/bootstrap.min.css" rel="stylesheet" integrity="sha384-sRIl4kxILFvY47J16cr9ZwB07vP4J8+LH7qKQnuqkuIAvNWLzeN8tE5YBujZqJLB" crossorigin="anonymous">

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

    <link
        href="<?= BASE_URL ?>assets/css/app.css"
        rel="stylesheet"
    >

</head>

<body class="bg-light">