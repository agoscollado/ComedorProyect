<?php
/** @var array $solicitudes */
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="stylesheet" href="<?= base_url('css/estilos.css') ?>?v=<?= time() ?>">
    <title>Solicitudes pendientes</title>
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
            <nav>
                <a class="nav-link" href="<?= base_url('gestionSolicitudes/listarPendientes') ?>">Solicitudes pendientes</a>
                <a class="nav-link" href="<?= base_url('gestionBeneficiarios/listarUsuarios') ?>">Bajas beneficiarios</a>
                <div class="user-chip">
                    <div class="user-avatar"><?= esc(strtoupper(substr(session()->get('nombre'), 0, 1))) ?></div>
                    <div>
                        <div class="user-name"><?= esc(session()->get('nombre')) ?></div>
                        <div class="user-role">Responsable</div>
                    </div>
                </div>
                <a class="btn btn-ghost" href="<?= base_url('logout') ?>">Cerrar sesión</a>
            </nav>
        </div>
    </header>

    <main class="wrap">
        <section class="welcome">
            <div class="eyebrow">Panel del Responsable</div>
            <h1>Hola, <?= esc(session()->get('nombre')) ?></h1>
            <p>Revisá la documentación cargada por los estudiantes y aprobá o rechazá cada trámite.</p>

            <?php if (session()->getFlashdata('exito')): ?>
                <div class="confirm-banner">✓ <?= esc(session()->getFlashdata('exito')) ?></div>
            <?php endif; ?>

            <?php if (session()->getFlashdata('error')): ?>
                <div class="mensaje-error"><?= esc(session()->getFlashdata('error')) ?></div>
            <?php endif; ?>
        </section>

        <section class="history">
            <div class="history-head">
                <h2>Trámites por revisar (<?= count($solicitudes) ?>)</h2>
            </div>

            <?php if (empty($solicitudes)): ?>
                <div class="tracker-card">
                    <p>No hay solicitudes pendientes por el momento.</p>
                </div>
            <?php else: ?>
                <table class="history-table">
                    <thead>
                        <tr>
                            <th>Trámite</th>
                            <th>Estudiante</th>
                            <th>Tipo</th>
                            <th>Fecha</th>
                            <th>Estado</th>
                            <th></th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($solicitudes as $solicitud): ?>
                            <tr>
                                <td>#<?= esc(str_pad($solicitud['id'], 4, '0', STR_PAD_LEFT)) ?></td>
                                <td><?= esc($solicitud['estudiante_nombre']) ?></td>
                                <td><?= esc(ucfirst($solicitud['tipo'])) ?></td>
                                <td><?= esc(date('d M Y', strtotime($solicitud['fecha_creacion']))) ?></td>
                                <td><span class="badge <?= esc($solicitud['badge']['clase']) ?>"><?= esc($solicitud['badge']['texto']) ?></span></td>
                                <td><a class="boton-link" href="<?= base_url('gestionSolicitudes/verDetalle/' . $solicitud['id']) ?>">Revisar</a></td>
                            </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
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