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
        $usuarioId = (new UsuarioModel())->insert(['activo' => 1, 'rol' => 'estudiante']);
        session()->set(['usuario_id' => $usuarioId, 'rol' => 'estudiante']);

        $resultado = (new AuthFilter())->before(service('request'), ['estudiante']);

        $this->assertNull($resultado);
    }

    public function testInactiveStudentCanUseStudentRoutes(): void
    {
        $usuarioId = (new UsuarioModel())->insert(['activo' => 0, 'rol' => 'estudiante']);
        session()->set(['usuario_id' => $usuarioId, 'rol' => 'estudiante']);

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
        $usuarioId = (new UsuarioModel())->insert(['activo' => 1, 'rol' => 'responsable']);
        session()->set(['usuario_id' => $usuarioId, 'rol' => 'responsable']);

        $resultado = (new AuthFilter())->before(service('request'), ['responsable']);

        $this->assertNull($resultado);
    }

    public function testSessionIsClearedWhenAccountRoleChanges(): void
    {
        $usuarioModel = new UsuarioModel();
        $usuarioId = $usuarioModel->insert(['activo' => 1, 'rol' => 'estudiante']);
        session()->set(['usuario_id' => $usuarioId, 'rol' => 'estudiante']);
        $usuarioModel->update($usuarioId, ['rol' => 'responsable']);

        $resultado = (new AuthFilter())->before(service('request'), ['estudiante']);

        $this->assertInstanceOf(RedirectResponse::class, $resultado);
        $this->assertNull(session()->get('usuario_id'));
    }

    public function testResponsibleCannotUseStudentRoutes(): void
    {
        $usuarioId = (new UsuarioModel())->insert(['activo' => 1, 'rol' => 'responsable']);
        session()->set(['usuario_id' => $usuarioId, 'rol' => 'responsable']);

        $resultado = (new AuthFilter())->before(service('request'), ['estudiante']);

        $this->assertInstanceOf(RedirectResponse::class, $resultado);
        $this->assertSame($usuarioId, session()->get('usuario_id'));
    }
}
