<?php

namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;

class CreateSolucitud extends Migration
{
    public function up()
    {
        $this->forge->addField([
            'id' => [
                'type'           => 'INT',
                'constraint'     => 11,
                'unsigned'       => true,
                'auto_increment' => true,
            ],
            'usuario_id' => [
                'type'       => 'INT',
                'constraint' => 11,
                'unsigned'   => true,
            ],
            'tipo' => [
                'type'       => 'ENUM',
                'constraint' => ['inscripcion', 'renovacion'],
            ],
            'estado' => [
                'type'       => 'ENUM',
                'constraint' => ['recibida', 'en revision', 'aprobada', 'rechazada'],
                'default'       => 'recibida',
            ],
            'fecha_creacion' => [
                'type'       => 'DATETIME',
                'null'       => false,
            ],
        ]);
        $this->forge->addPrimaryKey('id');
        $this->forge->addForeignKey('usuario_id', 'usuarios', 'id', 'RESTRICT', 'RESTRICT');
        $this->forge->createTable('solicitudes');
    }
    public function down()
    {
        $this->forge->dropTable('solicitudes');
    }
}
