<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="stylesheet" href="<?= base_url('css/estilos.css') ?>?v=<?= time() ?>">
    <title>Login</title>
</head>
<body>
    <!--- Mostrar mensaje de error si existe -->
    <?php $error = session()->getFlashdata('error'); ?>
        <?php if ($error): ?>
            <p><?= $error ?></p>
        <?php endif; ?>

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
</body>
</html>

