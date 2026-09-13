<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <!-- El ?v=<?= time() ?> evita que el navegador use una version vieja del CSS en cache -->
    <link rel="stylesheet" href="<?= base_url('css/estilos.css') ?>?v=<?= time() ?>">
    <title>Cargar Documentación</title>
</head>
<body>

    <div class="form-card">
        <h1>Cargar documentación</h1>

        <!--
            getFlashdata('errors') recupera el array de errores que mandamos
            desde el Controller con with('errors', $this->validator->getErrors()).
            Esto SOLO existe si hubo un error de validacion en el intento anterior.
        -->
        <?php if (session()->getFlashdata('errors')): ?>
            <div class="mensaje-error">
                <ul>
                    <?php foreach (session()->getFlashdata('errors') as $error): ?>
                        <!-- esc() escapa el texto para evitar inyeccion de HTML/JS malicioso -->
                        <li><?= esc($error) ?></li>
                    <?php endforeach; ?>
                </ul>
            </div>
        <?php endif; ?>

        <!-- Este es el mensaje de error genérico, el de la transacción fallida -->
        <?php if (session()->getFlashdata('error')): ?>
            <div class="mensaje-error">
                <?= esc(session()->getFlashdata('error')) ?>
            </div>
        <?php endif; ?>

        <!--
            enctype="multipart/form-data" es OBLIGATORIO para subir archivos.
            Sin esto, el navegador ni siquiera incluye los archivos en el request,
            aunque el usuario los haya seleccionado.
        -->
        <form action="<?= base_url('solicitud/crear') ?>" method="post" enctype="multipart/form-data">

            <label for="tipo">Tipo de trámite:</label>
            <select id="tipo" name="tipo" required>
                <option value="">Seleccioná una opción</option>
                <!--
                    old('tipo') recupera lo que el usuario habia elegido antes,
                    si la validacion fallo en el intento anterior (gracias a withInput()
                    en el Controller). Asi no tiene que volver a elegir todo de cero.
                -->
                <option value="inscripcion" <?= old('tipo') === 'inscripcion' ? 'selected' : '' ?>>Inscripción</option>
                <option value="renovacion" <?= old('tipo') === 'renovacion' ? 'selected' : '' ?>>Renovación</option>
            </select>

            <label for="dni">DNI (PDF o imagen):</label>
            <!--
                type="file" es el input especial para subir archivos.
                accept es solo una AYUDA VISUAL para el usuario (filtra que opciones
                le ofrece el explorador de archivos), pero NO es una validacion real:
                por eso en el Controller igual validamos con ext_in[] del lado del servidor.
            -->
            <input type="file" id="dni" name="dni" accept=".pdf,.jpg,.jpeg,.png" required>

            <label for="certificado">Certificado de alumno regular:</label>
            <input type="file" id="certificado" name="certificado" accept=".pdf,.jpg,.jpeg,.png" required>

            <button type="submit">Enviar documentación</button>

        </form>
    </div>

</body>
</html>