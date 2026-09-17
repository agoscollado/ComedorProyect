
<?php
/** @var array $solicitudes */
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="stylesheet" href="<?= base_url('css/estilos.css') ?>?v=<?= time() ?>">
    <title>Panel del Estudiante</title>
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
            <a class="nav-link" href="#">Mis trámites</a>
            <a class="nav-link" href="#">Chequera</a>
            <a class="nav-link" href="#">Reclamos</a>
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
        <h1>Hola, <?= esc(session()->get('nombre')) ?></h1>
        <p>Acá podés seguir el estado de tu documentación y gestionar tus trámites con el comedor.</p>

        <?php if (session()->getFlashdata('exito')): ?>
            <div class="confirm-banner">
                ✓ <?= esc(session()->getFlashdata('exito')) ?>
            </div>
        <?php endif; ?>

        <?php if (session()->getFlashdata('error')): ?>
            <div class="mensaje-error">
                <?= esc(session()->getFlashdata('error')) ?>
            </div>
        <?php endif; ?>
    </section>

    <section class="dashboard">
        <div>
            <?php if (!empty($solicitudes)): ?>
                <?php $ultima = $solicitudes[0]; ?>
                <div class="tracker-card">
                    <div class="tracker-head">
                        <div>
                            <div class="label">Tu solicitud más reciente</div>
                            <div class="title"><?= esc(ucfirst($ultima['tipo'])) ?></div>
                        </div>
                        <div class="id">#<?= esc(str_pad($ultima['id'], 4, '0', STR_PAD_LEFT)) ?></div>
                    </div>

                    <div class="steps">
                        <div class="step <?= $ultima['progreso']['pasoActivado'] === 1 ? 'active' : ($ultima['progreso']['pasoActivado'] > 1 ? '' : 'pending') ?>">
                            <div class="dot"><?= $ultima['progreso']['pasoActivado'] > 1 ? '✓' : '1' ?></div>
                            <div class="connector <?= $ultima['progreso']['pasoActivado'] < 2 ? 'off' : '' ?>"></div>
                            <div class="step-label">Recibida</div>
                        </div>
                        <div class="step <?= $ultima['progreso']['pasoActivado'] === 2 ? 'active' : ($ultima['progreso']['pasoActivado'] > 2 ? '' : 'pending') ?>">
                            <div class="dot"><?= $ultima['progreso']['pasoActivado'] > 2 ? '✓' : '2' ?></div>
                            <div class="connector <?= $ultima['progreso']['pasoActivado'] < 3 ? 'off' : '' ?>"></div>
                            <div class="step-label">En revisión</div>
                        </div>
                        <div class="step <?= $ultima['progreso']['pasoActivado'] === 3 ? $ultima['progreso']['resultado_color'] : 'pending' ?>">
                            <div class="dot">
                                <?= $ultima['progreso']['resultado_color'] === 'verde' ? '✓' : ($ultima['progreso']['resultado_color'] === 'rojo' ? '✕' : '3') ?>
                            </div>
                            <div class="step-label">Resultado</div>
                        </div>
                    </div>

                    <div class="tracker-detail">
                        <?php if ($ultima['estado'] === 'recibida'): ?>
                            Tu solicitud fue recibida y está en cola para revisión.
                        <?php elseif ($ultima['estado'] === 'en revision'): ?>
                            El personal del comedor está revisando tu documentación. Te avisamos ante cualquier novedad.
                        <?php elseif ($ultima['estado'] === 'aprobada'): ?>
                            <strong>¡Tu solicitud fue aprobada!</strong>
                        <?php else: ?>
                            Tu solicitud fue <strong>rechazada</strong>. Contactate con el comedor para más información.
                        <?php endif; ?>
                    </div>
                </div>
            <?php else: ?>
                <div class="tracker-card">
                    <p>Todavía no cargaste ninguna solicitud.</p>
                </div>
            <?php endif; ?>

            <div class="history">
                <div class="history-head">
                    <h2>Historial de trámites</h2>
                </div>
                <table class="history-table">
                    <thead>
                        <tr>
                            <th>Trámite</th>
                            <th>Tipo</th>
                            <th>Fecha</th>
                            <th>Estado</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($solicitudes as $solicitud): ?>
                            <tr>
                                <td>#<?= esc(str_pad($solicitud['id'], 4, '0', STR_PAD_LEFT)) ?></td>
                                <td><?= esc(ucfirst($solicitud['tipo'])) ?></td>
                                <td><?= esc(date('d M Y', strtotime($solicitud['fecha_creacion']))) ?></td>
                                <td><span class="badge <?= esc($solicitud['progreso']['badgeClase']) ?>"><?= esc($solicitud['progreso']['badgeTexto']) ?></span></td>
                            </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
        </div>

        <div class="side">
            <h2>Accesos rápidos</h2>

            <div class="action-card">
                <div class="num">01 — Documentación</div>
                <h3>Cargar nueva documentación</h3>
                <p>Subí tu certificado de alumno regular, DNI y demás documentación para inscripción o renovación.</p>
                <a class="card-link" href="<?= base_url('solicitud/nueva') ?>">Cargar documentación →</a>
            </div>

            <div class="action-card disabled">
                <div class="num">02 — Chequera</div>
                <h3>Historial de acreditaciones</h3>
                <p>Disponible en el próximo incremento del sistema.</p>
                <span class="card-link">Próximamente</span>
            </div>

            <div class="action-card disabled">
                <div class="num">03 — Reclamos</div>
                <h3>Reportar un problema</h3>
                <p>Disponible en el próximo incremento del sistema.</p>
                <span class="card-link">Próximamente</span>
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