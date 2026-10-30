<?php

namespace App\Filters;

use App\Models\UsuarioModel;
use CodeIgniter\Filters\FilterInterface;
use CodeIgniter\HTTP\RequestInterface;
use CodeIgniter\HTTP\ResponseInterface;

class AuthFilter implements FilterInterface
{
    /**
     * @param RequestInterface $request
     * @param array|null       $arguments
     *
     * @return RequestInterface|ResponseInterface|string|void
     */
    public function before(RequestInterface $request, $arguments = null)
    {
        if (!session()->get('usuario_id')) {
            return redirect()->to('/login');
        }

        if ($arguments && session()->get('rol') !== $arguments[0]) {
            return redirect()->to('/login');
        }

        try {
            $usuario = (new UsuarioModel())->find(session()->get('usuario_id'));
        } catch (\Throwable $exception) {
            log_message('error', 'No se pudo verificar el usuario {id}: {error}', [
                'id' => session()->get('usuario_id'),
                'error' => $exception->getMessage(),
            ]);
            $this->limpiarSesion();

            return redirect()->to('/login')->with('error', 'No se pudo verificar tu cuenta. Intentá iniciar sesión de nuevo.');
        }

        if ($usuario === null) {
            $this->limpiarSesion();

            return redirect()->to('/login')->with('error', 'No se encontró tu cuenta. Intentá iniciar sesión de nuevo.');
        }

        if ($usuario['rol'] !== session()->get('rol')) {
            $this->limpiarSesion();

            return redirect()->to('/login')->with('error', 'Tu sesión ya no es válida. Iniciá sesión de nuevo.');
        }

        if (empty($usuario['email_verified_at'])) {
            $this->limpiarSesion();

            return redirect()->to('/login')
                ->with('error', 'Verificá tu correo electrónico antes de continuar.');
        }

        if ((int) $usuario['auth_version'] !== (int) session()->get('auth_version')) {
            $this->limpiarSesion();

            return redirect()->to('/login')
                ->with('error', 'Tu sesión ya no es válida. Iniciá sesión de nuevo.');
        }

        if ($arguments && $usuario['rol'] !== $arguments[0]) {
            return redirect()->to('/login');
        }
    }

    private function limpiarSesion(): void
    {
        session()->remove(['usuario_id', 'nombre', 'rol', 'auth_version']);
        session()->destroy();
    }

    /**
     * @param RequestInterface  $request
     * @param ResponseInterface $response
     * @param array|null        $arguments
     *
     * @return ResponseInterface|void
     */
    public function after(RequestInterface $request, ResponseInterface $response, $arguments = null)
    {
        //
    }
}
