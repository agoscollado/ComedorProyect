<header>
    <div class="wrap header-inner">
        <div class="brand">
            <img class="brand-logo" src="<?= base_url('images/comedor-logo.png') ?>" alt="">
            <div class="brand-text">Comedor Universitario
                <span>Gestión de trámites en línea</span>
            </div>
        </div>
        <nav>
            <div class="nav-links">
                <?php if (!url_is('gestionSolicitudes/listarPendientes')): ?>
                    <a class="nav-link" href="<?= base_url('gestionSolicitudes/listarPendientes') ?>">Solicitudes</a>
                <?php endif; ?>
                <a class="nav-link" href="<?= base_url('gestionBeneficiarios/listarUsuarios') ?>">Beneficiarios</a>
                <a class="nav-link" href="<?= base_url('gestionReclamos/listar') ?>">Reclamos</a>
                <a class="nav-link" href="<?= base_url('reportes') ?>">Reportes</a>
            </div>
            <div class="user-chip">
                <div class="user-avatar"><?= esc(strtoupper(substr(session()->get('nombre'), 0, 1))) ?></div>
                <div>
                    <div class="user-name"><?= esc(session()->get('nombre')) ?></div>
                    <div class="user-role">Responsable</div>
                </div>
            </div>
            <form class="logout-form" action="<?= base_url('logout') ?>" method="post">
                <?= csrf_field() ?>
                <button class="btn btn-ghost" type="submit">Cerrar sesión</button>
            </form>
        </nav>
    </div>
</header>