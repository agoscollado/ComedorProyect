<?php

namespace App\Controllers;

use App\Controllers\BaseController;
use CodeIgniter\HTTP\ResponseInterface;

class GestionBeneficiarios extends BaseController
{
    public function listarUsuarios()
    {
        $usuarioModel = new \App\Models\UsuarioModel();
        $usuarios = $usuarioModel->where('activo', 1)->where('rol', 'estudiante')->findAll();
        return view('gestionBeneficiarios/listarUsuarios', ['usuarios' => $usuarios]);
    }

    public function darDeBaja($id)
    { //cambia el estado del usuario a inactivo, acceso restringido a Responsables vía filtro de ruta
        $usuarioModel = new \App\Models\UsuarioModel();
        $usuario = $usuarioModel->find($id);

        if (!$usuario) {
            return redirect()->to('/gestionBeneficiarios/listarUsuarios')->with('error', 'Usuario no encontrado.');
        }

        $usuarioModel->update($id, ['activo' => 0]); // Cambia el estado a inactivo
        return redirect()->to('/gestionBeneficiarios/listarUsuarios')->with('exito', 'Usuario dado de baja correctamente.');
    }
}
