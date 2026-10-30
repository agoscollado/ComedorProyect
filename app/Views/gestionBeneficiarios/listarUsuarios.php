<?php
/** @var array $usuarios */
/** @var \CodeIgniter\Pager\Pager $pager */
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
    <?= $this->include('partials/headerResponsable') ?>

        <main class="wrap">
            <section class="welcome">
                <div class="eyebrow">Panel del Responsable</div>
                <h1>Gestión de beneficios</h1>
                <p>Administrá el beneficio del comedor por separado del acceso de cada estudiante a su cuenta.</p>

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
                    <h2>Estudiantes</h2>
                </div>
                <?php if (empty($usuarios)): ?>
                    <div class="tracker-card">
                        <p>No hay estudiantes registrados.</p>
                    </div>
                <?php else: ?>
                <div class="table-responsive">
                <table class="history-table beneficiarios-table">
                    <thead>
                        <tr>
                            <th>Nombre</th>
                            <th>Email</th>
                            <th>DNI</th>
                            <th>Carrera</th>
                            <th>Beneficio</th>
                            <th>Chequera</th>
                            <th>Acción</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($usuarios as $usuario): ?>
                            <tr>
                                <td><?= esc($usuario['nombre']) ?></td>
                                <td><?= esc($usuario['email']) ?></td>
                                <td><?= esc($usuario['dni']) ?></td>
                                <td><?= esc($usuario['carrera'] ?? '') ?></td>
                                <td>
                                    <span class="estado-beneficio <?= (int) $usuario['activo'] === 1 ? 'activo' : 'inactivo' ?>">
                                        <?= (int) $usuario['activo'] === 1 ? 'Activo' : 'Inactivo' ?>
                                    </span>
                                </td>
                                <td>
                                    <?php if ((int) $usuario['activo'] === 1): ?>
                                        <form class="form-inline" action="<?= base_url('gestionChequera/registrarAcreditacion/' . $usuario['id']) ?>" method="post">
                                            <?= csrf_field() ?>
                                            <input type="number" name="monto" step="0.01" min="0.01" placeholder="Monto" aria-label="Monto a acreditar para <?= esc($usuario['nombre']) ?>" required>
                                            <button type="submit" class="btn-acreditar">Acreditar</button>
                                        </form>
                                    <?php else: ?>
                                        <span class="texto-secundario">No disponible</span>
                                    <?php endif; ?>
                                </td>
                                <td>
                                    <?php if ((int) $usuario['activo'] === 1): ?>
                                        <form class="form-beneficio" action="<?= base_url('gestionBeneficiarios/darDeBaja/' . $usuario['id']) ?>" method="post">
                                            <?= csrf_field() ?>
                                            <label class="solo-lectores" for="motivo-baja-<?= (int) $usuario['id'] ?>">Motivo de baja</label>
                                            <textarea id="motivo-baja-<?= (int) $usuario['id'] ?>" name="motivo" rows="2" minlength="5" maxlength="500" placeholder="Motivo de la baja" required></textarea>
                                            <button type="submit" class="btn-baja">Dar de baja</button>
                                        </form>
                                    <?php else: ?>
                                        <form class="form-beneficio" action="<?= base_url('gestionBeneficiarios/activar/' . $usuario['id']) ?>" method="post">
                                            <?= csrf_field() ?>
                                            <label class="solo-lectores" for="motivo-alta-<?= (int) $usuario['id'] ?>">Motivo de activación</label>
                                            <textarea id="motivo-alta-<?= (int) $usuario['id'] ?>" name="motivo" rows="2" minlength="5" maxlength="500" placeholder="Motivo de la activación" required></textarea>
                                            <button type="submit" class="btn-activar">Activar beneficio</button>
                                        </form>
                                    <?php endif; ?>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
                </div>
                <?= $pager->links('beneficiarios', 'comedorlistas') ?>
                <?php endif; ?>
            </section>

            <section class="history">
                <div class="history-head">
                    <h2>Últimos cambios de beneficio</h2>
                </div>
                <?php if (empty($historial)): ?>
                    <div class="tracker-card">
                        <p>Todavía no hay cambios de beneficio registrados.</p>
                    </div>
                <?php else: ?>
                    <div class="table-responsive">
                        <table class="history-table">
                            <thead>
                                <tr>
                                    <th>Fecha</th>
                                    <th>Estudiante</th>
                                    <th>Cambio</th>
                                    <th>Motivo</th>
                                    <th>Responsable</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php foreach ($historial as $cambio): ?>
                                    <tr>
                                        <td><?= esc($cambio['fecha_cambio']) ?></td>
                                        <td><?= esc($cambio['estudiante_nombre']) ?></td>
                                        <td><?= (int) $cambio['activo_anterior'] === 1 ? 'Activo' : 'Inactivo' ?>
                                            → <?= (int) $cambio['activo_nuevo'] === 1 ? 'Activo' : 'Inactivo' ?></td>
                                        <td><?= esc($cambio['motivo']) ?></td>
                                        <td><?= esc($cambio['responsable_nombre']) ?></td>
                                    </tr>
                                <?php endforeach; ?>
                            </tbody>
                        </table>
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