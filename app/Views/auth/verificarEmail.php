<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="referrer" content="no-referrer">
    <link rel="stylesheet" href="<?= base_url('css/estilos.css') ?>">
    <title>Verificar correo | Comedor Universitario</title>
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
        <section class="form-card registro-card" aria-labelledby="verificacion-titulo">
            <div class="registro-intro">
                <h1 id="verificacion-titulo">Verificar correo electrónico</h1>
                <p>La verificación confirma que podés recibir mensajes en la dirección asociada a tu cuenta.</p>
            </div>

            <?php if ($tokenValido): ?>
                <p>Al confirmar, vas a poder iniciar sesión en el sistema.</p>
                <form action="<?= base_url('verificar-email') ?>" method="post">
                    <?= csrf_field() ?>
                    <input type="hidden" name="token" value="<?= esc($token) ?>">
                    <button type="submit">Confirmar correo</button>
                </form>
            <?php else: ?>
                <div class="mensaje-error" role="alert">El enlace no es válido o venció. Podés solicitar uno nuevo.</div>
                <a class="auth-link" href="<?= base_url('verificacion/reenviar') ?>">Solicitar otro enlace</a>
            <?php endif; ?>
        </section>
    </main>

    <footer>
        <div class="wrap footer-inner">
            Comedor Universitario · Secretaría de Bienestar Estudiantil - 2026 ®
        </div>
    </footer>
</body>
</html>
