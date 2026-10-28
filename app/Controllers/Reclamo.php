<?php

namespace App\Controllers;

use App\Controllers\BaseController;
use App\Models\ReclamoModel;
use CodeIgniter\HTTP\ResponseInterface;

class Reclamo extends BaseController
{
    public function index()
    { //se obtiene el modelo de reclamos y se buscan todos los reclamos del usuario actual, ordenados por fecha de creación descendente.
        $reclamoModel = new ReclamoModel();
        $reclamos = $reclamoModel
            ->where('usuario_id', session()->get('usuario_id'))
            ->orderBy('fecha_creacion', 'DESC')
            ->findAll();

        foreach ($reclamos as &$reclamo) {
            $reclamo['badge'] = $reclamoModel->estadoBadge($reclamo['estado']);
            $reclamo['categoria_texto'] = ReclamoModel::CATEGORIAS[$reclamo['categoria']] ?? $reclamo['categoria'];
        }
        unset($reclamo);

        return view('reclamo/index', ['reclamos' => $reclamos]);
    }
    public function crear()
    {
        //reglas de validación para los campos del formulario.
        $reglas = [ 
            'categoria' => [
                'label' => 'Categoría',
                'rules' => 'required|in_list[' . implode(',', array_keys(ReclamoModel::CATEGORIAS)) . ']',
            ],
            'descripcion' => [
                'label' => 'Descripción',
                'rules' => 'required|string|min_length[10]|max_length[1000]',
            ],
        ];

        if (!$this->validate($reglas)) {
            return redirect()->back()->withInput()->with('errors', $this->validator->getErrors());
        }

        $categoria = $this->request->getPost('categoria');
        $descripcion = $this->request->getPost('descripcion');

        $reclamoModel = new \App\Models\ReclamoModel();

        //se prepara el arreglo de datos para insertar un nuevo reclamo en la base de datos.
        $data = [
            'usuario_id' => session()->get('usuario_id'),
            'categoria' => $categoria,
            'descripcion' => $descripcion,
            'estado' => 'pendiente',
            'fecha_creacion' => date('Y-m-d H:i:s'),
        ];

        if ($reclamoModel->insert($data) === false) {
            return redirect()->back()->withInput()->with('errors', ['error' => 'No se pudo crear el reclamo. Intente nuevamente.']);
        }

        return redirect()->to('/reclamos')->with('exito', 'Reclamo creado exitosamente.');
    }

    public function mostrarFormulario()
    {
        return view('reclamo/nuevo', ['categorias' => ReclamoModel::CATEGORIAS]);
    }
}
