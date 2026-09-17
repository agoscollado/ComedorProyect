<?php
/** @var array $solicitud */
/** @var array|null $estudiante */
/** @var array $documentos */
/** @var array $badge */
$etiquetasDocumento = [
    'dni' => 'DNI',
    'certificado' => 'Certificado de alumno regular',
];
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="stylesheet" href="<?= base_url('css/estilos.css') ?>?v=<?= time() ?>">
    <title>Solicitud #<?= esc(str_pad($solicitud['id'], 4, '0', STR_PAD_LEFT)) ?></title>
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
            <h1>Solicitud #<?= esc(str_pad($solicitud['id'], 4, '0', STR_PAD_LEFT)) ?></h1>
            <p><a class="card-link" href="<?= base_url('gestionSolicitudes/listarPendientes') ?>">← Volver a solicitudes pendientes</a></p>

            <?php if (session()->getFlashdata('error')): ?>
                <div class="mensaje-error"><?= esc(session()->getFlashdata('error')) ?></div>
            <?php endif; ?>
        </section>

        <section class="dashboard">
            <div>
                <div class="tracker-card">
                    <div class="tracker-head">
                        <div>
                            <div class="label">Estudiante</div>
                            <div class="title"><?= esc($estudiante['nombre'] ?? 'Estudiante no encontrado') ?></div>
                        </div>
                        <span class="badge <?= esc($badge['clase']) ?>"><?= esc($badge['texto']) ?></span>
                    </div>
                    <div class="tracker-detail">
                        <strong>Email:</strong> <?= esc($estudiante['email'] ?? '-') ?><br>
                        <strong>DNI:</strong> <?= esc($estudiante['dni'] ?? '-') ?><br>
                        <strong>Carrera:</strong> <?= esc($estudiante['carrera'] ?? '-') ?><br>
                        <strong>Tipo de trámite:</strong> <?= esc(ucfirst($solicitud['tipo'])) ?><br>
                        <strong>Fecha:</strong> <?= esc(date('d M Y', strtotime($solicitud['fecha_creacion']))) ?>
                    </div>
                </div>

                <div class="history" style="margin-top:20px;">
                    <div class="history-head">
                        <h2>Documentación cargada</h2>
                    </div>

                    <?php if (empty($documentos)): ?>
                        <div class="tracker-card"><p>El estudiante no cargó documentos.</p></div>
                    <?php else: ?>
                        <?php foreach ($documentos as $documento): ?>
                            <div class="action-card">
                                <h3><?= esc($etiquetasDocumento[$documento['tipo_documento']] ?? ucfirst($documento['tipo_documento'])) ?></h3>
                                <a class="card-link" target="_blank" rel="noopener" href="<?= base_url('gestionSolicitudes/verDocumento/' . $documento['id']) ?>">Ver documento →</a>
                            </div>
                        <?php endforeach; ?>
                    <?php endif; ?>
                </div>
            </div>

            <div class="side">
                <h2>Decisión</h2>
                <div class="action-card">
                    <form action="<?= base_url('gestionSolicitudes/aprobar/' . $solicitud['id']) ?>" method="post" style="margin-bottom:10px;">
                        <button type="submit" class="btn" style="width:100%; background-color: var(--color-primario); color: var(--color-fondo);">Aprobar</button>
                    </form>
                    <form action="<?= base_url('gestionSolicitudes/rechazar/' . $solicitud['id']) ?>" method="post">
                        <button type="submit" class="btn btn-ghost" style="width:100%;">Rechazar</button>
                    </form>
                </div>
            </div>
        </section>
    </main>

    <footer>
        <div class="wrap footer-inner">
            Comedor Universitario · Secretaría de Bienestar Estudiantil
        </div>
    </footer>
</body>
</html>