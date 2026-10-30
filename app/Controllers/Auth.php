<?php

namespace App\Controllers;

use App\Controllers\BaseController;
use App\Models\AuthTokenModel;
use App\Models\UsuarioModel;
use CodeIgniter\Database\Exceptions\DatabaseException;
use Throwable;

class Auth extends BaseController
{
    public function mostrarFormLogin()
    {
        return view('auth/login');
    }

    public function mostrarFormRegistro()
    {
        return view('auth/registro');
    }

    public function mostrarFormVerificacion(string $token)
    {
        $this->response
            ->setHeader('Cache-Control', 'no-store, private')
            ->setHeader('Referrer-Policy', 'no-referrer');

        return view('auth/verificarEmail', [
            'token' => $token,
            'tokenValido' => (new AuthTokenModel())->buscarTokenValido($token, 'verify_email') !== null,
        ]);
    }

    public function confirmarEmail()
    {
        $token = $this->request->getPost('token');
        if (! is_string($token)) {
            return redirect()->to('/login')->with('error', 'El enlace no es válido o venció. Solicitá uno nuevo.');
        }

        try {
            $verificado = (new AuthTokenModel())->verificarEmail($token);
        } catch (Throwable $exception) {
            log_message('error', 'No se pudo confirmar un correo: {error}', [
                'error' => $exception->getMessage(),
            ]);

            return redirect()->to('/login')->with('error', 'No se pudo confirmar el correo. Intentá nuevamente.');
        }

        if (! $verificado) {
            return redirect()->to('/verificacion/reenviar')
                ->with('error', 'El enlace no es válido o venció. Podés solicitar uno nuevo.');
        }

        return redirect()->to('/login')
            ->with('exito', 'Correo verificado. Ya podés iniciar sesión.');
    }

    public function mostrarFormReenvioVerificacion()
    {
        return view('auth/reenviarVerificacion');
    }

    public function reenviarVerificacion()
    {
        $email = $this->request->getPost('email');
        $permitido = $this->permitirSolicitudAuth('verificacion', is_string($email) ? $email : '');
        if ($permitido && is_string($email)) {
            $email = strtolower(trim($email));
            if (filter_var($email, FILTER_VALIDATE_EMAIL) !== false) {
                try {
                    $usuario = (new UsuarioModel())->where('email', $email)->first();
                    if ($usuario !== null && empty($usuario['email_verified_at'])) {
                        $this->enviarVerificacion((int) $usuario['id'], $email, $usuario['nombre']);
                    }
                } catch (Throwable $exception) {
                    log_message('error', 'No se pudo reenviar la verificación de correo: {error}', [
                        'error' => $exception->getMessage(),
                    ]);
                }
            }
        }

        return redirect()->to('/login')->with(
            'exito',
            'Si la cuenta necesita verificar el correo, el sistema intentará enviar un nuevo enlace. Revisá también la carpeta de spam.'
        );
    }

    public function mostrarFormRecuperacion()
    {
        return view('auth/recuperarPassword');
    }

    public function solicitarRecuperacion()
    {
        $email = $this->request->getPost('email');
        $permitido = $this->permitirSolicitudAuth('recuperacion', is_string($email) ? $email : '');
        if ($permitido && is_string($email)) {
            $email = strtolower(trim($email));
            if (filter_var($email, FILTER_VALIDATE_EMAIL) !== false) {
                $usuario = (new UsuarioModel())->where('email', $email)->first();
                if ($usuario !== null) {
                    try {
                        $token = (new AuthTokenModel())->crearToken((int) $usuario['id'], 'reset_password', 3600);
                        $url = site_url('password/restablecer/' . $token);
                        $this->enviarCorreoAuth(
                            (int) $usuario['id'],
                            $email,
                            'Restablecer contraseña',
                            "Recibimos una solicitud para restablecer la contraseña de tu cuenta.\n\n"
                                . "Para elegir una nueva contraseña, abrí este enlace dentro de la próxima hora:\n"
                                . $url
                                . "\n\nSi no solicitaste este cambio, ignorá este correo."
                        );
                    } catch (Throwable $exception) {
                        log_message('error', 'No se pudo iniciar la recuperación para la cuenta {id}: {error}', [
                            'id' => $usuario['id'],
                            'error' => $exception->getMessage(),
                        ]);
                    }
                }
            }
        }

        return redirect()->to('/password/recuperar')->with(
            'exito',
            'Si existe una cuenta asociada a ese correo, el sistema intentará enviar instrucciones. Revisá también la carpeta de spam.'
        );
    }

    public function mostrarFormRestablecimiento(string $token)
    {
        $this->response
            ->setHeader('Cache-Control', 'no-store, private')
            ->setHeader('Referrer-Policy', 'no-referrer');

        return view('auth/restablecerPassword', [
            'token' => $token,
            'tokenValido' => (new AuthTokenModel())->buscarTokenValido($token, 'reset_password') !== null,
            'errores' => [],
        ]);
    }

    public function restablecerPassword()
    {
        $this->response
            ->setHeader('Cache-Control', 'no-store, private')
            ->setHeader('Referrer-Policy', 'no-referrer');

        $token = $this->request->getPost('token');
        $password = $this->request->getPost('password');
        $passwordConfirm = $this->request->getPost('password_confirm');

        if (! is_string($token) || ! is_string($password) || ! is_string($passwordConfirm)) {
            return $this->vistaRestablecimientoInvalida();
        }

        $errores = $this->erroresPassword($password);
        if ($password === '') {
            $errores[] = 'La contraseña es obligatoria.';
        }
        if ($passwordConfirm !== $password) {
            $errores[] = 'Las contraseñas no coinciden.';
        }

        if ($errores !== []) {
            return view('auth/restablecerPassword', [
                'token' => $token,
                'tokenValido' => (new AuthTokenModel())->buscarTokenValido($token, 'reset_password') !== null,
                'errores' => $errores,
            ]);
        }

        $hash = password_hash($password, PASSWORD_DEFAULT);
        if ($hash === false) {
            throw new \RuntimeException('No se pudo proteger la contraseña.');
        }

        try {
            $actualizado = (new AuthTokenModel())->restablecerPassword($token, $hash);
        } catch (Throwable $exception) {
            log_message('error', 'No se pudo restablecer una contraseña: {error}', [
                'error' => $exception->getMessage(),
            ]);

            return redirect()->to('/password/recuperar')
                ->with('error', 'No se pudo actualizar la contraseña. Solicitá un nuevo enlace.');
        }

        if (! $actualizado) {
            return redirect()->to('/password/recuperar')
                ->with('error', 'El enlace no es válido o venció. Solicitá uno nuevo.');
        }

        return redirect()->to('/login')
            ->with('exito', 'La contraseña se actualizó. Ya podés iniciar sesión.');
    }

    public function registrarEstudiante()
    {
        $throttleKey = 'registro-' . hash('sha256', $this->request->getIPAddress());
        if (! service('throttler')->check($throttleKey, 10, 900)) {
            return redirect()->back()
                ->with('error', 'Se alcanzó el límite de intentos. Esperá unos minutos antes de volver a intentarlo.');
        }

        $campos = ['nombre', 'email', 'dni', 'carrera', 'password', 'password_confirm'];
        $entrada = $this->request->getPost($campos);
        $datos = [];
        $errores = [];

        foreach ($campos as $campo) {
            $valor = $entrada[$campo] ?? '';
            if (! is_string($valor)) {
                $errores[$campo] = 'El dato ingresado no es válido.';
                $datos[$campo] = '';
                continue;
            }

            $datos[$campo] = in_array($campo, ['password', 'password_confirm'], true)
                ? $valor
                : trim($valor);
        }

        $datos['email'] = strtolower($datos['email']);
        $reglas = [
            'nombre' => 'required|max_length[100]',
            'email' => 'required|valid_email|max_length[150]|is_unique[usuarios.email]',
            'dni' => 'required|max_length[20]|is_unique[usuarios.dni]',
            'carrera' => 'permit_empty|max_length[100]',
            'password' => 'required',
            'password_confirm' => 'required|matches[password]',
        ];
        $mensajes = [
            'email' => [
                'is_unique' => 'No se pudo completar el registro con esos datos. Revisá la información o iniciá sesión si ya tenés una cuenta.',
            ],
            'dni' => [
                'is_unique' => 'No se pudo completar el registro con esos datos. Revisá la información o iniciá sesión si ya tenés una cuenta.',
            ],
        ];

        if (! $this->validate($reglas, $mensajes, $datos)) {
            $errores = array_merge($errores, $this->validator->getErrors());
        }

        $errores = array_merge($errores, $this->erroresPassword($datos['password']));

        if ($errores !== []) {
            return redirect()->back()
                ->with('errors', $errores)
                ->with('old', array_intersect_key($datos, array_flip(['nombre', 'email', 'dni', 'carrera'])));
        }

        $hash = password_hash($datos['password'], PASSWORD_DEFAULT);
        if ($hash === false) {
            throw new \RuntimeException('No se pudo proteger la contraseña para el registro.');
        }

        $usuarioModel = new UsuarioModel();
        try {
            $usuarioId = $usuarioModel->insert([
                'nombre' => $datos['nombre'],
                'email' => $datos['email'],
                'password' => $hash,
                'rol' => 'estudiante',
                'dni' => $datos['dni'],
                'carrera' => $datos['carrera'] === '' ? null : $datos['carrera'],
                'activo' => 0,
                'email_verified_at' => null,
            ], true);
        } catch (DatabaseException $exception) {
            if (in_array((string) $exception->getCode(), ['1062', '23000'], true)) {
                return redirect()->back()
                    ->with('error', 'No se pudo completar el registro con esos datos. Revisá la información o iniciá sesión si ya tenés una cuenta.')
                    ->with('old', array_intersect_key($datos, array_flip(['nombre', 'email', 'dni', 'carrera'])));
            }

            log_message('error', 'No se pudo registrar una cuenta de estudiante: {error}', [
                'error' => $exception->getMessage(),
            ]);

            return redirect()->back()
                ->with('error', 'No se pudo crear la cuenta. Intentá nuevamente más tarde.')
                ->with('old', array_intersect_key($datos, array_flip(['nombre', 'email', 'dni', 'carrera'])));
        }

        if ($usuarioId === false) {
            log_message('error', 'El modelo no pudo guardar una cuenta de estudiante.');
            return redirect()->back()
                ->with('error', 'No se pudo crear la cuenta. Intentá nuevamente más tarde.')
                ->with('old', array_intersect_key($datos, array_flip(['nombre', 'email', 'dni', 'carrera'])));
        }

        try {
            $this->enviarVerificacion((int) $usuarioId, $datos['email'], $datos['nombre']);
        } catch (Throwable $exception) {
            log_message('error', 'No se pudo iniciar la verificación de correo para la cuenta {id}: {error}', [
                'id' => $usuarioId,
                'error' => $exception->getMessage(),
            ]);
        }

        return redirect()->to('/login')
            ->with('exito', 'Si se creó la cuenta, queda pendiente de verificación. El sistema intentará enviar un enlace; revisá también la carpeta de spam.');
    }

    public function procesarLogin()
    {
        $email = $this->request->getPost('email');
        $password = $this->request->getPost('password');
        $rol = $this->request->getPost('rol');
        $ipKey = 'login-ip-' . hash('sha256', $this->request->getIPAddress());
        $mensajeError = 'Email, contraseña o rol incorrectos.';

        if (! service('throttler')->check($ipKey, 60, 900)) {
            return redirect()->back()
                ->with('error', 'Se alcanzó el límite de intentos. Esperá unos minutos antes de volver a intentarlo.');
        }

        if (! is_string($email) || ! is_string($password) || ! is_string($rol)) {
            return redirect()->back()->with('error', $mensajeError);
        }

        $emailNormalizado = strtolower(trim($email));
        $accountKey = 'login-account-' . hash(
            'sha256',
            $this->request->getIPAddress() . "\0" . $emailNormalizado
        );
        if (! service('throttler')->check($accountKey, 10, 900)) {
            return redirect()->back()
                ->with('error', 'Se alcanzó el límite de intentos. Esperá unos minutos antes de volver a intentarlo.');
        }

        $model = new UsuarioModel();
        $usuario = $model->where('email', $emailNormalizado)->first();

        if (
            $usuario === null
            || ! password_verify($password, $usuario['password'])
            || $usuario['rol'] !== $rol
            || empty($usuario['email_verified_at'])
        ) {
            return redirect()->back()->with('error', $mensajeError);
        }

        session()->regenerate(true);
        session()->set([
            'usuario_id' => $usuario['id'],
            'nombre' => $usuario['nombre'],
            'rol' => $usuario['rol'],
            'auth_version' => (int) $usuario['auth_version'],
        ]);

        if ($usuario['rol'] === 'estudiante') {
            return redirect()->to('/panel-estudiante');
        }

        return redirect()->to('/gestionSolicitudes/listarPendientes');
    }

    public function logout()
    {
        session()->destroy();

        return redirect()->to('/login');
    }

    private function enviarVerificacion(int $userId, string $email, string $nombre): bool
    {
        $token = (new AuthTokenModel())->crearToken($userId, 'verify_email', 86400);
        $url = site_url('verificar-email/' . $token);

        return $this->enviarCorreoAuth(
            $userId,
            $email,
            'Verificá tu correo electrónico',
            "Hola {$nombre},\n\n"
                . "Para verificar el correo de tu cuenta del Comedor Universitario, abrí este enlace dentro de las próximas 24 horas:\n"
                . $url
                . "\n\nSi no creaste esta cuenta, podés ignorar este mensaje."
        );
    }

    private function enviarCorreoAuth(int $userId, string $destinatario, string $asunto, string $mensaje): bool
    {
        $configuracion = config('Email');
        if (
            $configuracion->protocol !== 'smtp'
            || $configuracion->fromEmail === ''
            || $configuracion->SMTPHost === ''
            || $configuracion->SMTPUser === ''
            || $configuracion->SMTPPass === ''
        ) {
            log_message('error', 'No está configurado el envío SMTP para la cuenta {id}.', ['id' => $userId]);

            return false;
        }

        $correo = service('email');
        try {
            $correo->initialize($configuracion);
            $correo->setFrom($configuracion->fromEmail, $configuracion->fromName);
            $correo->setTo($destinatario);
            $correo->setSubject($asunto);
            $correo->setMessage($mensaje);

            if (! $correo->send(false)) {
                log_message('error', 'Falló la entrega de correo de autenticación para la cuenta {id}.', [
                    'id' => $userId,
                ]);

                return false;
            }

            return true;
        } catch (Throwable $exception) {
            log_message('error', 'Falló la entrega de correo de autenticación para la cuenta {id}: {error}', [
                'id' => $userId,
                'error' => $exception->getMessage(),
            ]);

            return false;
        } finally {
            $correo->clear(true);
        }
    }

    private function erroresPassword(string $password): array
    {
        $errores = [];
        if ($password !== '' && mb_strlen($password, 'UTF-8') < 12) {
            $errores[] = 'La contraseña debe tener al menos 12 caracteres.';
        }
        if (strlen($password) > 72) {
            $errores[] = 'La contraseña no puede superar los 72 bytes.';
        }
        if ($password !== '' && trim($password) === '') {
            $errores[] = 'La contraseña no puede estar formada solo por espacios.';
        }
        if ($password !== '' && preg_match('/\p{Lu}/u', $password) !== 1) {
            $errores[] = 'La contraseña debe incluir al menos una letra mayúscula.';
        }
        if ($password !== '' && preg_match('/\p{N}/u', $password) !== 1) {
            $errores[] = 'La contraseña debe incluir al menos un número.';
        }
        if ($password !== '' && preg_match('/[\p{P}\p{S}]/u', $password) !== 1) {
            $errores[] = 'La contraseña debe incluir al menos un carácter especial.';
        }

        return $errores;
    }

    private function vistaRestablecimientoInvalida()
    {
        return view('auth/restablecerPassword', [
            'token' => '',
            'tokenValido' => false,
            'errores' => [],
        ]);
    }

    private function permitirSolicitudAuth(string $operacion, string $email): bool
    {
        $throttler = service('throttler');
        $ip = hash('sha256', $this->request->getIPAddress());
        $permitidoPorIp = $throttler->check('auth-' . $operacion . '-ip-' . $ip, 10, 900);

        $emailNormalizado = strtolower(trim($email));
        if (filter_var($emailNormalizado, FILTER_VALIDATE_EMAIL) === false) {
            return $permitidoPorIp;
        }

        $claveCuenta = hash('sha256', $ip . "\0" . $emailNormalizado);
        $permitidoPorCuenta = $throttler->check(
            'auth-' . $operacion . '-cuenta-' . $claveCuenta,
            3,
            900
        );

        return $permitidoPorIp && $permitidoPorCuenta;
    }
}
