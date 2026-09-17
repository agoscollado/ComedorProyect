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
        return view('solicitud/nueva');
    }

    public function crear()
    {
        // Reglas de validación para los campos del formulario.
        $reglas = [
            // required: no puede venir vacío
            // in_list[...]: el valor tiene que ser EXACTAMENTE uno de estos.
            'tipo' => 'required|in_list[inscripcion,renovacion]',

            // uploaded[dni,certificado]: confirma que se subió un archivo en el campo "dni"
            // max_size[dni,2048]: tamaño máximo en KB (2048 KB = 2 MB)
            // ext_in[dni,...]: solo permite estas extensiones de archivo
            'dni'  => 'uploaded[dni]|max_size[dni,2048]|ext_in[dni,pdf,jpg,jpeg,png]|mime_in[dni,application/pdf,image/jpeg,image/png]' ,
            'certificado' => 'uploaded[certificado]|max_size[certificado,2048]|ext_in[certificado,pdf,jpg,jpeg,png]|mime_in[certificado,application/pdf,image/jpeg,image/png]',
        ];

        // $this->validate() corre las reglas de arriba contra lo que llegó en el request.
        // Si algo no cumple, devuelve false y frenamos, sin tocar la base de datos.
        if (!$this->validate($reglas)) {
            // withInput(): "recordá" lo que el usuario ya había tipeado (como el tipo elegido),
            // para no hacerlo empezar el formulario de cero.
            // with('errors', ...): mandamos el detalle de qué campo falló y por qué.
            return redirect()->back()->withInput()->with('errors', $this->validator->getErrors());
        }

        // Leemos el campo de texto normal con getPost()
        $tipo = $this->request->getPost('tipo');
        // los archivos con getFile(), que es distinto porque un archivo no es un simple string, sino un objeto con métodos propios.
        $dni = $this->request->getFile('dni');
        $certificado = $this->request->getFile('certificado');

        $solicitudModel = new SolicitudModel();
        $documentoModel = new DocumentoModel();

        // Conexión directa a la base, para poder usar transacciones (insertar varias cosas como un solo bloque, que se confirma o deshace entero).
        $db = \Config\Database::connect();

        // A partir de acá, todo insert/update queda "en pausa", no confirmado todavía.
        $db->transStart();

        $solicitudId = $solicitudModel->insert([
            // Usamos el usuario_id de la SESIÓN, no de un campo del formulario, así evitamos que alguien pueda mandar un usuario_id ajeno a mano.
            'usuario_id'     => session()->get('usuario_id'),
            'tipo'           => $tipo,
            'estado'         => 'recibida', // estado por defecto
            'fecha_creacion' => date('Y-m-d H:i:s'),
        ]);

        // Reutilizamos el mismo método privado para los dos archivos, en vez de repetir 2 veces el bloque de "generar nombre + mover + insertar".
        $this->guardarDocumento($documentoModel, $solicitudId, 'dni', $dni);
        $this->guardarDocumento($documentoModel, $solicitudId, 'certificado', $certificado);

        // Le decimos a la base "confirmá todo lo anterior de una, o si algo salió mal en el medio, deshacé todo (rollback automático)".
        $db->transComplete();

        // transStatus() nos dice si la transacción terminó bien o no.
        if ($db->transStatus() === false) {
            return redirect()->back()->with('error', 'No se pudo procesar la solicitud, intentá de nuevo');
        }

        return redirect()->to('/panel-estudiante')->with('exito', 'Documentación enviada correctamente');
    }

    // private: este método es de uso interno del Controller, nadie desde afuera (rutas, otros archivos) puede llamarlo directamente.
    // Cada parámetro tiene su tipo declarado (type hint), lo cual ayuda a evitar errores tontos como pasar un string donde se esperaba un objeto UploadedFile.
    private function guardarDocumento(DocumentoModel $documentoModel, int $solicitudId, string $tipoDocumento, UploadedFile $archivo)
    {
        // Generamos un nombre aleatorio y único para el archivo. Evita que dos estudiantes que suben "dni.pdf" se pisen entre sí, y evita que alguien adivine el nombre de un archivo ajeno.
        $nombreArchivo = $archivo->getRandomName();

        // WRITEPATH apunta siempre a la carpeta writable/ del proyecto, sin importar el sistema operativo o dónde esté instalado CodeIgniter.
        // Guardamos ACA (no en public/) para que el archivo no sea accesible directo por URL sin pasar por nuestro control de sesión/roles.
        $archivo->move(WRITEPATH . 'uploads', $nombreArchivo);

        $documentoModel->insert([
            'solicitud_id'   => $solicitudId,
            'tipo_documento' => $tipoDocumento,
            'ruta_archivo'   => $nombreArchivo,
            'fecha_carga'    => date('Y-m-d H:i:s'),
        ]);
    }
}