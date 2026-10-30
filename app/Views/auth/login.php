<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="stylesheet" href="<?= base_url('css/estilos.css') ?>?v=<?= time() ?>">
    <title>Login</title>
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
    <?php if ($exito = session()->getFlashdata('exito')): ?>
        <div class="auth-feedback mensaje-exito" role="status"><?= esc($exito) ?></div>
    <?php endif; ?>
    <?php if ($error = session()->getFlashdata('error')): ?>
        <div class="auth-feedback mensaje-error" role="alert"><?= esc($error) ?></div>
    <?php endif; ?>

    <main class="auth-main">
    <form class="tabs" action="<?= base_url('login') ?>" method="post">
        <?= csrf_field() ?>
        <input type="radio" id="tab_estudiante" name="rol" value="estudiante" checked>
        <label for="tab_estudiante">Estudiante</label>
        <input type="radio" id="tab_responsable" name="rol" value="responsable">
        <label for="tab_responsable">Responsable</label>
            <div class="panel">
                
                    <label for="email">Email:</label>
                    <input type="email" id="email" name="email" required>
                    
                    <label for="password">Contraseña:</label>
                    <input type="password" id="password" name="password" required>
                    
                    <button type="submit">Iniciar Sesión</button>
                    <a class="auth-link" href="<?= base_url('password/recuperar') ?>">¿Olvidaste tu contraseña?</a>
                    <a class="auth-link auth-link-registro" href="<?= base_url('registro') ?>">¿No tenés cuenta? Registrate</a>
            </div>
    </form>
    </main>
    
    <footer>
            <div class="wrap footer-inner">
                Comedor Universitario · Secretaría de Bienestar Estudiantil - 2026 ®
            </div>
        </footer>
</body>
</html>
