<?php
/** @var array $reclamos */
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="stylesheet" href="<?= base_url('css/estilos.css') ?>?v=<?= time() ?>">
    <title>Mis reclamos</title>
</head>
<body class="panel-page">

    <?= $this->include('partials/headerEstudiante') ?>

    <main class="wrap">
        <section class="welcome">
            <div class="eyebrow">Panel del Estudiante</div>
            <h1>Mis reclamos</h1>
            <p>Seguí el estado de tus reclamos y consultá las respuestas del comedor.</p>

            <?php if (session()->getFlashdata('exito')): ?>
                <div class="confirm-banner">✓ <?= esc(session()->getFlashdata('exito')) ?></div>
            <?php endif; ?>
            <?php if (session()->getFlashdata('error')): ?>
                <div class="mensaje-error"><?= esc(session()->getFlashdata('error')) ?></div>
            <?php endif; ?>

            <div class="acciones-reclamo">
                <a class="btn-link" href="<?= base_url('reclamos/nuevo') ?>">Nuevo reclamo</a>
            </div>
        </section>

        <section class="history">
            <?php if (empty($reclamos)): ?>
                <div class="tracker-card">
                    <p>Todavía no cargaste ningún reclamo.</p>
                </div>
            <?php else: ?>
                <div class="reclamo-lista">
                    <?php foreach ($reclamos as $reclamo): ?>
                        <div class="tracker-card">
                            <div class="tracker-head">
                                <div>
                                    <div class="label">Presentado el <?= esc(date('d/m/Y H:i', strtotime($reclamo['fecha_creacion']))) ?></div>
                                    <div class="title"><?= esc($reclamo['categoria_texto']) ?></div>
                                </div>
                                <div class="id">#<?= esc(str_pad($reclamo['id'], 4, '0', STR_PAD_LEFT)) ?></div>
                            </div>

                            <span class="badge <?= esc($reclamo['badge']['clase']) ?>"><?= esc($reclamo['badge']['texto']) ?></span>

                            <div class="campo">
                                <div class="campo-label">Tu reclamo</div>
                                <p><?= nl2br(esc($reclamo['descripcion'])) ?></p>
                            </div>

                            <div class="campo campo-respuesta">
                                <?php if ($reclamo['estado'] === 'respondido'): ?>
                                    <div class="campo-label">
                                        Respuesta del comedor · <?= esc(date('d/m/Y H:i', strtotime($reclamo['fecha_respuesta']))) ?>
                                    </div>
                                    <p><?= nl2br(esc($reclamo['respuesta'])) ?></p>
                                <?php else: ?>
                                    <p class="sin-respuesta">Todavía no hay respuesta. Te vamos a avisar acá cuando el comedor responda.</p>
                                <?php endif; ?>
                            </div>
                        </div>
                    <?php endforeach; ?>
                </div>
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