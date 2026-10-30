<?php

require_once APPPATH . 'Database/Migrations/2026-10-30-143000_CreateHistorialBeneficios.php';

use App\Database\Migrations\CreateHistorialBeneficios;
use CodeIgniter\Test\CIUnitTestCase;

/**
 * @internal
 */
final class CreateHistorialBeneficiosMigrationTest extends CIUnitTestCase
{
    protected function setUp(): void
    {
        parent::setUp();

        $forge = \Config\Database::forge();
        $forge->dropTable('historial_beneficios', true);
        $forge->dropTable('usuarios', true);
        $forge->addField([
            'id' => [
                'type'           => 'INT',
                'unsigned'       => true,
                'auto_increment' => true,
            ],
        ]);
        $forge->addPrimaryKey('id');
        $forge->createTable('usuarios');
    }

    protected function tearDown(): void
    {
        $forge = \Config\Database::forge();
        $forge->dropTable('historial_beneficios', true);
        $forge->dropTable('usuarios', true);

        parent::tearDown();
    }

    public function testMigrationCreatesAuditableBenefitHistoryTable(): void
    {
        $migration = new CreateHistorialBeneficios(\Config\Database::forge());
        $migration->up();

        $db = \Config\Database::connect();
        $this->assertTrue($db->tableExists('historial_beneficios'));
        $this->assertContains('estudiante_id', $db->getFieldNames('historial_beneficios'));
        $this->assertContains('responsable_id', $db->getFieldNames('historial_beneficios'));
        $this->assertContains('motivo', $db->getFieldNames('historial_beneficios'));
        $this->assertContains('fecha_cambio', $db->getFieldNames('historial_beneficios'));

        $migration->down();
        $this->assertFalse($db->tableExists('historial_beneficios'));
    }
}
