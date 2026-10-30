<?php

namespace App\Models;

use CodeIgniter\Model;

class AuthTokenModel extends Model
{
    protected $table = 'auth_tokens';
    protected $primaryKey = 'id';
    protected $returnType = 'array';
    protected $useSoftDeletes = false;
    protected $protectFields = true;
    protected $allowedFields = ['user_id', 'purpose', 'token_hash', 'expires_at', 'created_at'];
    protected $useTimestamps = false;

    public function crearToken(int $userId, string $purpose, int $vigenciaSegundos): string
    {
        $ahora = date('Y-m-d H:i:s');
        $this->where('expires_at <', $ahora)->delete();
        $this->where('user_id', $userId)->where('purpose', $purpose)->delete();

        $token = bin2hex(random_bytes(32));
        $guardado = $this->insert([
            'user_id' => $userId,
            'purpose' => $purpose,
            'token_hash' => hash('sha256', $token),
            'expires_at' => date('Y-m-d H:i:s', time() + $vigenciaSegundos),
            'created_at' => $ahora,
        ]);

        if ($guardado === false) {
            throw new \RuntimeException('No se pudo crear el token de autenticación.');
        }

        return $token;
    }

    public function buscarTokenValido(string $token, string $purpose): ?array
    {
        if (! preg_match('/\A[a-f0-9]{64}\z/', $token)) {
            return null;
        }

        return $this->where('token_hash', hash('sha256', $token))
            ->where('purpose', $purpose)
            ->where('expires_at >=', date('Y-m-d H:i:s'))
            ->first();
    }

    public function verificarEmail(string $token): bool
    {
        return $this->consumirToken($token, 'verify_email', static function (int $userId): bool {
            return (new UsuarioModel())
                ->where('id', $userId)
                ->where('email_verified_at', null)
                ->set(['email_verified_at' => date('Y-m-d H:i:s')])
                ->update();
        });
    }

    public function restablecerPassword(string $token, string $passwordHash): bool
    {
        return $this->consumirToken($token, 'reset_password', function (int $userId) use ($passwordHash): bool {
            $actualizado = $this->db->table('usuarios')
                ->set('password', $passwordHash)
                ->set('auth_version', 'auth_version + 1', false)
                ->where('id', $userId)
                ->update();

            if (! $actualizado) {
                return false;
            }

            return $this->where('user_id', $userId)->delete() !== false;
        });
    }

    private function consumirToken(string $token, string $purpose, callable $actualizarCuenta): bool
    {
        if (! preg_match('/\A[a-f0-9]{64}\z/', $token)) {
            return false;
        }

        $db = $this->db;
        $db->transBegin();

        $registro = $this->where('token_hash', hash('sha256', $token))
            ->where('purpose', $purpose)
            ->where('expires_at >=', date('Y-m-d H:i:s'))
            ->first();

        if ($registro === null) {
            $db->transRollback();
            return false;
        }

        $eliminado = $this->where('id', $registro['id'])->delete();
        if (! $eliminado || $db->affectedRows() !== 1 || ! $actualizarCuenta((int) $registro['user_id'])) {
            $db->transRollback();
            return false;
        }

        if (! $db->transStatus()) {
            $db->transRollback();
            return false;
        }

        $db->transCommit();

        return true;
    }
}
