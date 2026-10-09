<?php
/** @var array $acreditaciones */
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="stylesheet" href="<?= base_url('css/estilos.css') ?>?v=<?= time() ?>">
    <title>Mi chequera</title>
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
                <a class="nav-link" href="<?= base_url('tramites') ?>">Mis trámites</a>
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
            <h1>Mi chequera</h1>
            <p>Historial de montos que el comedor te acreditó. El saldo disponible para consumir se verifica en caja al momento de retirar la vianda.</p>
        </section>

        <section class="history">
            <div class="history-head">
                <h2>Acreditaciones</h2>
            </div>

            <?php if (empty($acreditaciones)): ?>
                <div class="tracker-card">
                    <p>Todavía no tenés acreditaciones registradas.</p>
                </div>
            <?php else: ?>
                <table class="history-table">
                    <thead>
                        <tr>
                            <th>Fecha</th>
                            <th>Monto acreditado</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($acreditaciones as $acreditacion): ?>
                            <tr>
                                <td><?= esc(date('d M Y', strtotime($acreditacion['fecha_acreditacion']))) ?></td>
                                <td>$ <?= esc(number_format($acreditacion['monto'], 2, ',', '.')) ?></td>
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