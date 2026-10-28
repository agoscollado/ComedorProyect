<?php

use App\Models\SolicitudModel;
use CodeIgniter\Test\CIUnitTestCase;

/**
 * @internal
 */
final class SolicitudModelTest extends CIUnitTestCase
{
    protected function setUp(): void
    {
        parent::setUp();

        $db = \Config\Database::connect();
        $forge = \Config\Database::forge($db);
        $forge->dropTable('solicitudes', true);
        $forge->addField([
            'id' => [
                'type' => 'INT',
                'auto_increment' => true,
            ],
            'usuario_id' => [
                'type' => 'INT',
            ],
            'tipo' => [
                'type' => 'VARCHAR',
                'constraint' => 20,
            ],
            'estado' => [
                'type' => 'VARCHAR',
                'constraint' => 20,
            ],
            'fecha_creacion' => [
                'type' => 'DATETIME',
            ],
            'fecha_resolucion' => [
                'type' => 'DATETIME',
                'null' => true,
            ],
        ]);
        $forge->addPrimaryKey('id');
        $forge->createTable('solicitudes');
    }

    protected function tearDown(): void
    {
        \Config\Database::connect()->disableForeignKeyChecks();
        \Config\Database::forge()->dropTable('solicitudes', true);
        \Config\Database::connect()->enableForeignKeyChecks();

        parent::tearDown();
    }

    public function testOnlyReceivedRequestsOlderThanOneDayMoveToReview(): void
    {
        $model = new SolicitudModel();
        $oldRequestId = $model->insert($this->solicitud(date('Y-m-d H:i:s', strtotime('-2 days'))));
        $recentRequestId = $model->insert($this->solicitud(date('Y-m-d H:i:s', strtotime('-12 hours'))));
        $resolvedRequestId = $model->insert($this->solicitud(
            date('Y-m-d H:i:s', strtotime('-2 days')),
            'aprobada',
        ));

        $model->actualizarEstadosVencidos();

        $this->assertSame('en revision', $model->find($oldRequestId)['estado']);
        $this->assertSame('recibida', $model->find($recentRequestId)['estado']);
        $this->assertSame('aprobada', $model->find($resolvedRequestId)['estado']);
    }

    private function solicitud(string $fechaCreacion, string $estado = 'recibida'): array
    {
        return [
            'usuario_id' => 1,
            'tipo' => 'inscripcion',
            'estado' => $estado,
            'fecha_creacion' => $fechaCreacion,
        ];
    }
}
