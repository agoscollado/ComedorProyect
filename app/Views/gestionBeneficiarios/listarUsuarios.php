<?php
/** @var array $usuarios */
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="stylesheet" href="<?= base_url('css/estilos.css') ?>?v=<?= time() ?>">
    <title>Gestión de Beneficiarios</title>
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
                <h1>Beneficiarios activos</h1>
                <p>Estudiantes actualmente habilitados para retirar vianda. Podés dar de baja a quien corresponda.</p>

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

            <section class="history">
                <div class="history-head">
                    <h2>Historial de beneficiarios</h2>
                </div>
                <table class="history-table">
                    <thead>
                        <tr>
                            <th>Nombre</th>
                            <th>Email</th>
                        <th>DNI</th>
                        <th>Carrera</th>
                        <th>Acción</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($usuarios as $usuario): ?>
                        <tr>
                            <td><?= esc($usuario['nombre']) ?></td>
                            <td><?= esc($usuario['email']) ?></td>
                            <td><?= esc($usuario['dni']) ?></td>
                            <td><?= esc($usuario['carrera']) ?></td>
                            <td>
                                <form action="<?= base_url('gestionBeneficiarios/darDeBaja/' . $usuario['id']) ?>" method="post">
                                    <button type="submit" class="btn-baja">Dar de baja</button>
                                </form>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
            </section>
        </main>

        <footer>
        <div class="wrap footer-inner">
            Comedor Universitario · Secretaría de Bienestar Estudiantil
        </div>
    </footer>
</body>
</html>