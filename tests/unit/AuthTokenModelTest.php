<?php

use App\Models\AuthTokenModel;
use App\Models\UsuarioModel;
use CodeIgniter\Test\CIUnitTestCase;

/**
 * @internal
 */
final class AuthTokenModelTest extends CIUnitTestCase
{
    protected function setUp(): void
    {
        parent::setUp();

        $db = \Config\Database::connect();
        $forge = \Config\Database::forge($db);
        $forge->dropTable('auth_tokens', true);
        $forge->dropTable('usuarios', true);

        $forge->addField([
            'id' => [
                'type' => 'INT',
                'auto_increment' => true,
            ],
            'email_verified_at' => [
                'type' => 'DATETIME',
                'null' => true,
            ],
            'password' => [
                'type' => 'VARCHAR',
                'constraint' => 255,
                'null' => true,
            ],
            'auth_version' => [
                'type' => 'INT',
                'default' => 0,
            ],
        ]);
        $forge->addPrimaryKey('id');
        $forge->createTable('usuarios');

        $forge->addField([
            'id' => [
                'type' => 'INT',
                'auto_increment' => true,
            ],
            'user_id' => [
                'type' => 'INT',
            ],
            'purpose' => [
                'type' => 'VARCHAR',
                'constraint' => 32,
            ],
            'token_hash' => [
                'type' => 'CHAR',
                'constraint' => 64,
            ],
            'expires_at' => [
                'type' => 'DATETIME',
            ],
            'created_at' => [
                'type' => 'DATETIME',
            ],
        ]);
        $forge->addPrimaryKey('id');
        $forge->addUniqueKey('token_hash');
        $forge->createTable('auth_tokens');
    }

    protected function tearDown(): void
    {
        $forge = \Config\Database::forge();
        $forge->dropTable('auth_tokens', true);
        $forge->dropTable('usuarios', true);

        parent::tearDown();
    }

    public function testVerificationTokenIsStoredAsHashAndCanOnlyBeConsumedOnce(): void
    {
        $userId = (new UsuarioModel())->insert(['email_verified_at' => null]);
        $model = new AuthTokenModel();
        $token = $model->crearToken($userId, 'verify_email', 3600);

        $this->assertNotSame($token, $model->first()['token_hash']);
        $this->assertNotNull($model->buscarTokenValido($token, 'verify_email'));
        $this->assertTrue($model->verificarEmail($token));
        $this->assertFalse($model->verificarEmail($token));
        $this->assertNotEmpty((new UsuarioModel())->find($userId)['email_verified_at']);
    }

    public function testPasswordResetEscapesHashAndIncrementsSessionVersion(): void
    {
        $oldHash = password_hash('OldPassword123!', PASSWORD_DEFAULT);
        $userId = (new UsuarioModel())->insert([
            'email_verified_at' => '2026-01-01 00:00:00',
            'password' => $oldHash,
        ]);
        $model = new AuthTokenModel();
        $token = $model->crearToken($userId, 'reset_password', 3600);
        $newPassword = 'NewPassword456!';
        $newHash = password_hash($newPassword, PASSWORD_DEFAULT);

        $this->assertTrue($model->restablecerPassword($token, $newHash));

        $usuario = (new UsuarioModel())->find($userId);
        $this->assertTrue(password_verify($newPassword, $usuario['password']));
        $this->assertSame(1, (int) $usuario['auth_version']);
        $this->assertNull($model->buscarTokenValido($token, 'reset_password'));
        $this->assertSame(0, $model->where('user_id', $userId)->countAllResults());
    }

    public function testExpiredTokenCannotBeUsed(): void
    {
        $userId = (new UsuarioModel())->insert(['email_verified_at' => null]);
        $model = new AuthTokenModel();
        $token = $model->crearToken($userId, 'verify_email', 3600);

        \Config\Database::connect()
            ->table('auth_tokens')
            ->set('expires_at', '2000-01-01 00:00:00')
            ->where('token_hash', hash('sha256', $token))
            ->update();

        $this->assertNull($model->buscarTokenValido($token, 'verify_email'));
        $this->assertFalse($model->verificarEmail($token));
        $this->assertNull((new UsuarioModel())->find($userId)['email_verified_at']);
    }
}
