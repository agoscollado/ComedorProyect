<h1>¡Bienvenido, responsable!</h1>
<p>Sesion iniciada como: <?= session()->get('nombre') ?></p>
<a href="<?= base_url('logout') ?>">Cerrar sesión</a>