<?php

namespace App\Controllers;

use App\Controllers\BaseController;
use CodeIgniter\HTTP\ResponseInterface;

class PanelEstudiante extends BaseController
{
    public function index()
    {
        $solicitudModel = new \App\Models\SolicitudModel();

        //filtramos las solicitudes del usuario logueado y las ordenamos por fecha de creación descendente, trayendo todas las solicitudes encontradas.
        $solicitudes = $solicitudModel->where('usuario_id', session()->get('usuario_id'))->orderBy('fecha_creacion', 'DESC')->findAll();
        //recorremos las solicitudes encontradas y calculamos el progreso de cada una según su estado, para poder mostrarlo en la vista.
        foreach ($solicitudes as &$solicitud) {
            $solicitud['progreso'] = $this->calcularProgreso($solicitud['estado']);
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

        //también asignamos una clase y un texto para el badge que muestra el estado de la solicitud, para poder mostrarlo en la vista.
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
        //retornamos un array con los valores calculados para poder usarlos en la vista.
        return [
            'pasoActivado' => $pasoActivado,
            'resultado_color' => $resultado_color,
            'badgeClase' => $badgeClase,
            'badgeTexto' => $badgeTexto,
        ];
    }
}
