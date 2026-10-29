<header>
    <div class="wrap header-inner">
        <div class="brand">
            <div class="brand-mark"></div>
            <div class="brand-text">Comedor Universitario
                <span>Gestión de trámites en línea</span>
            </div>
        </div>
        <nav>
            <div class="nav-links">
                <?php if (!url_is('panel-estudiante')): ?>
                    <a class="nav-link" href="<?= base_url('panel-estudiante') ?>">Mi panel</a>
                <?php endif; ?>
                <a class="nav-link" href="<?= base_url('tramites') ?>">Mis trámites</a>
                <a class="nav-link" href="<?= base_url('chequera') ?>">Chequera</a>
                <a class="nav-link" href="<?= base_url('reclamos') ?>">Reclamos</a>
            </div>
            <div class="user-chip">
                <div class="user-avatar"><?= esc(strtoupper(substr(session()->get('nombre'), 0, 1))) ?></div>
                <div>
                    <div class="user-name"><?= esc(session()->get('nombre')) ?></div>
                    <div class="user-role">Estudiante</div>
                </div>
            </div>
            <form class="logout-form" action="<?= base_url('logout') ?>" method="post">
                <?= csrf_field() ?>
                <button class="btn btn-ghost" type="submit">Cerrar sesión</button>
            </form>
        </nav>
    </div>
</header>