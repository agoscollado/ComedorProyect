<?php

use App\Filters\AuthFilter;
use App\Models\UsuarioModel;
use CodeIgniter\HTTP\RedirectResponse;
use CodeIgniter\Test\CIUnitTestCase;

/**
 * @internal
 */
final class AuthFilterTest extends CIUnitTestCase
{
    protected function setUp(): void
    {
        parent::setUp();

        $db = \Config\Database::connect();
        $forge = \Config\Database::forge($db);
        $forge->dropTable('usuarios', true);
        $forge->addField([
            'id' => [
                'type' => 'INT',
                'auto_increment' => true,
            ],
            'activo' => [
                'type' => 'BOOLEAN',
                'default' => false,
            ],
            'rol' => [
                'type' => 'VARCHAR',
                'constraint' => 20,
                'null' => true,
            ],
            'email_verified_at' => [
                'type' => 'DATETIME',
                'null' => true,
            ],
            'auth_version' => [
                'type' => 'INT',
                'default' => 0,
            ],
        ]);
        $forge->addPrimaryKey('id');
        $forge->createTable('usuarios');
    }

    protected function tearDown(): void
    {
        \Config\Database::connect()->disableForeignKeyChecks();
        \Config\Database::forge()->dropTable('usuarios', true);
        \Config\Database::connect()->enableForeignKeyChecks();

        parent::tearDown();
    }

    public function testActiveStudentCanUseStudentRoutes(): void
    {
        $usuarioId = $this->crearUsuario('estudiante', 1);
        session()->set(['usuario_id' => $usuarioId, 'rol' => 'estudiante', 'auth_version' => 0]);

        $resultado = (new AuthFilter())->before(service('request'), ['estudiante']);

        $this->assertNull($resultado);
    }

    public function testInactiveStudentCanUseStudentRoutes(): void
    {
        $usuarioId = $this->crearUsuario('estudiante', 0);
        session()->set(['usuario_id' => $usuarioId, 'rol' => 'estudiante', 'auth_version' => 0]);

        $resultado = (new AuthFilter())->before(service('request'), ['estudiante']);

        $this->assertNull($resultado);
        $this->assertSame($usuarioId, session()->get('usuario_id'));
    }

    public function testUnknownStudentSessionIsCleared(): void
    {
        session()->set(['usuario_id' => 999, 'rol' => 'estudiante']);

        $resultado = (new AuthFilter())->before(service('request'), ['estudiante']);

        $this->assertInstanceOf(RedirectResponse::class, $resultado);
        $this->assertNull(session()->get('usuario_id'));
    }

    public function testActiveResponsibleCanUseResponsibleRoutes(): void
    {
        $usuarioId = $this->crearUsuario('responsable', 1);
        session()->set(['usuario_id' => $usuarioId, 'rol' => 'responsable', 'auth_version' => 0]);

        $resultado = (new AuthFilter())->before(service('request'), ['responsable']);

        $this->assertNull($resultado);
    }

    public function testSessionIsClearedWhenAccountRoleChanges(): void
    {
        $usuarioModel = new UsuarioModel();
        $usuarioId = $this->crearUsuario('estudiante', 1);
        session()->set(['usuario_id' => $usuarioId, 'rol' => 'estudiante', 'auth_version' => 0]);
        $usuarioModel->update($usuarioId, ['rol' => 'responsable']);

        $resultado = (new AuthFilter())->before(service('request'), ['estudiante']);

        $this->assertInstanceOf(RedirectResponse::class, $resultado);
        $this->assertNull(session()->get('usuario_id'));
    }

    public function testResponsibleCannotUseStudentRoutes(): void
    {
        $usuarioId = $this->crearUsuario('responsable', 1);
        session()->set(['usuario_id' => $usuarioId, 'rol' => 'responsable', 'auth_version' => 0]);

        $resultado = (new AuthFilter())->before(service('request'), ['estudiante']);

        $this->assertInstanceOf(RedirectResponse::class, $resultado);
        $this->assertSame($usuarioId, session()->get('usuario_id'));
    }

    public function testUnverifiedAccountCannotUseProtectedRoutes(): void
    {
        $usuarioId = (new UsuarioModel())->insert([
            'activo' => 0,
            'rol' => 'estudiante',
            'auth_version' => 0,
        ]);
        session()->set(['usuario_id' => $usuarioId, 'rol' => 'estudiante', 'auth_version' => 0]);

        $resultado = (new AuthFilter())->before(service('request'), ['estudiante']);

        $this->assertInstanceOf(RedirectResponse::class, $resultado);
        $this->assertNull(session()->get('usuario_id'));
    }

    public function testPasswordResetVersionInvalidatesExistingSession(): void
    {
        $usuarioId = $this->crearUsuario('estudiante', 0);
        session()->set(['usuario_id' => $usuarioId, 'rol' => 'estudiante', 'auth_version' => 0]);
        (new UsuarioModel())->update($usuarioId, ['auth_version' => 1]);

        $resultado = (new AuthFilter())->before(service('request'), ['estudiante']);

        $this->assertInstanceOf(RedirectResponse::class, $resultado);
        $this->assertNull(session()->get('usuario_id'));
    }

    private function crearUsuario(string $rol, int $activo): int
    {
        return (new UsuarioModel())->insert([
            'activo' => $activo,
            'rol' => $rol,
            'email_verified_at' => '2026-01-01 00:00:00',
            'auth_version' => 0,
        ]);
    }
}
