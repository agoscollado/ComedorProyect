<?php

namespace App\Models;

use CodeIgniter\Model;

class SolicitudModel extends Model
{
    protected $table            = 'solicitudes';
    protected $primaryKey       = 'id';
    protected $useAutoIncrement = true;
    protected $returnType       = 'array';
    protected $useSoftDeletes   = false;
    protected $protectFields    = true;
    protected $allowedFields    = ['usuario_id', 'tipo', 'estado', 'fecha_creacion', 'fecha_resolucion'];

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
    {
        $badgeClase = match ($estado) {
            'recibida' => 'badge-recibida',
            'en revision' => 'badge-en-revision',
            'aprobada' => 'badge-aprobada',
            'rechazada' => 'badge-rechazada',
        };

        $badgeTexto = match ($estado) {
            'recibida' => 'Recibida',
            'en revision' => 'En revisión',
            'aprobada' => 'Aprobada',
            'rechazada' => 'Rechazada',
        };

        return ['clase' => $badgeClase, 'texto' => $badgeTexto];
    }

    public function actualizarEstadosVencidos(): void
    { //actualiza el estado de las solicitudes que llevan más de un día en estado "recibida" a "en revision"
        $limite = date('Y-m-d H:i:s', strtotime('-1 day'));

        $this->builder()
            ->where('estado', 'recibida')
            ->where('fecha_creacion <=', $limite)
            ->update(['estado' => 'en revision']);
    }

    public function contarPorTipo(string $desde, string $hasta): array
    {//devuelve un arreglo con la cantidad de solicitudes por tipo en el período especificado
        return $this->select('tipo, COUNT(*) AS total')
            ->where('fecha_creacion >=', $desde . ' 00:00:00')
            ->where('fecha_creacion <=', $hasta . ' 23:59:59')
            ->groupBy('tipo')
            ->findAll();
    }

    public function contarPorEstado(string $desde, string $hasta): array
    {//devuelve un arreglo con la cantidad de solicitudes por estado en el período especificado
        return $this->select('estado, COUNT(*) AS total')
            ->where('fecha_creacion >=', $desde . ' 00:00:00')
            ->where('fecha_creacion <=', $hasta . ' 23:59:59')
            ->groupBy('estado')
            ->findAll();
    }
}
