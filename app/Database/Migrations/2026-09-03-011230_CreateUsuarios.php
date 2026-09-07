<?php

namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;

class CreateUsuarios extends Migration
{
    public function up()
    {
        $this->forge->addField([
            'id' => [
                'type'           => 'INT',
                'unsigned'       => true,
                'auto_increment' => true,
            ],
            'nombre' => [
                'type'       => 'varchar',
                'constraint' => 100,
            ],
            'email' => [
                'type'       => 'varchar',
                'constraint' => 150,
            ],
            'password' => [
                'type'       => 'varchar',
                'constraint' => 255,
            ],
            'rol' => [
                'type'       => 'ENUM',
                'constraint' => ['estudiante', 'responsable'],
            ],
            'dni' => [
                'type'       => 'varchar',
                'constraint' => 20,
                'null'       => true,
            ],
            'carrera' => [
                'type'       => 'varchar',
                'constraint' => 100,
                'null'       => true,
            ],
        ]);

        $this->forge->addPrimaryKey('id');
        $this->forge->addUniqueKey('email');
        $this->forge->createTable('usuarios');
    }

    public function down()
    {
        $this->forge->dropTable('usuarios');
    }
}
