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

        if ($arguments && $arguments[0] === 'estudiante') {
            try {
                $usuario = (new UsuarioModel())->find(session()->get('usuario_id'));
            } catch (\Throwable $exception) {
                log_message('error', 'No se pudo verificar el estado del usuario {id}: {error}', [
                    'id' => session()->get('usuario_id'),
                    'error' => $exception->getMessage(),
                ]);
                session()->remove(['usuario_id', 'nombre', 'rol']);
                session()->destroy();

                return redirect()->to('/login')->with('error', 'No se pudo verificar tu cuenta. Intentá iniciar sesión de nuevo.');
            }

            if ($usuario === null) {
                session()->remove(['usuario_id', 'nombre', 'rol']);
                session()->destroy();

                return redirect()->to('/login')->with('error', 'No se encontró tu cuenta. Intentá iniciar sesión de nuevo.');
            }
        }
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
