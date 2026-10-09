<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="stylesheet" href="<?= base_url('css/estilos.css') ?>?v=<?= time() ?>">
    <title>Trámites Personales</title>
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
            <a class="nav-link" href="<?= base_url('panel-estudiante') ?>">Mi panel</a>
            <a class="nav-link" href="<?= base_url('tramites') ?>">Trámites personales</a>
            <a class="nav-link" href="<?= base_url('chequera') ?>">Chequera</a>
            <div class="user-chip">
                <div class="user-avatar"><?= esc(strtoupper(substr(session()->get('nombre'), 0, 1))) ?></div>
                <div>
                    <div class="user-name"><?= esc(session()->get('nombre')) ?></div>
                    <div class="user-role">Estudiante</div>
                </div>
            </div>
            <a class="btn btn-ghost" href="<?= base_url('logout') ?>">Cerrar sesión</a>
        </nav>
    </div>
</header>

<main class="wrap">
    <section class="welcome">
        <div class="eyebrow">Panel del Estudiante</div>
        <h1>Trámites personales</h1>
        <p>Historial completo de tus solicitudes de inscripción y renovación.</p>
    </section>

    <section class="history">
        <?php if (empty($solicitudes)): ?>
            <div class="tracker-card">
                <p>Todavía no cargaste ninguna solicitud.</p>
            </div>
        <?php else: ?>
            <table class="history-table">
                <thead>
                    <tr>
                        <th>Trámite</th>
                        <th>Tipo</th>
                        <th>Fecha de inicio</th>
                        <th>Fecha de resolución</th>
                        <th>Estado</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($solicitudes as $solicitud): ?>
                        <tr>
                            <td>#<?= esc(str_pad($solicitud['id'], 4, '0', STR_PAD_LEFT)) ?></td>
                            <td><?= esc(ucfirst($solicitud['tipo'])) ?></td>
                            <td><?= esc(date('d M Y', strtotime($solicitud['fecha_creacion']))) ?></td>
                            <td>
                                <?php if ($solicitud['fecha_resolucion']): ?>
                                    <?= esc(date('d M Y', strtotime($solicitud['fecha_resolucion']))) ?>
                                <?php else: ?>
                                    —
                                <?php endif; ?>
                            </td>
                            <td><span class="badge <?= esc($solicitud['badge']['clase']) ?>"><?= esc($solicitud['badge']['texto']) ?></span></td>
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