<?php $error = session()->getFlashdata('error'); ?>
<?php $exito = session()->getFlashdata('exito'); ?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="stylesheet" href="<?= base_url('css/estilos.css') ?>?v=<?= time() ?>">
    <title>Recuperar contraseña | Comedor Universitario</title>
</head>
<body class="panel-page">
    <header>
        <div class="wrap header-inner">
            <div class="brand">
                <img class="brand-logo" src="<?= base_url('images/comedor-logo.png') ?>" alt="">
                <div class="brand-text">Comedor Universitario
                    <span>Gestión de trámites en línea</span>
                </div>
            </div>
        </div>
    </header>

    <main class="auth-main registro-main">
        <section class="form-card registro-card" aria-labelledby="recuperar-titulo">
            <div class="registro-intro">
                <h1 id="recuperar-titulo">Recuperar contraseña</h1>
                <p>Ingresá el correo asociado a tu cuenta. Si existe, recibirás instrucciones para recuperar el acceso.</p>
            </div>

            <?php if ($error): ?>
                <div class="mensaje-error" role="alert"><?= esc($error) ?></div>
            <?php endif; ?>
            <?php if ($exito): ?>
                <div class="mensaje-exito" role="status"><?= esc($exito) ?></div>
            <?php endif; ?>

            <form class="recuperar-form" action="<?= base_url('password/recuperar') ?>" method="post">
                <?= csrf_field() ?>
                <label for="email">Correo electrónico</label>
                <input type="email" id="email" name="email" maxlength="150" autocomplete="email" required>
                <button type="submit">Enviar instrucciones</button>
                <a class="auth-link" href="<?= base_url('login') ?>">Volver a iniciar sesión</a>
            </form>
        </section>
    </main>

    <footer>
        <div class="wrap footer-inner">
            Comedor Universitario · Secretaría de Bienestar Estudiantil - 2026 ®
        </div>
    </footer>
</body>
</html>
