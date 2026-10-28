<?php

namespace App\Controllers;

use App\Controllers\BaseController;
use CodeIgniter\HTTP\ResponseInterface;

class TramitesPersonales extends BaseController
{
    public function index()
    {
        $solicitudModel = new \App\Models\SolicitudModel();
        $usuarioId = session()->get('usuario_id');
        $solicitudes = $solicitudModel->where('usuario_id', $usuarioId)->orderBy('fecha_creacion', 'DESC')->findAll();

        foreach ($solicitudes as &$solicitud) {
            $solicitud['badge'] = $solicitudModel->estadoBadge($solicitud['estado']);
        }

        return view('tramitesPersonales/index', ['solicitudes' => $solicitudes]);
    }
}
