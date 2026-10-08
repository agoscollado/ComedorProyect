<?php

namespace App\Controllers;

use App\Controllers\BaseController;
use CodeIgniter\HTTP\ResponseInterface;

class PanelEstudiante extends BaseController
{
    public function index()
    {
        $solicitudModel = new \App\Models\SolicitudModel();
        $solicitudModel->actualizarEstadosVencidos(); //actualizamos los estados de las solicitudes que hayan vencido, para que se refleje en la vista del panel del estudiante.

        //filtramos las solicitudes del usuario logueado y las ordenamos por fecha de creación descendente, trayendo todas las solicitudes encontradas.
        $solicitudes = $solicitudModel->where('usuario_id', session()->get('usuario_id'))->orderBy('fecha_creacion', 'DESC')->findAll();
        //recorremos las solicitudes encontradas y calculamos el progreso de cada una según su estado, para poder mostrarlo en la vista.
        foreach ($solicitudes as &$solicitud) {
            $solicitud['progreso'] = $this->calcularProgreso($solicitud['estado']);
            $solicitud['badge'] = $solicitudModel->estadoBadge($solicitud['estado']);
        }
        //retornamos la vista del panel del estudiante, pasando las solicitudes encontradas para que se muestren en la vista.
        return view('panelEstudiante/index', [
            'solicitudes' => $solicitudes,
        ]);
    }

    private function calcularProgreso (string $estado) {
        //asignamos un número de paso según el estado de la solicitud, para poder mostrarlo en la barra de progreso.
        $pasoActivado = match ($estado) { //match es como un switch, pero devuelve un valor y no necesita break.
            'recibida' => 1,
            'en revision' => 2,
            default => 3, // 'aprobada' o 'rechazada', default es como un "else" para cualquier otro caso que no esté listado arriba.
        };

        //asignamos un color según el estado de la solicitud, para poder mostrarlo en la barra de progreso.
        $resultado_color = $estado === 'aprobada' ? 'verde' : ($estado === 'rechazada' ? 'rojo' : 'gris');

        //retornamos un array con los valores calculados para poder usarlos en la vista.
        return [
            'pasoActivado' => $pasoActivado,
            'resultado_color' => $resultado_color,
            ];
    }
}
