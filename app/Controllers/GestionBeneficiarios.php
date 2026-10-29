<?php

namespace App\Controllers;

use App\Controllers\BaseController;
use CodeIgniter\HTTP\ResponseInterface;

class GestionBeneficiarios extends BaseController
{
    public function listarUsuarios()
    {
        $usuarioModel = new \App\Models\UsuarioModel();
        $usuarios = $usuarioModel
            ->where('activo', 1)
            ->where('rol', 'estudiante')
            ->orderBy('nombre', 'ASC')
            ->paginate(20, 'beneficiarios');

        return view('gestionBeneficiarios/listarUsuarios', [
            'usuarios' => $usuarios,
            'pager' => $usuarioModel->pager,
        ]);
    }

    public function darDeBaja($id)
    { //cambia el estado del usuario a inactivo, acceso restringido a Responsables vía filtro de ruta
        $usuarioModel = new \App\Models\UsuarioModel();
        $usuario = $usuarioModel->find($id);

        if (!$usuario) {
            return redirect()->to('/gestionBeneficiarios/listarUsuarios')->with('error', 'Usuario no encontrado.');
        }

        if ($usuario['rol'] !== 'estudiante') {
            return redirect()->to('/gestionBeneficiarios/listarUsuarios')->with('error', 'Solo se puede dar de baja a estudiantes.');
        }

        try {
            $actualizado = $usuarioModel->update($id, ['activo' => 0]);
        } catch (\Throwable $exception) {
            log_message('error', 'No se pudo dar de baja al estudiante ID {id}: {error}', [
                'id' => $id,
                'error' => $exception->getMessage(),
            ]);

            return redirect()->to('/gestionBeneficiarios/listarUsuarios')
                ->with('error', 'No se pudo dar de baja al estudiante. Intentá de nuevo.');
        }

        if (!$actualizado) {
            log_message('error', 'No se pudo dar de baja al estudiante ID {id}.', ['id' => $id]);

            return redirect()->to('/gestionBeneficiarios/listarUsuarios')
                ->with('error', 'No se pudo dar de baja al estudiante. Intentá de nuevo.');
        }

        return redirect()->to('/gestionBeneficiarios/listarUsuarios')->with('exito', 'Usuario dado de baja correctamente.');
    }
}
