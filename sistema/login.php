<?php

session_start();

require_once __DIR__ . '/config/app.php';

if (isset($_SESSION['usuario_id'])) {
    header('Location: ' . BASE_URL . 'index.php');
    exit;
}
?>

<!doctype html>
<html lang="es">
<head>
    <meta charset="UTF-8">

    <meta
        name="viewport"
        content="width=device-width, initial-scale=1"
    >

    <title>Iniciar sesión | <?= APP_NAME ?></title>

    <link rel="shortcut icon" href="<?= BASE_URL ?>/assets/img/favicon.webp" type="image/x-icon">


    <link
        href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.8/dist/css/bootstrap.min.css"
        rel="stylesheet"
    >

    <link
        rel="stylesheet"
        href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/7.0.0/css/all.min.css"
    >
</head>

<body class="bg-light">

<div class="container min-vh-100 d-flex align-items-center justify-content-center">

    <div class="row justify-content-center w-100">

        <div class="col-12 col-sm-9 col-md-6 col-lg-4">

            <div class="card shadow-sm border-0">

                <div class="card-body p-4">

                    <div class="text-center mb-4">

                        <img
                            src="<?= BASE_URL ?>/assets/img/favicon.webp"
                            alt="Logo Don Pingüino"
                            class="img-fluid"
                            width="50"
                        >

                        <h3 class="fw-bold mb-1">
                            Don Pingüino
                        </h3>

                        <p class="text-muted mb-0">
                            Sistema de gestión
                        </p>

                    </div>

                    <form id="formLogin">

                        <div class="mb-3">

                            <label class="form-label">
                                Usuario
                            </label>

                            <div class="input-group">

                                <span class="input-group-text">
                                    <i class="fa-solid fa-user"></i>
                                </span>

                                <input
                                    type="text"
                                    class="form-control"
                                    name="usuario"
                                    id="usuario"
                                    autocomplete="username"
                                    required
                                >

                            </div>

                        </div>

                        <div class="mb-4">

                            <label class="form-label">
                                Contraseña
                            </label>

                            <div class="input-group">

                                <span class="input-group-text">
                                    <i class="fa-solid fa-lock"></i>
                                </span>

                                <input
                                    type="password"
                                    class="form-control"
                                    name="password"
                                    id="password"
                                    autocomplete="current-password"
                                    required
                                >

                            </div>

                        </div>

                        <button
                            type="submit"
                            class="btn btn-dark w-100"
                            id="btnLogin"
                        >
                            <i class="fa-solid fa-right-to-bracket me-2"></i>
                            Ingresar
                        </button>

                    </form>

                </div>

            </div>

        </div>

    </div>

</div>


<script src="https://code.jquery.com/jquery-3.7.1.min.js"></script>

<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>

<script>
$(function () {

    $('#formLogin').on('submit', function (e) {
        e.preventDefault();

        const $btn = $('#btnLogin');

        $btn.prop('disabled', true);

        $btn.html(`
            <span
                class="spinner-border spinner-border-sm me-2"
            ></span>
            Ingresando...
        `);

        $.ajax({
            url: 'ajax/auth/login.php',
            type: 'POST',
            dataType: 'json',
            data: $(this).serialize(),

            success: function (response) {

                if (response.success) {

                    window.location.href = 'index.php';

                    return;
                }

                Swal.fire({
                    icon: 'error',
                    title: 'No se pudo iniciar sesión',
                    text: response.message
                });
            },

            error: function () {

                Swal.fire({
                    icon: 'error',
                    title: 'Error',
                    text: 'Ocurrió un error al comunicarse con el servidor.'
                });
            },

            complete: function () {

                $btn.prop('disabled', false);

                $btn.html(`
                    <i class="fa-solid fa-right-to-bracket me-2"></i>
                    Ingresar
                `);
            }
        });
    });

});
</script>

</body>
</html>