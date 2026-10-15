<?php

namespace App\Controllers;

use App\Controllers\BaseController;
use CodeIgniter\HTTP\ResponseInterface;
use App\Models\UsuarioModel;

class GestionChequera extends BaseController
{
    public function registrarAcreditacion($id)
    {
        $usuario = (new UsuarioModel())->find($id);
        if ($usuario === null || $usuario['rol'] !== 'estudiante' || !$usuario['activo']) {
            return redirect()->to('/gestionBeneficiarios/listarUsuarios')
                ->with('error', 'Solo se pueden acreditar chequeras de estudiantes activos.');
        }

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
        $db = \Config\Database::connect();
        $transaccionAbierta = false;

        try {
            if (!$db->transBegin()) {
                throw new \RuntimeException('No se pudo iniciar la transacción.');
            }
            $transaccionAbierta = true;

            $chequera = $chequeraModel->where('usuario_id', $id)->first();
            $chequeraId = $chequera['id'] ?? $chequeraModel->insert([
                'usuario_id' => $id,
                'estado' => 'activa',
            ]);

            if (!$chequeraId) {
                throw new \RuntimeException('No se pudo crear la chequera.');
            }

            $acreditacionId = $acreditacionModel->insert([
                'chequera_id' => $chequeraId,
                'monto' => $this->request->getPost('monto'),
                'fecha_acreditacion' => date('Y-m-d H:i:s'),
            ]);

            if (!$acreditacionId || !$db->transStatus() || !$db->transCommit()) {
                throw new \RuntimeException('No se pudo guardar la acreditación.');
            }

            $transaccionAbierta = false;
        } catch (\Throwable $exception) {
            if ($transaccionAbierta && !$db->transRollback()) {
                log_message('error', 'No se pudo revertir el registro de acreditación para el estudiante {id}.', ['id' => $id]);
            }

            log_message('error', 'No se pudo registrar una acreditación para el estudiante {id}: {error}', [
                'id' => $id,
                'error' => $exception->getMessage(),
            ]);

            return redirect()->back()->with('error', 'No se pudo registrar la acreditación. Intentá de nuevo.');
        }

        return redirect()->to('/gestionBeneficiarios/listarUsuarios')->with('exito', 'Acreditación registrada exitosamente.');
    }
}
