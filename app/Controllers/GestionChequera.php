<?php

namespace App\Controllers;

use App\Controllers\BaseController;
use CodeIgniter\HTTP\ResponseInterface;

class GestionChequera extends BaseController
{
    public function registrarAcreditacion($id)
    {
        $reglas = [
            'monto' => [
                'rules' => 'required|decimal|greater_than[0]',
                'errors' => [
                    'required' => 'El monto es obligatorio.',
                    'decimal' => 'El monto debe ser un número decimal.',
                    'greater_than' => 'El monto debe ser mayor que cero.',
                ],
            ],
        ];
        
        if (!$this->validate($reglas)) {
            return redirect()->back()->withInput()->with('error', implode(' ', $this->validator->getErrors()));
        }

        $acreditacionModel = new \App\Models\AcreditacionModel();
        $chequeraModel = new \App\Models\ChequeraModel();

        $chequera = $chequeraModel->where('usuario_id', $id)->first();

        if ($chequera) {
            $chequera_id = $chequera['id'];
        } else {
            $chequera_id = $chequeraModel->insert(['usuario_id' => $id, 'estado' => 'activa']);
        }

        $data = [
            'chequera_id' => $chequera_id,
            'monto' => $this->request->getPost('monto'),
            'fecha_acreditacion' => date('Y-m-d H:i:s'),
        ];

        if ($acreditacionModel->insert($data)) {
            return redirect()->to('/gestionBeneficiarios/listarUsuarios')->with('exito', 'Acreditación registrada exitosamente');
        } else {
            return redirect()->back()->with('error', 'Error al registrar la acreditación');
        }
    }
}
