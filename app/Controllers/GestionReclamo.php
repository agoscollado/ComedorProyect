<?php

namespace App\Controllers;

use App\Controllers\BaseController;
use App\Models\ReclamoModel;
use CodeIgniter\HTTP\ResponseInterface;

class GestionReclamo extends BaseController
{
    public function index()
    { //muestra la lista de reclamos según el estado seleccionado (pendiente o respondido)
        $estado = $this->request->getGet('estado');
        if (!in_array($estado, ['pendiente', 'respondido'], true)) {
            $estado = 'pendiente';
        }

        $reclamoModel = new ReclamoModel();
        $reclamos = $reclamoModel
            ->select('reclamos.*, usuarios.nombre AS estudiante_nombre')
            ->join('usuarios', 'usuarios.id = reclamos.usuario_id')
            ->where('reclamos.estado', $estado)
            ->orderBy('reclamos.fecha_creacion', $estado === 'pendiente' ? 'ASC' : 'DESC')
            ->findAll();

        foreach ($reclamos as &$reclamo) {
            $reclamo['badge'] = $reclamoModel->estadoBadge($reclamo['estado']);
            $reclamo['categoria_texto'] = ReclamoModel::CATEGORIAS[$reclamo['categoria']] ?? $reclamo['categoria'];
        }
        unset($reclamo);

        return view('gestionReclamos/index', [
            'reclamos' => $reclamos,
            'estadoActual' => $estado,
        ]);
    }

    public function verDetalle($id)
    {//muestra el detalle de un reclamo específico, incluyendo la respuesta si ya fue respondido
        $reclamoModel = new ReclamoModel();
        $reclamo = $reclamoModel
            ->select('reclamos.*, usuarios.nombre AS estudiante_nombre, usuarios.email AS estudiante_email, resp.nombre AS responsable_nombre')
            ->join('usuarios', 'usuarios.id = reclamos.usuario_id')
            ->join('usuarios AS resp', 'resp.id = reclamos.responsable_id', 'left')
            ->find($id);

        if ($reclamo === null) {
            return redirect()->to('/gestionReclamos/listar')->with('error', 'El reclamo no existe.');
        }

        $reclamo['badge'] = $reclamoModel->estadoBadge($reclamo['estado']);
        $reclamo['categoria_texto'] = ReclamoModel::CATEGORIAS[$reclamo['categoria']] ?? $reclamo['categoria'];

        return view('gestionReclamos/verDetalle', ['reclamo' => $reclamo]);
    }

    public function responder($id)
    {//permite al responsable responder un reclamo pendiente, validando la entrada y actualizando el estado del reclamo
        $reglas = [
            'respuesta' => [
                'rules' => 'required|min_length[10]|max_length[1000]',
                'errors' => [
                    'required' => 'La respuesta es obligatoria.',
                    'min_length' => 'La respuesta debe tener al menos 10 caracteres.',
                    'max_length' => 'La respuesta no puede superar los 1000 caracteres.',
                ],
            ],
        ];

        if (!$this->validate($reglas)) {
            return redirect()->back()->withInput()->with('errors', $this->validator->getErrors());
        }

        $reclamoModel = new ReclamoModel();
        if ($reclamoModel->find($id) === null) {
            return redirect()->to('/gestionReclamos/listar')->with('error', 'El reclamo no existe.');
        }

        $db = \Config\Database::connect();

        try {
            //solo actualiza si sigue pendiente: evita que dos Responsables (o un doble clic) pisen una respuesta ya enviada.
            $db->table('reclamos')
                ->where('id', $id)
                ->where('estado', 'pendiente')
                ->update([
                    'estado' => 'respondido',
                    'respuesta' => $this->request->getPost('respuesta'),
                    'responsable_id' => session()->get('usuario_id'),
                    'fecha_respuesta' => date('Y-m-d H:i:s'),
                ]);

            if ($db->affectedRows() !== 1) {
                return redirect()->to('/gestionReclamos/listar')->with('error', 'El reclamo ya fue respondido.');
            }
        } catch (\Throwable $exception) {
            log_message('error', 'No se pudo responder el reclamo {id}: {error}', [
                'id' => $id,
                'error' => $exception->getMessage(),
            ]);

            return redirect()->back()->withInput()->with('error', 'No se pudo guardar la respuesta. Intentá de nuevo.');
        }

        return redirect()->to('/gestionReclamos/listar')->with('exito', 'Respuesta enviada correctamente.');
    }
}
