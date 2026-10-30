<?php

namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;

class AddUniqueDniToUsuarios extends Migration
{
    public function up()
    {
        $tabla = $this->db->escapeIdentifiers($this->db->prefixTable('usuarios'));
        $duplicados = $this->db->query(
            'SELECT COUNT(*) AS cantidad FROM (
                SELECT dni
                FROM ' . $tabla . '
                WHERE dni IS NOT NULL
                GROUP BY dni
                HAVING COUNT(*) > 1
            ) AS dni_duplicados'
        )->getRowArray();

        if ((int) ($duplicados['cantidad'] ?? 0) > 0) {
            throw new \RuntimeException(
                'No se puede agregar la unicidad del DNI: hay valores duplicados en usuarios. Revisalos antes de migrar.'
            );
        }

        $this->forge->addUniqueKey('dni', 'uq_usuarios_dni');
        if (! $this->forge->processIndexes('usuarios')) {
            throw new \RuntimeException('No se pudo crear el índice único para DNI.');
        }
    }

    public function down()
    {
        if (! $this->forge->dropKey('usuarios', 'uq_usuarios_dni')) {
            throw new \RuntimeException('No se pudo quitar el índice único para DNI.');
        }
    }
}
