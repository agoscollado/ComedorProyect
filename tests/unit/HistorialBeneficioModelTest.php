<?php

use App\Models\HistorialBeneficioModel;
use App\Models\UsuarioModel;
use CodeIgniter\Test\CIUnitTestCase;

/**
 * @internal
 */
final class HistorialBeneficioModelTest extends CIUnitTestCase
{
    protected function setUp(): void
    {
        parent::setUp();

        $forge = \Config\Database::forge();
        $forge->dropTable('historial_beneficios', true);
        $forge->dropTable('usuarios', true);
        $forge->addField([
            'id' => [
                'type' => 'INT',
                'auto_increment' => true,
            ],
            'nombre' => [
                'type' => 'VARCHAR',
                'constraint' => 100,
                'null' => true,
            ],
            'rol' => [
                'type' => 'VARCHAR',
                'constraint' => 20,
            ],
            'activo' => [
                'type' => 'BOOLEAN',
                'default' => false,
            ],
        ]);
        $forge->addPrimaryKey('id');
        $forge->createTable('usuarios');

        $forge->addField([
            'id' => [
                'type' => 'INT',
                'auto_increment' => true,
            ],
            'estudiante_id' => [
                'type' => 'INT',
            ],
            'responsable_id' => [
                'type' => 'INT',
            ],
            'activo_anterior' => [
                'type' => 'BOOLEAN',
            ],
            'activo_nuevo' => [
                'type' => 'BOOLEAN',
            ],
            'motivo' => [
                'type' => 'VARCHAR',
                'constraint' => 500,
            ],
            'fecha_cambio' => [
                'type' => 'DATETIME',
            ],
        ]);
        $forge->addPrimaryKey('id');
        $forge->createTable('historial_beneficios');
    }

    protected function tearDown(): void
    {
        $forge = \Config\Database::forge();
        $forge->dropTable('historial_beneficios', true);
        $forge->dropTable('usuarios', true);

        parent::tearDown();
    }

    public function testActivationUpdatesOnlyBenefitAndRecordsResponsibleAndReason(): void
    {
        $usuarioModel = new UsuarioModel();
        $responsableId = $usuarioModel->insert(['nombre' => 'Responsable', 'rol' => 'responsable', 'activo' => 0]);
        $estudianteId = $usuarioModel->insert(['nombre' => 'Estudiante', 'rol' => 'estudiante', 'activo' => 0]);

        $registrado = (new HistorialBeneficioModel())->registrarCambio(
            $estudianteId,
            $responsableId,
            1,
            'Aprobación de solicitud'
        );

        $estudiante = $usuarioModel->find($estudianteId);
        $historial = (new HistorialBeneficioModel())->first();

        $this->assertTrue($registrado);
        $this->assertSame(1, (int) $estudiante['activo']);
        $this->assertSame('estudiante', $estudiante['rol']);
        $this->assertSame($estudianteId, (int) $historial['estudiante_id']);
        $this->assertSame($responsableId, (int) $historial['responsable_id']);
        $this->assertSame(0, (int) $historial['activo_anterior']);
        $this->assertSame(1, (int) $historial['activo_nuevo']);
        $this->assertSame('Aprobación de solicitud', $historial['motivo']);
    }

    public function testSameStateDoesNotCreateAnAuditEntry(): void
    {
        $usuarioModel = new UsuarioModel();
        $responsableId = $usuarioModel->insert(['nombre' => 'Responsable', 'rol' => 'responsable', 'activo' => 0]);
        $estudianteId = $usuarioModel->insert(['nombre' => 'Estudiante', 'rol' => 'estudiante', 'activo' => 1]);

        $registrado = (new HistorialBeneficioModel())->registrarCambio(
            $estudianteId,
            $responsableId,
            1,
            'Solicitud revisada'
        );

        $this->assertFalse($registrado);
        $this->assertSame(0, (new HistorialBeneficioModel())->countAllResults());
        $this->assertSame(1, (int) $usuarioModel->find($estudianteId)['activo']);
    }

    public function testResponsibleCannotBeChangedAsAStudentBenefit(): void
    {
        $usuarioModel = new UsuarioModel();
        $responsableId = $usuarioModel->insert(['nombre' => 'Responsable', 'rol' => 'responsable', 'activo' => 0]);

        $this->expectException(\DomainException::class);
        (new HistorialBeneficioModel())->registrarCambio(
            $responsableId,
            $responsableId,
            1,
            'No corresponde'
        );
    }
}
