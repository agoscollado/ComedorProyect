<?php

namespace App\Controllers;

use App\Controllers\BaseController;
use CodeIgniter\HTTP\ResponseInterface;

class HistorialChequera extends BaseController
{
    public function index()
    {
        $chequeraModel = new \App\Models\ChequeraModel();
        $acreditacionModel = new \App\Models\AcreditacionModel();

        $usuarioId = session()->get('usuario_id');
        $chequera = $chequeraModel->where('usuario_id', $usuarioId)->first(); // obtenemos la chequera más reciente del usuario logueado
        
        $acreditaciones = [];

        if($chequera) {
            $acreditaciones = $acreditacionModel->where('chequera_id', $chequera['id'])->orderBy('fecha_acreditacion', 'DESC')->findAll(); // obtenemos todas las acreditaciones asociadas a la chequera del usuario logueado, ordenadas por fecha descendente
            }
        return view('chequera/historial', [
            'acreditaciones' => $acreditaciones,
        ]);
    }
}

