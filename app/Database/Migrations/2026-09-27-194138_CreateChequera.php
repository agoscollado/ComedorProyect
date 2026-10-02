<?php

namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;

class CreateChequera extends Migration
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
            'estado' => [
                'type'       => 'ENUM',
                'constraint' => ['activa', 'inactiva'],
                'default'    => 'inactiva',
            ],
        ]);
        $this->forge->addPrimaryKey('id');
        $this->forge->addForeignKey('usuario_id', 'usuarios', 'id', 'RESTRICT', 'RESTRICT');
        $this->forge->addUniqueKey('usuario_id');
        $this->forge->createTable('chequeras');
    }

    public function down()
    {
        $this->forge->dropTable('chequeras');
    }
}
