<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="referrer" content="no-referrer">
    <link rel="stylesheet" href="<?= base_url('css/estilos.css') ?>">
    <title>Crear nueva contraseña | Comedor Universitario</title>
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
        <section class="form-card registro-card" aria-labelledby="restablecer-titulo">
            <div class="registro-intro">
                <h1 id="restablecer-titulo">Crear nueva contraseña</h1>
                <p>Elegí una contraseña nueva para tu cuenta.</p>
            </div>

            <?php if (! $tokenValido): ?>
                <div class="mensaje-error" role="alert">El enlace no es válido o venció. Solicitá uno nuevo para continuar.</div>
                <a class="auth-link" href="<?= base_url('password/recuperar') ?>">Solicitar otro enlace</a>
            <?php else: ?>
                <?php if ($errores !== []): ?>
                    <div class="mensaje-error" role="alert">
                        <ul>
                            <?php foreach ($errores as $mensaje): ?>
                                <li><?= esc($mensaje) ?></li>
                            <?php endforeach; ?>
                        </ul>
                    </div>
                <?php endif; ?>

                <form action="<?= base_url('password/restablecer') ?>" method="post">
                    <?= csrf_field() ?>
                    <input type="hidden" name="token" value="<?= esc($token) ?>">

                    <div class="registro-campos">
                        <div class="registro-campo">
                            <label for="password">Nueva contraseña</label>
                            <input type="password" id="password" name="password" minlength="12"
                                autocomplete="new-password" aria-describedby="password-requisitos" required>
                            <div class="password-requisitos" id="password-requisitos">
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
                            <input type="password" id="password_confirm" name="password_confirm" minlength="12"
                                autocomplete="new-password" required>
                        </div>
                    </div>
                    <button class="registro-submit" type="submit">Guardar contraseña</button>
                </form>
            <?php endif; ?>
        </section>
    </main>

    <footer>
        <div class="wrap footer-inner">
            Comedor Universitario · Secretaría de Bienestar Estudiantil - 2026 ®
        </div>
    </footer>
    <script>
        const campoPassword = document.getElementById('password');
        if (campoPassword) {
            const requisitos = {
                longitud: (valor) => [...valor].length >= 12,
                mayuscula: (valor) => /\p{Lu}/u.test(valor),
                numero: (valor) => /\p{N}/u.test(valor),
                especial: (valor) => /[\p{P}\p{S}]/u.test(valor),
            };

            campoPassword.addEventListener('input', () => {
                for (const [nombre, cumple] of Object.entries(requisitos)) {
                    const elemento = document.querySelector(`[data-requisito="${nombre}"]`);
                    const estaCumplido = cumple(campoPassword.value);
                    elemento.classList.toggle('cumplido', estaCumplido);
                    elemento.setAttribute('aria-label', `${elemento.textContent}: ${estaCumplido ? 'cumplido' : 'pendiente'}`);
                }
            });
        }
    </script>
</body>
</html>
