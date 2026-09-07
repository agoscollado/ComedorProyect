<h1>¡Bienvenido, estudiante!</h1>
<p>Sesion iniciada como: <?= session()->get('nombre') ?></p>
<a href="<?= base_url('logout') ?>">Cerrar sesión</a>