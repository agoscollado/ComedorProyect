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
                <div class="brand-mark"></div>
                <div class="brand-text">Comedor Universitario
                    <span>Gestión de trámites en línea</span>
                </div>
            </div>
        </div>
    </header>
    <!--- Mostrar mensaje de error si existe -->
    <?php $error = session()->getFlashdata('error'); ?>
        <?php if ($error): ?>
            <p><?= $error ?></p>
        <?php endif; ?>

    <main class="auth-main">
    <form class="tabs" action="<?= base_url('login') ?>" method="post">
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
                
            </div>
    </form>
    </main>
    
    <footer>
            <div class="wrap footer-inner">
                Comedor Universitario · Secretaría de Bienestar Estudiantil
            </div>
        </footer>
</body>
</html>

