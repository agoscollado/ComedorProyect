<?php

namespace App\Models;

use CodeIgniter\Model;

class ReclamoModel extends Model
{
    public const CATEGORIAS = [
    'inscripcion' => 'Inscripción o renovación',
    'chequera'    => 'Chequera',
    'atencion'    => 'Atención del personal',
    'vianda'      => 'Vianda',
    'sistema'     => 'Sistema web',
    'otro'        => 'Otro',
    ];
    
    protected $table            = 'reclamos';
    protected $primaryKey       = 'id';
    protected $useAutoIncrement = true;
    protected $returnType       = 'array';
    protected $useSoftDeletes   = false;
    protected $protectFields    = true;
    protected $allowedFields    = ['usuario_id','responsable_id','categoria','descripcion','estado','respuesta','fecha_creacion','fecha_respuesta'];

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

    public function estadoBadge(string $estado): array
    { //devuelve un arreglo con la clase CSS y el texto correspondiente al estado del reclamo, para mostrarlo como un badge en la interfaz
        return match ($estado) {
            'pendiente'  => ['clase' => 'badge-recibida', 'texto' => 'Pendiente'],
            'respondido' => ['clase' => 'badge-aprobada', 'texto' => 'Respondido'],
            default      => ['clase' => 'badge-recibida', 'texto' => ucfirst($estado)],
        };
    }
}
