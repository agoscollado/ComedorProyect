<?php
/** @var array $reclamos */
/** @var string $estadoActual */
/** @var \CodeIgniter\Pager\Pager $pager */
$esPendiente = $estadoActual === 'pendiente';
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="stylesheet" href="<?= base_url('css/estilos.css') ?>?v=<?= time() ?>">
    <title>Gestión de reclamos</title>
</head>
<body class="panel-page">

    <?= $this->include('partials/headerResponsable') ?>

    <main class="wrap">
        <section class="welcome">
            <div class="eyebrow">Panel del Responsable</div>
            <h1>Reclamos</h1>
            <p>Revisá los reclamos presentados por los estudiantes y respondelos.</p>

            <?php if (session()->getFlashdata('exito')): ?>
                <div class="confirm-banner">✓ <?= esc(session()->getFlashdata('exito')) ?></div>
            <?php endif; ?>
            <?php if (session()->getFlashdata('error')): ?>
                <div class="mensaje-error"><?= esc(session()->getFlashdata('error')) ?></div>
            <?php endif; ?>
        </section>

        <section class="history">
            <div class="history-head">
                <h2><?= $esPendiente ? 'Pendientes de respuesta' : 'Respondidos' ?></h2>
                <a class="card-link" href="<?= base_url('gestionReclamos/listar?estado=' . ($esPendiente ? 'respondido' : 'pendiente')) ?>">
                    <?= $esPendiente ? 'Ver respondidos →' : '← Ver pendientes' ?>
                </a>
            </div>

            <?php if (empty($reclamos)): ?>
                <div class="tracker-card">
                    <p><?= $esPendiente ? 'No hay reclamos pendientes.' : 'Todavía no respondiste ningún reclamo.' ?></p>
                </div>
            <?php else: ?>
                <div class="table-responsive">
                <table class="history-table">
                    <thead>
                        <tr>
                            <th>Reclamo</th>
                            <th>Estudiante</th>
                            <th>Categoría</th>
                            <th>Fecha</th>
                            <th>Estado</th>
                            <th>Acciones</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($reclamos as $reclamo): ?>
                            <tr>
                                <td>#<?= esc(str_pad($reclamo['id'], 4, '0', STR_PAD_LEFT)) ?></td>
                                <td><?= esc($reclamo['estudiante_nombre']) ?></td>
                                <td><?= esc($reclamo['categoria_texto']) ?></td>
                                <td><?= esc(date('d/m/Y H:i', strtotime($reclamo['fecha_creacion']))) ?></td>
                                <td><span class="badge <?= esc($reclamo['badge']['clase']) ?>"><?= esc($reclamo['badge']['texto']) ?></span></td>
                                <td><a class="btn-link" href="<?= base_url('gestionReclamos/verDetalle/' . $reclamo['id']) ?>"><?= $esPendiente ? 'Responder' : 'Ver' ?></a></td>
                            </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
                </div>
                <?= $pager->links('reclamos') ?>
            <?php endif; ?>
        </section>
    </main>

<footer>
    <div class="wrap footer-inner">
        Comedor Universitario · Secretaría de Bienestar Estudiantil
    </div>
</footer>

</body>
</html>