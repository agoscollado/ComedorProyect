<?php

namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;

class CreateAcreditaciones extends Migration
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
            'chequera_id' => [
                'type'       => 'INT',
                'constraint' => 11,
                'unsigned'   => true,
            ],
            'monto' => [
                'type'       => 'DECIMAL',
                'constraint' => '10,2',
            ],
            'fecha_acreditacion' => [
                'type'       => 'DATETIME',
            ],
        ]);
        $this->forge->addPrimaryKey('id');
        $this->forge->addForeignKey('chequera_id', 'chequeras', 'id', 'RESTRICT', 'RESTRICT');
        $this->forge->createTable('acreditaciones');
    }

    public function down()
    {
        $this->forge->dropTable('acreditaciones');
    }
}

