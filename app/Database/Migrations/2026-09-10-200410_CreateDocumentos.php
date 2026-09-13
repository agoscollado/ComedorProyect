<?php

namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;

class CreateDocumentos extends Migration
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
            'solicitud_id' => [
                'type'       => 'INT',
                'constraint' => 11,
                'unsigned'   => true,
            ],
            'tipo_documento' => [
                'type'       => 'ENUM',
                'constraint' => ['dni', 'certificado'],
            ],
            'ruta_archivo' => [
                'type'       => 'VARCHAR',
                'constraint' => 255,
            ],
            'fecha_carga' => [
                'type' => 'DATETIME',
                'null' => false,
            ],
        ]);

        $this->forge->addPrimaryKey('id');
        $this->forge->addForeignKey('solicitud_id', 'solicitudes', 'id', 'RESTRICT', 'RESTRICT');
        $this->forge->createTable('documentos');
    }

    public function down()
    {
        $this->forge->dropTable('documentos');
    }
}
