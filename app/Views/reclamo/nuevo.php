<?php
//$categorias valor del ENUM => texto que ve el estudiante (viene de ReclamoModel::CATEGORIAS) //
/** @var array<string, string> $categorias */
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="stylesheet" href="<?= base_url('css/estilos.css') ?>?v=<?= time() ?>">
    <title>Cargar reclamo</title>
</head>
<body class="panel-page">

    <?= $this->include('partials/headerEstudiante') ?>

    <main class="wrap">
        <div class="form-card">
            <h1>Cargar reclamo</h1>

            <?php if (session()->getFlashdata('errors')): ?>
                <div class="mensaje-error">
                    <ul>
                        <?php foreach (session()->getFlashdata('errors') as $error): ?>
                            <li><?= esc($error) ?></li>
                        <?php endforeach; ?>
                    </ul>
                </div>
            <?php endif; ?>

            <form action="<?= base_url('reclamos/crear') ?>" method="post">
                <?= csrf_field() ?>

                <label for="categoria">Categoría:</label>
                <select id="categoria" name="categoria" required>
                    <option value="">Seleccioná una opción</option>
                    <?php foreach ($categorias as $valor => $texto): ?>
                        <option value="<?= $valor ?>" <?= old('categoria') === $valor ? 'selected' : '' ?>>
                            <?= esc($texto) ?>
                        </option>
                    <?php endforeach; ?>
                </select>

                <label for="descripcion">Describí tu reclamo:</label>
                <!-- El valor de un textarea va ENTRE las etiquetas, no en un atributo -->
                <textarea id="descripcion" name="descripcion" rows="6" maxlength="1000" required><?= esc(old('descripcion')) ?></textarea>

                <button type="submit">Enviar reclamo</button>
            </form>
        </div>
    </main>

<footer>
    <div class="wrap footer-inner">
        Comedor Universitario · Secretaría de Bienestar Estudiantil
    </div>
</footer>

</body>
</html>