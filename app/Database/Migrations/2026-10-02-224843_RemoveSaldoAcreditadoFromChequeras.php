<?php

namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;

class RemoveSaldoAcreditadoFromChequeras extends Migration
{
    public function up()
    {
        $this->forge->dropColumn('chequeras', 'saldo_acreditado');
    }

    public function down()
    {
        $this->forge->addColumn('chequeras', [
            'saldo_acreditado' => [
                'type'       => 'DECIMAL',
                'constraint' => '10,2',
                'default'    => 0.00,
            ],
        ]);
    }
}
