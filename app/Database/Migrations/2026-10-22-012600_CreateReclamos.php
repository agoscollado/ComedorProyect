<?php

namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;

class CreateReclamos extends Migration
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
            'responsable_id' => [
                'type'       => 'INT',
                'constraint' => 11,
                'unsigned'   => true,
                'null'       => true,
            ],
            'categoria' => [
                'type'       => 'ENUM',
                'constraint' => ['inscripcion', 'chequera', 'atencion', 'vianda', 'sistema', 'otro'],
            ],
            'descripcion' => [
                'type' => 'TEXT',
            ],
            'estado' => [
                'type'       => 'ENUM',
                'constraint' => ['pendiente', 'respondido'],
                'default'    => 'pendiente',
            ],
            'respuesta' => [
                'type' => 'TEXT',
                'null' => true,
            ],
            'fecha_creacion' => [
                'type' => 'DATETIME',
            ],
            'fecha_respuesta' => [
                'type' => 'DATETIME',
                'null' => true,
            ],
        ]);
        $this->forge->addPrimaryKey('id');
        $this->forge->addForeignKey('usuario_id', 'usuarios', 'id', 'RESTRICT', 'RESTRICT');
        $this->forge->addForeignKey('responsable_id', 'usuarios', 'id', 'RESTRICT', 'RESTRICT');
        $this->forge->createTable('reclamos');
    }

    public function down()
    {
        $this->forge->dropTable('reclamos');
    }
}
