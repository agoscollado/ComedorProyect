<?php

namespace App\Controllers;

use App\Controllers\BaseController;
use App\Models\SolicitudModel;
use App\Models\DocumentoModel;
use CodeIgniter\Http\Files\UploadedFile;

class Solicitud extends BaseController
{
    public function mostrarFormulario()
    {
        if ($bloqueo = $this->bloqueoDeAcceso()) {
            return $bloqueo;
        }

        return view('solicitud/nueva');
    }

    public function crear()
    {
        $tipoSolicitud = $this->request->getPost('tipo');
        if ($bloqueo = $this->bloqueoDeAcceso(is_string($tipoSolicitud) ? $tipoSolicitud : null)) {
            return $bloqueo;
        }

        //Reglas de validación para los campos del formulario.
        $reglas = [
            // required: no puede venir vacío
            // in_list[...]: el valor tiene que ser EXACTAMENTE uno de estos.
            'tipo' => 'required|in_list[inscripcion,renovacion]',

            //uploaded[dni,certificado]: confirma que se subió un archivo en el campo "dni"
            //max_size[dni,2048]: tamaño máximo en KB (2048 KB = 2 MB)
            //ext_in[dni,...]: solo permite estas extensiones de archivo
            'dni'  => 'uploaded[dni]|max_size[dni,2048]|ext_in[dni,pdf,jpg,jpeg,png]|mime_in[dni,application/pdf,image/jpeg,image/png]' ,
            'certificado' => 'uploaded[certificado]|max_size[certificado,2048]|ext_in[certificado,pdf,jpg,jpeg,png]|mime_in[certificado,application/pdf,image/jpeg,image/png]',
        ];

        //$this->validate() corre las reglas de arriba contra lo que llegó en el request.
        //Si algo no cumple, devuelve false y frenamos, sin tocar la base de datos.
        if (!$this->validate($reglas)) {
            //withInput(): "recordá" lo que el usuario ya había tipeado (como el tipo elegido),
            //para no hacerlo empezar el formulario de cero.
            //with('errors', ...): mandamos el detalle de qué campo falló y por qué.
            return redirect()->back()->withInput()->with('errors', $this->validator->getErrors());
        }

        //Leemos el campo de texto normal con getPost()
        $tipo = $this->request->getPost('tipo');
        //los archivos con getFile(), que es distinto porque un archivo no es un simple string, sino un objeto con métodos propios.
        $dni = $this->request->getFile('dni');
        $certificado = $this->request->getFile('certificado');

        $solicitudModel = new SolicitudModel();
        $documentoModel = new DocumentoModel();

        //Conexión directa a la base, para poder usar transacciones (insertar varias cosas como un solo bloque, que se confirma o deshace entero).
        $db = \Config\Database::connect();

        $archivosGuardados = [];
        $transaccionCompletada = false;
        $transaccionAbierta = false;
        if (!$db->transBegin()) {
            return redirect()->back()->with('error', 'No se pudo iniciar el registro de la solicitud.');
        }
        $transaccionAbierta = true;

        try {
            $solicitudId = $solicitudModel->insert([
                'usuario_id'     => session()->get('usuario_id'),
                'tipo'           => $tipo,
                'estado'         => 'recibida',
                'fecha_creacion' => date('Y-m-d H:i:s'),
            ]);

            if (!$solicitudId) {
                throw new \RuntimeException('No se pudo registrar la solicitud.');
            }

            $this->guardarDocumento($documentoModel, $solicitudId, 'dni', $dni, $archivosGuardados);
            $this->guardarDocumento($documentoModel, $solicitudId, 'certificado', $certificado, $archivosGuardados);

            if (!$db->transStatus()) {
                throw new \RuntimeException('No se pudo guardar la documentación.');
            }

            if (!$db->transCommit()) {
                throw new \RuntimeException('No se pudo confirmar la solicitud.');
            }
            $transaccionAbierta = false;

            $transaccionCompletada = true;
        } catch (\Throwable $exception) {
            log_message('error', 'No se pudo crear la solicitud del usuario {usuario}: {error}', [
                'usuario' => session()->get('usuario_id'),
                'error' => $exception->getMessage(),
            ]);

            return redirect()->back()->with('error', 'No se pudo procesar la solicitud. Intentá de nuevo.');
        } finally {
            if (!$transaccionCompletada) {
                if ($transaccionAbierta) {
                    if (!$db->transRollback()) {
                        log_message('error', 'No se pudo revertir la transacción de solicitud del usuario {usuario}.', [
                            'usuario' => session()->get('usuario_id'),
                        ]);
                    }
                }

                foreach ($archivosGuardados as $rutaArchivo) {
                    if (is_file($rutaArchivo) && !unlink($rutaArchivo)) {
                        log_message('error', 'No se pudo eliminar el archivo de solicitud fallida: ' . $rutaArchivo);
                    }
                }
            }
        }

        return redirect()->to('/panel-estudiante')->with('exito', 'Documentación enviada correctamente');
    }

    private function bloqueoDeAcceso(?string $tipoSolicitud = null)
    {//Auth.php ya se encargó de que el usuario esté logueado, así que acá asumimos que sí lo está.
        $usuarioModel = new \App\Models\UsuarioModel();
        $usuario = $usuarioModel->find(session()->get('usuario_id'));

        if (!$usuario['activo']) {
            $solicitudModel = new SolicitudModel();
            $tieneSolicitudAprobada = $solicitudModel
                ->where('usuario_id', $usuario['id'])
                ->where('estado', 'aprobada')
                ->countAllResults() > 0;

            if ($tieneSolicitudAprobada || ($tipoSolicitud !== null && $tipoSolicitud !== 'inscripcion')) {
                return redirect()->to('/panel-estudiante')->with('error', 'Tu cuenta está inactiva. Contactá al comedor para más información.');
            }
        }

        $calendario = new \App\Libraries\CalendarioAcademico();
        if (!$calendario->solicitudesAbiertas(date('Y-m-d'))) {
            return redirect()->to('/panel-estudiante')->with('error', 'El plazo para presentar solicitudes está cerrado. Se vuelve a habilitar al comenzar el próximo cuatrimestre.');
        }

        return null;
    }

    //Cada parámetro tiene su tipo declarado (type hint), lo cual ayuda a evitar errores tontos como pasar un string donde se esperaba un objeto UploadedFile.
    private function guardarDocumento(DocumentoModel $documentoModel, int $solicitudId, string $tipoDocumento, UploadedFile $archivo, array &$archivosGuardados)
    {
        //Generamos un nombre aleatorio y único para el archivo. Evita que dos estudiantes que suben "dni.pdf" se pisen entre sí, y evita que alguien adivine el nombre de un archivo ajeno.
        $nombreArchivo = $archivo->getRandomName();

        //WRITEPATH apunta siempre a la carpeta writable/ del proyecto, sin importar el sistema operativo o dónde esté instalado CodeIgniter.
        //Guardamos ACA (no en public/) para que el archivo no sea accesible directo por URL sin pasar por nuestro control de sesión/roles.
        $archivo->move(WRITEPATH . 'uploads', $nombreArchivo);
        $rutaArchivo = WRITEPATH . 'uploads' . DIRECTORY_SEPARATOR . $nombreArchivo;
        $archivosGuardados[] = $rutaArchivo;

        if (!$documentoModel->insert([
            'solicitud_id'   => $solicitudId,
            'tipo_documento' => $tipoDocumento,
            'ruta_archivo'   => $nombreArchivo,
            'fecha_carga'    => date('Y-m-d H:i:s'),
        ])) {
            throw new \RuntimeException('No se pudieron registrar los documentos de la solicitud.');
        }
    }
}