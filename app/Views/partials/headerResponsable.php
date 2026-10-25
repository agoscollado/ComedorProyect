<header>
    <div class="wrap header-inner">
        <div class="brand">
            <div class="brand-mark"></div>
            <div class="brand-text">Comedor Universitario
                <span>Gestión de trámites en línea</span>
            </div>
        </div>
        <nav>
            <a class="nav-link" href="<?= base_url('gestionSolicitudes/listarPendientes') ?>">Solicitudes</a>
            <a class="nav-link" href="<?= base_url('gestionBeneficiarios/listarUsuarios') ?>">Beneficiarios</a>
            <a class="nav-link" href="<?= base_url('gestionReclamos/listar') ?>">Reclamos</a>
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