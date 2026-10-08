<?php

namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;

class AddFechaResolucionToSolicitudes extends Migration
{
    public function up()
    {
        $this->forge->addColumn('solicitudes', [
            'fecha_resolucion' => [
                'type' => 'DATETIME',
                'null' => true,
            ],
        ]);
    }

    public function down()
    {
        $this->forge->dropColumn('solicitudes', 'fecha_resolucion');
    }
}

