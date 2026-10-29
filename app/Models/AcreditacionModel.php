<?php

namespace App\Models;

use CodeIgniter\Model;

class AcreditacionModel extends Model
{
    protected $table            = 'acreditaciones';
    protected $primaryKey       = 'id';
    protected $useAutoIncrement = true;
    protected $returnType       = 'array';
    protected $useSoftDeletes   = false;
    protected $protectFields    = true;
    protected $allowedFields    = ['chequera_id', 'monto', 'fecha_acreditacion'];

    protected bool $allowEmptyInserts = false;
    protected bool $updateOnlyChanged = true;

    protected array $casts = [];
    protected array $castHandlers = [];

    // Dates
    protected $useTimestamps = false;
    protected $dateFormat    = 'datetime';
    protected $createdField  = 'created_at';
    protected $updatedField  = 'updated_at';
    protected $deletedField  = 'deleted_at';

    // Validation
    protected $validationRules      = [];
    protected $validationMessages   = [];
    protected $skipValidation       = false;
    protected $cleanValidationRules = true;

    // Callbacks
    protected $allowCallbacks = true;
    protected $beforeInsert   = [];
    protected $afterInsert    = [];
    protected $beforeUpdate   = [];
    protected $afterUpdate    = [];
    protected $beforeFind     = [];
    protected $afterFind      = [];
    protected $beforeDelete   = [];
    protected $afterDelete    = [];

    //devuelve un arreglo con la cantidad de acreditaciones y el monto total acreditado en el período especificado
    public function resumenEnPeriodo(string $desde, string $hasta): array
    {
        return $this->select('COUNT(*) AS cantidad, COALESCE(SUM(monto), 0) AS total')
            ->where('fecha_acreditacion >=', $desde . ' 00:00:00')
            ->where('fecha_acreditacion <=', $hasta . ' 23:59:59')
            ->first();
    }
}
