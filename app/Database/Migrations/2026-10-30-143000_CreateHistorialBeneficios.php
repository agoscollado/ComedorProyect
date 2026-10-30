<?php

namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;

class CreateHistorialBeneficios extends Migration
{
    public function up()
    {
        $this->forge->addField([
            'id' => [
                'type'           => 'INT',
                'unsigned'       => true,
                'auto_increment' => true,
            ],
            'estudiante_id' => [
                'type'     => 'INT',
                'unsigned' => true,
            ],
            'responsable_id' => [
                'type'     => 'INT',
                'unsigned' => true,
            ],
            'activo_anterior' => [
                'type' => 'BOOLEAN',
            ],
            'activo_nuevo' => [
                'type' => 'BOOLEAN',
            ],
            'motivo' => [
                'type'       => 'VARCHAR',
                'constraint' => 500,
            ],
            'fecha_cambio' => [
                'type' => 'DATETIME',
            ],
        ]);
        $this->forge->addPrimaryKey('id');
        $this->forge->addKey(['estudiante_id', 'fecha_cambio'], false, false, 'idx_historial_beneficios_estudiante_fecha');
        $this->forge->addKey(['responsable_id', 'fecha_cambio'], false, false, 'idx_historial_beneficios_responsable_fecha');
        $this->forge->addForeignKey('estudiante_id', 'usuarios', 'id', 'RESTRICT', 'RESTRICT');
        $this->forge->addForeignKey('responsable_id', 'usuarios', 'id', 'RESTRICT', 'RESTRICT');
        $this->forge->createTable('historial_beneficios');
    }

    public function down()
    {
        $this->forge->dropTable('historial_beneficios', true);
    }
}
