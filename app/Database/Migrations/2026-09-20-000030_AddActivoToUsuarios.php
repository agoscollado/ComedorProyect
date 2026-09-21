<?php

namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;

class AddActivoToUsuarios extends Migration
{
    public function up()
    {
        $this->forge->addColumn('usuarios', [
            'activo' => [
                'type' => 'BOOLEAN',
                'default' => false,
                'after' => 'rol'
            ]
        ]);
    }

    public function down()
    {
        $this->forge->dropColumn('usuarios', 'activo');
    }
}
