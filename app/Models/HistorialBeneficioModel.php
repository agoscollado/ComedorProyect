<?php

namespace App\Models;

use CodeIgniter\Model;
use DomainException;
use RuntimeException;
use Throwable;

class HistorialBeneficioModel extends Model
{
    protected $table = 'historial_beneficios';
    protected $primaryKey = 'id';
    protected $returnType = 'array';
    protected $allowedFields = [
        'estudiante_id',
        'responsable_id',
        'activo_anterior',
        'activo_nuevo',
        'motivo',
        'fecha_cambio',
    ];
    protected $useTimestamps = false;

    public function registrarCambio(int $estudianteId, int $responsableId, int $activoNuevo, string $motivo): bool
    {
        $db = $this->db;
        if (! $db->transBegin()) {
            throw new RuntimeException('No se pudo iniciar la transacción para el cambio de beneficio.');
        }
        $transaccionAbierta = true;

        try {
            $estudiante = (new UsuarioModel())->find($estudianteId);
            if ($estudiante === null || $estudiante['rol'] !== 'estudiante') {
                throw new DomainException('El estudiante no existe.');
            }

            $activoAnterior = (int) $estudiante['activo'];
            if ($activoAnterior === $activoNuevo) {
                $db->transRollback();
                $transaccionAbierta = false;

                return false;
            }

            $actualizado = $db->table('usuarios')
                ->where('id', $estudianteId)
                ->where('rol', 'estudiante')
                ->where('activo', $activoAnterior)
                ->update(['activo' => $activoNuevo]);

            if (! $actualizado || $db->affectedRows() !== 1) {
                throw new RuntimeException('No se pudo actualizar el estado del beneficio.');
            }

            if ($this->insert([
                'estudiante_id' => $estudianteId,
                'responsable_id' => $responsableId,
                'activo_anterior' => $activoAnterior,
                'activo_nuevo' => $activoNuevo,
                'motivo' => $motivo,
                'fecha_cambio' => date('Y-m-d H:i:s'),
            ]) === false) {
                throw new RuntimeException('No se pudo guardar la auditoría del cambio de beneficio.');
            }

            if (! $db->transStatus() || ! $db->transCommit()) {
                throw new RuntimeException('No se pudo confirmar el cambio de beneficio.');
            }
            $transaccionAbierta = false;

            return true;
        } catch (Throwable $exception) {
            if ($transaccionAbierta) {
                $db->transRollback();
            }

            throw $exception;
        }
    }

    public function ultimosCambios(int $limite = 20): array
    {
        return $this->select(
            'historial_beneficios.*, estudiantes.nombre AS estudiante_nombre, responsables.nombre AS responsable_nombre'
        )
            ->join('usuarios AS estudiantes', 'estudiantes.id = historial_beneficios.estudiante_id')
            ->join('usuarios AS responsables', 'responsables.id = historial_beneficios.responsable_id')
            ->orderBy('historial_beneficios.fecha_cambio', 'DESC')
            ->orderBy('historial_beneficios.id', 'DESC')
            ->findAll($limite);
    }
}
