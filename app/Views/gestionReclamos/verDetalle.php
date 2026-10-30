<?php
/** @var array $reclamo */
$pendiente = $reclamo['estado'] === 'pendiente';
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="stylesheet" href="<?= base_url('css/estilos.css') ?>?v=<?= time() ?>">
    <title>Reclamo #<?= esc(str_pad($reclamo['id'], 4, '0', STR_PAD_LEFT)) ?></title>
</head>
<body class="panel-page">

    <?= $this->include('partials/headerResponsable') ?>

    <main class="wrap">
    <section class="welcome welcome-detalle">
        <a class="card-link" href="<?= base_url('gestionReclamos/listar') ?>">← Volver a reclamos</a>
        <div class="eyebrow">Reclamo #<?= esc(str_pad($reclamo['id'], 4, '0', STR_PAD_LEFT)) ?></div>
        <h1><?= esc($reclamo['categoria_texto']) ?></h1>
        <p>
            Presentado por <strong><?= esc($reclamo['estudiante_nombre']) ?></strong>
            (<?= esc($reclamo['estudiante_email']) ?>)
            el <?= esc(date('d/m/Y H:i', strtotime($reclamo['fecha_creacion']))) ?>
            <span class="badge <?= esc($reclamo['badge']['clase']) ?>"><?= esc($reclamo['badge']['texto']) ?></span>
        </p>
    </section>

    <div class="detalle-grid">
        <div class="tracker-card">
            <div class="label">Descripción del reclamo</div>
            <p><?= nl2br(esc($reclamo['descripcion'])) ?></p>
        </div>

        <?php if ($pendiente): ?>
            <div class="form-card">
                <h2>Responder</h2>

                <?php if (session()->getFlashdata('errors')): ?>
                    <div class="mensaje-error">
                        <ul>
                            <?php foreach (session()->getFlashdata('errors') as $error): ?>
                                <li><?= esc($error) ?></li>
                            <?php endforeach; ?>
                        </ul>
                    </div>
                <?php endif; ?>
                <?php if (session()->getFlashdata('error')): ?>
                    <div class="mensaje-error"><?= esc(session()->getFlashdata('error')) ?></div>
                <?php endif; ?>

                <form action="<?= base_url('gestionReclamos/responder/' . $reclamo['id']) ?>" method="post">
                    <?= csrf_field() ?>
                    <label for="respuesta">Tu respuesta:</label>
                    <textarea id="respuesta" name="respuesta" rows="6" maxlength="1000" required><?= esc(old('respuesta')) ?></textarea>
                    <button type="submit">Enviar respuesta</button>
                </form>
            </div>
        <?php else: ?>
            <div class="tracker-card">
                <div class="label">
                    Respuesta de <?= esc($reclamo['responsable_nombre'] ?? 'Responsable') ?>
                    · <?= esc(date('d/m/Y H:i', strtotime($reclamo['fecha_respuesta']))) ?>
                </div>
                <p><?= nl2br(esc($reclamo['respuesta'])) ?></p>
            </div>
        <?php endif; ?>
    </div>
    </main>

<footer>
    <div class="wrap footer-inner">
        Comedor Universitario · Secretaría de Bienestar Estudiantil - 2026 ®
    </div>
</footer>

</body>
</html>