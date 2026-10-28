<?php

namespace App\Controllers;

use App\Controllers\BaseController;
use CodeIgniter\HTTP\ResponseInterface;

class GestionSolicitud extends BaseController
{
    public function listarPendientes()
    { //lista todas las solicitudes que están en estado "recibida" o "en revision"
        $solicitudModel = new \App\Models\SolicitudModel();
        $solicitudesPendientes = $solicitudModel
            ->select('solicitudes.*, usuarios.nombre AS estudiante_nombre')
            ->join('usuarios', 'usuarios.id = solicitudes.usuario_id')
            ->whereIn('estado', ['recibida', 'en revision'])
            ->orderBy('fecha_creacion', 'ASC')
            ->paginate(25, 'solicitudes');

        foreach ($solicitudesPendientes as &$solicitud) {
            $solicitud['badge'] = $solicitudModel->estadoBadge($solicitud['estado']);
        }
        unset($solicitud);

        return view('gestionSolicitudes/indexListarPendientes', [
            'solicitudes' => $solicitudesPendientes,
            'pager' => $solicitudModel->pager,
        ]);
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
            'badge' => $solicitudModel->estadoBadge($solicitud['estado']), // agrega la información del badge para mostrar el estado de la solicitud
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
    {
        return $this->resolverSolicitud($id, 'aprobada');
    }

    public function rechazar($id)
    {
        return $this->resolverSolicitud($id, 'rechazada');
    }

    private function resolverSolicitud($id, string $estado)
    {
        $solicitudModel = new \App\Models\SolicitudModel();
        $solicitud = $solicitudModel->find($id);

        if (!$solicitud) {
            return redirect()->to('/gestionSolicitudes/listarPendientes')->with('error', 'Solicitud no encontrada.');
        }

        $estadosPendientes = ['recibida', 'en revision'];
        if (!in_array($solicitud['estado'], $estadosPendientes, true)) {
            return redirect()->to('/gestionSolicitudes/listarPendientes')->with('error', 'La solicitud ya fue resuelta.');
        }

        $db = \Config\Database::connect();
        $transaccionAbierta = false;

        try {
            if (!$db->transBegin()) {
                throw new \RuntimeException('No se pudo iniciar la transacción.');
            }
            $transaccionAbierta = true;

            $actualizada = $db->table('solicitudes')
                ->where('id', $id)
                ->whereIn('estado', $estadosPendientes)
                ->update([
                    'estado' => $estado,
                    'fecha_resolucion' => date('Y-m-d H:i:s'),
                ]);

            if (!$actualizada || $db->affectedRows() !== 1) {
                $db->transRollback();
                $transaccionAbierta = false;

                return redirect()->to('/gestionSolicitudes/listarPendientes')->with('error', 'La solicitud ya fue resuelta.');
            }

            if ($estado === 'aprobada') {
                $usuarioActualizado = $db->table('usuarios')
                    ->where('id', $solicitud['usuario_id'])
                    ->update(['activo' => 1]);

                if (!$usuarioActualizado) {
                    throw new \RuntimeException('No se pudo activar al estudiante.');
                }
            }

            if (!$db->transStatus() || !$db->transCommit()) {
                throw new \RuntimeException('No se pudo guardar la decisión.');
            }

            $transaccionAbierta = false;
        } catch (\Throwable $exception) {
            if ($transaccionAbierta && !$db->transRollback()) {
                log_message('error', 'No se pudo revertir la decisión de solicitud {id}.', ['id' => $id]);
            }

            log_message('error', 'No se pudo resolver la solicitud {id}: {error}', [
                'id' => $id,
                'error' => $exception->getMessage(),
            ]);

            return redirect()->to('/gestionSolicitudes/listarPendientes')
                ->with('error', 'No se pudo actualizar la solicitud. Intentá de nuevo.');
        }

        $mensaje = $estado === 'aprobada'
            ? 'Solicitud aprobada exitosamente.'
            : 'Solicitud rechazada exitosamente.';

        return redirect()->to('/gestionSolicitudes/listarPendientes')->with('exito', $mensaje);
    }
}
