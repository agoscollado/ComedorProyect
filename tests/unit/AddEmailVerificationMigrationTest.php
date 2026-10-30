<?php

require_once APPPATH . 'Database/Migrations/2026-10-29-230000_AddEmailVerificationAndAuthTokens.php';

use App\Database\Migrations\AddEmailVerificationAndAuthTokens;
use CodeIgniter\Test\CIUnitTestCase;

/**
 * @internal
 */
final class AddEmailVerificationMigrationTest extends CIUnitTestCase
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
            'email' => [
                'type' => 'VARCHAR',
                'constraint' => 150,
            ],
            'activo' => [
                'type' => 'BOOLEAN',
                'default' => false,
            ],
        ]);
        $forge->addPrimaryKey('id');
        $forge->createTable('usuarios');
        $db->table('usuarios')->insert([
            'email' => 'existente@example.com',
            'activo' => 0,
        ]);
    }

    protected function tearDown(): void
    {
        $forge = \Config\Database::forge();
        $forge->dropTable('auth_tokens', true);
        $forge->dropTable('usuarios', true);

        parent::tearDown();
    }

    public function testExistingAccountsAreVerifiedAndTokenTableIsCreated(): void
    {
        $migration = new AddEmailVerificationAndAuthTokens(\Config\Database::forge());
        $migration->up();

        $usuario = \Config\Database::connect()
            ->table('usuarios')
            ->where('email', 'existente@example.com')
            ->get()
            ->getRowArray();

        $this->assertNotEmpty($usuario['email_verified_at']);
        $this->assertSame(0, (int) $usuario['auth_version']);
        $this->assertTrue(\Config\Database::connect()->tableExists('auth_tokens'));
        $this->assertContains('token_hash', \Config\Database::connect()->getFieldNames('auth_tokens'));
    }
}
