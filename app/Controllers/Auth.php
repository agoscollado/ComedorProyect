<?php

namespace App\Controllers;

use App\Controllers\BaseController;
use CodeIgniter\HTTP\ResponseInterface;
use App\Models\UsuarioModel;

class Auth extends BaseController
{
    public function mostrarFormLogin()
    {
        return view('auth/login');
    }

    public function procesarLogin()
    {
        $email = $this->request->getPost('email');
        $password = $this->request->getPost('password');
        $rol = $this->request->getPost('rol');

        $model = new UsuarioModel();
        $usuario = $model->where('email', $email)->first();

        if(is_null($usuario)) {
            return redirect()->back()->with('error', 'Email, contraseña o rol incorrectos');
        }

        if(!password_verify($password, $usuario['password'])) {
            return redirect()->back()->with('error', 'Email, contraseña o rol incorrectos');
        }

        if ($usuario['rol'] !== $rol) {
                return redirect()->back()->with('error', 'Email, contraseña o rol incorrectos');
            }
        //iniciar sesión
        session()->set([
            'usuario_id' => $usuario['id'],
            'nombre' => $usuario['nombre'],
            'rol' => $usuario['rol']
            ]);

        if($usuario['rol'] === 'estudiante') {
            return redirect()->to('/panel-estudiante');
        } else {
            return redirect()->to('/gestionSolicitudes/listarPendientes');
        }
    }

    public function logout()
    {
        session()->destroy();
        return redirect()->to('/login');
    }
}
