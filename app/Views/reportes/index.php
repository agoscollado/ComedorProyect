<?php
/** @var string $desde */
/** @var string $hasta */
/** @var string|null $error */
/** @var array $solicitudesPorEstado */
/** @var array $solicitudesPorTipo */
/** @var array $reclamosPorCategoria */
/** @var array $reclamosPorEstado */
/** @var array $resumenAcreditaciones */
/** @var array $estudiantesPorEstado */

$etiquetasEstadosSolicitud = [
    'recibida' => 'Recibida',
    'en revision' => 'En revisión',
    'aprobada' => 'Aprobada',
    'rechazada' => 'Rechazada',
];
$etiquetasTiposSolicitud = [
    'inscripcion' => 'Inscripción',
    'renovacion' => 'Renovación',
];
$etiquetasEstadosReclamo = [
    'pendiente' => 'Pendiente',
    'respondido' => 'Respondido',
];
$cantidadAcreditaciones = (int) ($resumenAcreditaciones['cantidad'] ?? 0);
$totalAcreditado = (float) ($resumenAcreditaciones['total'] ?? 0);
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="stylesheet" href="<?= base_url('css/estilos.css') ?>?v=<?= time() ?>">
    <title>Reportes | Comedor Universitario</title>
</head>
<body class="panel-page">
    <?= $this->include('partials/headerResponsable') ?>

    <main class="wrap">
        <section class="welcome">
            <div class="eyebrow">Panel del Responsable</div>
            <h1>Reportes</h1>
            <p>Consultá la actividad de solicitudes, reclamos y acreditaciones para el período elegido.</p>
            <?php if ($error !== null): ?>
                <div class="mensaje-error" role="alert"><?= esc($error) ?> Se muestran los últimos 90 días.</div>
            <?php endif; ?>
        </section>

        <section class="history reportes-filtro" aria-labelledby="titulo-periodo">
            <div class="history-head">
                <h2 id="titulo-periodo">Período del reporte</h2>
            </div>
            <form class="reportes-filtro-form" method="get" action="<?= base_url('reportes') ?>">
                <div class="reportes-campo">
                    <label for="desde">Desde</label>
                    <input type="date" id="desde" name="desde" value="<?= esc($desde) ?>" required>
                </div>
                <div class="reportes-campo">
                    <label for="hasta">Hasta</label>
                    <input type="date" id="hasta" name="hasta" value="<?= esc($hasta) ?>" required>
                </div>
                <button class="reportes-submit" type="submit">Aplicar período</button>
                <button class="reportes-submit reportes-export" type="submit" formaction="<?= base_url('reportes/exportar') ?>">
                    Descargar CSV
                </button>
            </form>
        </section>

        <section class="history reportes-seccion" aria-labelledby="titulo-solicitudes">
            <div class="history-head">
                <h2 id="titulo-solicitudes">Solicitudes</h2>
            </div>
            <div class="reportes-grid">
                <article class="tracker-card reportes-card">
                    <h3>Por estado</h3>
                    <dl class="reportes-lista">
                        <?php foreach ($etiquetasEstadosSolicitud as $estado => $etiqueta): ?>
                            <div class="reportes-fila">
                                <dt><?= esc($etiqueta) ?></dt>
                                <dd><?= esc($solicitudesPorEstado[$estado] ?? 0) ?></dd>
                            </div>
                        <?php endforeach; ?>
                    </dl>
                </article>
                <article class="tracker-card reportes-card">
                    <h3>Por tipo</h3>
                    <dl class="reportes-lista">
                        <?php foreach ($etiquetasTiposSolicitud as $tipo => $etiqueta): ?>
                            <div class="reportes-fila">
                                <dt><?= esc($etiqueta) ?></dt>
                                <dd><?= esc($solicitudesPorTipo[$tipo] ?? 0) ?></dd>
                            </div>
                        <?php endforeach; ?>
                    </dl>
                </article>
            </div>
        </section>

        <section class="history reportes-seccion" aria-labelledby="titulo-reclamos">
            <div class="history-head">
                <h2 id="titulo-reclamos">Reclamos</h2>
            </div>
            <div class="reportes-grid">
                <article class="tracker-card reportes-card">
                    <h3>Por categoría</h3>
                    <dl class="reportes-lista">
                        <?php foreach (\App\Models\ReclamoModel::CATEGORIAS as $categoria => $etiqueta): ?>
                            <div class="reportes-fila">
                                <dt><?= esc($etiqueta) ?></dt>
                                <dd><?= esc($reclamosPorCategoria[$categoria] ?? 0) ?></dd>
                            </div>
                        <?php endforeach; ?>
                    </dl>
                </article>
                <article class="tracker-card reportes-card">
                    <h3>Por estado</h3>
                    <dl class="reportes-lista">
                        <?php foreach ($etiquetasEstadosReclamo as $estado => $etiqueta): ?>
                            <div class="reportes-fila">
                                <dt><?= esc($etiqueta) ?></dt>
                                <dd><?= esc($reclamosPorEstado[$estado] ?? 0) ?></dd>
                            </div>
                        <?php endforeach; ?>
                    </dl>
                </article>
            </div>
        </section>

        <section class="history reportes-seccion" aria-labelledby="titulo-acreditaciones">
            <div class="history-head">
                <h2 id="titulo-acreditaciones">Acreditaciones</h2>
            </div>
            <div class="reportes-grid reportes-metricas">
                <article class="tracker-card reportes-metrica">
                    <div class="label">Cantidad en el período</div>
                    <p><?= esc((string) $cantidadAcreditaciones) ?></p>
                </article>
                <article class="tracker-card reportes-metrica">
                    <div class="label">Total acreditado</div>
                    <p>$ <?= esc(number_format($totalAcreditado, 2, ',', '.')) ?></p>
                </article>
            </div>
        </section>

        <section class="history reportes-seccion" aria-labelledby="titulo-beneficiarios">
            <div class="history-head">
                <h2 id="titulo-beneficiarios">Beneficiarios</h2>
            </div>
            <div class="reportes-grid reportes-metricas">
                <article class="tracker-card reportes-metrica">
                    <div class="label">Estudiantes activos</div>
                    <p><?= esc($estudiantesPorEstado['1'] ?? 0) ?></p>
                </article>
                <article class="tracker-card reportes-metrica">
                    <div class="label">Estudiantes inactivos</div>
                    <p><?= esc($estudiantesPorEstado['0'] ?? 0) ?></p>
                </article>
            </div>
        </section>
    </main>

    <footer>
        <div class="wrap footer-inner">
            Comedor Universitario · Secretaría de Bienestar Estudiantil - 2026 ®
        </div>
    </footer>
</body>
</html>
