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

    <?= $this->include('partials/headerEstudiante') ?>

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