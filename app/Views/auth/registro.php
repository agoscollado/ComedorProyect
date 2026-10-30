<?php
$errores = session()->getFlashdata('errors');
$errores = is_array($errores) ? $errores : [];
$old = session()->getFlashdata('old');
$old = is_array($old) ? $old : [];
$error = session()->getFlashdata('error');
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="stylesheet" href="<?= base_url('css/estilos.css') ?>?v=<?= time() ?>">
    <title>Crear cuenta | Comedor Universitario</title>
</head>
<body class="panel-page">
    <header>
        <div class="wrap header-inner">
            <div class="brand">
                <img class="brand-logo" src="<?= base_url('images/comedor-logo.png') ?>" alt="">
                <div class="brand-text">Comedor Universitario
                    <span>Gestión de trámites en línea</span>
                </div>
            </div>
        </div>
    </header>

    <main class="auth-main registro-main">
        <section class="form-card registro-card" aria-labelledby="registro-titulo">
            <div class="registro-intro">
                <h1 id="registro-titulo">Crear cuenta de estudiante</h1>
                <p>Registrarte crea tu cuenta en el sistema. El beneficio del comedor se administra por separado.</p>
            </div>

            <?php if ($error): ?>
                <div class="mensaje-error" role="alert"><?= esc($error) ?></div>
            <?php endif; ?>

            <?php if ($errores !== []): ?>
                <div class="mensaje-error" role="alert">
                    <ul>
                        <?php foreach ($errores as $mensaje): ?>
                            <li><?= esc($mensaje) ?></li>
                        <?php endforeach; ?>
                    </ul>
                </div>
            <?php endif; ?>

            <form action="<?= base_url('registro') ?>" method="post">
                <?= csrf_field() ?>

                <div class="registro-campos">
                    <div class="registro-campo">
                        <label for="nombre">Nombre y apellido</label>
                        <input type="text" id="nombre" name="nombre" maxlength="100" autocomplete="name"
                            value="<?= esc($old['nombre'] ?? '') ?>" required>
                    </div>

                    <div class="registro-campo">
                        <label for="email">Correo electrónico</label>
                        <input type="email" id="email" name="email" maxlength="150" autocomplete="email"
                            value="<?= esc($old['email'] ?? '') ?>" required>
                    </div>

                    <div class="registro-campo">
                        <label for="dni">DNI</label>
                        <input type="text" id="dni" name="dni" maxlength="20" inputmode="numeric" autocomplete="off"
                            value="<?= esc($old['dni'] ?? '') ?>" required>
                    </div>

                    <div class="registro-campo">
                        <label for="carrera">Carrera <span>(opcional)</span></label>
                        <input type="text" id="carrera" name="carrera" maxlength="100" autocomplete="organization-title"
                            value="<?= esc($old['carrera'] ?? '') ?>">
                    </div>

                    <div class="registro-campo">
                        <label for="password">Contraseña</label>
                        <div class="password-input-wrap">
                            <input type="password" id="password" name="password" minlength="12"
                                autocomplete="new-password" aria-describedby="password-requisitos" required>
                            <button class="password-toggle" type="button" aria-label="Mostrar contraseña"
                                aria-pressed="false" aria-controls="password">
                                <svg viewBox="0 0 24 24" aria-hidden="true" focusable="false">
                                    <path d="M2 12s3.6-7 10-7 10 7 10 7-3.6 7-10 7S2 12 2 12Z"></path>
                                    <circle cx="12" cy="12" r="3"></circle>
                                    <path class="ojo-tachado" d="M3 3l18 18"></path>
                                </svg>
                            </button>
                        </div>
                        <div class="password-requisitos" id="password-requisitos" aria-live="polite">
                            <p>La contraseña debe incluir:</p>
                            <ul class="password-requisitos-lista">
                                <li data-requisito="longitud">Al menos 12 caracteres</li>
                                <li data-requisito="mayuscula">Una letra mayúscula</li>
                                <li data-requisito="numero">Un número</li>
                                <li data-requisito="especial">Un carácter especial (por ejemplo: !, @ o #)</li>
                            </ul>
                        </div>
                    </div>

                    <div class="registro-campo">
                        <label for="password_confirm">Repetir contraseña</label>
                        <div class="password-input-wrap">
                            <input type="password" id="password_confirm" name="password_confirm" minlength="12"
                                autocomplete="new-password" required>
                            <button class="password-toggle" type="button" aria-label="Mostrar contraseña"
                                aria-pressed="false" aria-controls="password_confirm">
                                <svg viewBox="0 0 24 24" aria-hidden="true" focusable="false">
                                    <path d="M2 12s3.6-7 10-7 10 7 10 7-3.6 7-10 7S2 12 2 12Z"></path>
                                    <circle cx="12" cy="12" r="3"></circle>
                                    <path class="ojo-tachado" d="M3 3l18 18"></path>
                                </svg>
                            </button>
                        </div>
                    </div>
                </div>

                <button class="registro-submit" type="submit">Crear cuenta</button>
            </form>

            <a class="auth-link registro-link" href="<?= base_url('login') ?>">Ya tengo una cuenta</a>
        </section>
    </main>

    <footer>
        <div class="wrap footer-inner">
            Comedor Universitario · Secretaría de Bienestar Estudiantil - 2026 ®
        </div>
    </footer>
    <script>
        const campoPassword = document.getElementById('password');
        const requisitosPassword = {
            longitud: (valor) => [...valor].length >= 12,
            mayuscula: (valor) => /\p{Lu}/u.test(valor),
            numero: (valor) => /\p{N}/u.test(valor),
            especial: (valor) => /[\p{P}\p{S}]/u.test(valor),
        };

        campoPassword.addEventListener('input', () => {
            for (const [nombre, cumple] of Object.entries(requisitosPassword)) {
                const requisito = document.querySelector(`[data-requisito="${nombre}"]`);
                const estaCumplido = cumple(campoPassword.value);
                requisito.classList.toggle('cumplido', estaCumplido);
                requisito.setAttribute('aria-label', `${requisito.textContent}: ${estaCumplido ? 'cumplido' : 'pendiente'}`);
            }
        });

        document.querySelectorAll('.password-toggle').forEach((boton) => {
            boton.addEventListener('click', () => {
                const campo = document.getElementById(boton.getAttribute('aria-controls'));
                const mostrar = campo.type === 'password';

                campo.type = mostrar ? 'text' : 'password';
                boton.classList.toggle('mostrar', mostrar);
                boton.setAttribute('aria-pressed', String(mostrar));
                boton.setAttribute('aria-label', `${mostrar ? 'Ocultar' : 'Mostrar'} contraseña`);
            });
        });
    </script>
</body>
</html>
