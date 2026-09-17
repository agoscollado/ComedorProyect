<?php

namespace App\Controllers;

use App\Controllers\BaseController;
use CodeIgniter\HTTP\ResponseInterface;

class GestionSolicitud extends BaseController
{
    public function index()
    {
        //
    }

    public function listarPendientes()
    { //lista todas las solicitudes que están en estado "recibida" o "en revision"
        $solicitudModel = new \App\Models\SolicitudModel();
        $usuarioModel = new \App\Models\UsuarioModel();

        $solicitudesPendientes = $solicitudModel
            ->whereIn('estado', ['recibida', 'en revision'])
            ->orderBy('fecha_creacion', 'ASC')
            ->findAll();

        foreach ($solicitudesPendientes as &$solicitud) {
            $estudiante = $usuarioModel->find($solicitud['usuario_id']);
            $solicitud['estudiante_nombre'] = $estudiante['nombre'] ?? 'Estudiante no encontrado';
            $solicitud['badge'] = $this->estadoBadge($solicitud['estado']);
        }

        return view('gestionSolicitudes/indexListarPendientes', ['solicitudes' => $solicitudesPendientes]);
    }

    public function verDetalle($id) {
        // muestra el detalle de la solicitud, incluyendo los documentos subidos y la información del estudiante que la envió
        $solicitudModel = new \App\Models\SolicitudModel();
        $documentoModel = new \App\Models\DocumentoModel();
        $usuarioModel = new \App\Models\UsuarioModel();

        $solicitud = $solicitudModel->find($id);
        if (!$solicitud) {
            return redirect()->to('/gestionSolicitudes/listarPendientes')->with('error', 'Solicitud no encontrada.');
        }

        $estudiante = $usuarioModel->find($solicitud['usuario_id']);
        $documentos = $documentoModel->where('solicitud_id', $id)->findAll();

        return view('gestionSolicitudes/verDetalle', [
            'solicitud' => $solicitud,
            'estudiante' => $estudiante,
            'documentos' => $documentos,
            'badge' => $this->estadoBadge($solicitud['estado']), // agrega la información del badge para mostrar el estado de la solicitud
        ]);
    }
    public function verDocumento($id) {
        // muestra un documento específico de la solicitud en el navegador (inline) en vez de forzar la descarga, acceso restringido a Responsables vía filtro de ruta
        $documentoModel = new \App\Models\DocumentoModel();

    $documento = $documentoModel->find($id);
    if (!$documento) {
        return redirect()->back()->with('error', 'Documento no encontrado.');
    }

    $rutaCompleta = WRITEPATH . 'uploads' . DIRECTORY_SEPARATOR . $documento['ruta_archivo'];

    if (!file_exists($rutaCompleta)) {
        return redirect()->back()->with('error', 'El archivo no se encuentra disponible.');
    }

    return $this->response
        ->setHeader('Content-Type', mime_content_type($rutaCompleta))
        ->setHeader('Content-Disposition', 'inline; filename="' . basename($rutaCompleta) . '"')
        ->setBody(file_get_contents($rutaCompleta));
    }

    public function aprobar($id)
    { //aprueba la solicitud y cambia su estado a "aprobada"
        $solicitudModel = new \App\Models\SolicitudModel();
        $solicitud = $solicitudModel->find($id);

        if (!$solicitud) {
            return redirect()->to('/gestionSolicitudes/listarPendientes')->with('error', 'Solicitud no encontrada.');
        }

        $solicitudModel->update($id, ['estado' => 'aprobada']);
        return redirect()->to('/gestionSolicitudes/listarPendientes')->with('exito', 'Solicitud aprobada exitosamente.');
    }

    public function rechazar($id)
    { //rechaza la solicitud y cambia su estado a "rechazada"
        $solicitudModel = new \App\Models\SolicitudModel();
        $solicitud = $solicitudModel->find($id);

        if (!$solicitud) {
            return redirect()->to('/gestionSolicitudes/listarPendientes')->with('error', 'Solicitud no encontrada.');
        }

        $solicitudModel->update($id, ['estado' => 'rechazada']);
        return redirect()->to('/gestionSolicitudes/listarPendientes')->with('exito', 'Solicitud rechazada exitosamente.');
    }

    private function estadoBadge(string $estado): array
    {
        $badgeClase = match ($estado) {
            'recibida' => 'badge-recibida',
            'en revision' => 'badge-en-revision',
            'aprobada' => 'badge-aprobada',
            'rechazada' => 'badge-rechazada',
        };

        $badgeTexto = match ($estado) {
            'recibida' => 'Recibida',
            'en revision' => 'En revisión',
            'aprobada' => 'Aprobada',
            'rechazada' => 'Rechazada',
        };

        return ['clase' => $badgeClase, 'texto' => $badgeTexto];
    }
}
