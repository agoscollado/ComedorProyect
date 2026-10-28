<?php

namespace Config;

use CodeIgniter\Config\BaseConfig;
use InvalidArgumentException;
use JsonException;

class Cuatrimestres extends BaseConfig
{
    /**
     * Cuatrimestres habilitados, ordenados y sin superposición.
     * Cada entrada requiere nombre, inicio y fin en formato Y-m-d inclusivo.
     * Los días de aviso y cierre deben ser enteros no negativos y no superar la duración de ninguno de los cuatrimestres; el aviso no puede comenzar después del cierre.
     * IMPORTANTE: fuera de estos rangos el formulario de solicitudes queda bloqueado.
     */
    public array $cuatrimestres = [
        ['nombre' => '1° cuatrimestre 2026', 'inicio' => '2026-03-11', 'fin' => '2026-06-26'],
        ['nombre' => '2° cuatrimestre 2026', 'inicio' => '2026-08-03', 'fin' => '2026-11-13'],
    ];

    public function __construct()
    {
        parent::__construct();

        $periodos = env('academic.terms');
        if ($periodos === false || $periodos === null || $periodos === '') {
            return;
        }

        if (!is_string($periodos)) {
            throw new InvalidArgumentException('La configuración academic.terms debe ser un JSON válido.');
        }

        try {
            $periodosDecodificados = json_decode($periodos, true, 512, JSON_THROW_ON_ERROR);
        } catch (JsonException $exception) {
            throw new InvalidArgumentException('La configuración academic.terms debe ser un JSON válido.', 0, $exception);
        }

        if (!is_array($periodosDecodificados) || $periodosDecodificados === []) {
            throw new InvalidArgumentException('La configuración academic.terms debe contener al menos un período.');
        }

        $this->cuatrimestres = $periodosDecodificados;
    }

    /** Días previos al fin del cuatrimestre en que se muestra el aviso de vencimiento. */
    public int $diasAvisoVencimiento = 15;

    /** Días previos al fin del cuatrimestre en que se dejan de recibir solicitudes. */
    public int $diasCierreSolicitudes = 2;
}
