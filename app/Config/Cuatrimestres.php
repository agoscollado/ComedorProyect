<?php

namespace Config;

use CodeIgniter\Config\BaseConfig;

class Cuatrimestres extends BaseConfig
{
    
    /**
     * Cuatrimestres habilitados, en orden cronológico.
     * Fechas inclusivas, formato Y-m-d.
     * IMPORTANTE: fuera de estos rangos el formulario de solicitudes queda bloqueado.
     * Hay que cargar los del 2027 apenas se publique el calendario académico.
     */
    public array $cuatrimestres = [
        ['nombre' => '1° cuatrimestre 2026', 'inicio' => '2026-03-11', 'fin' => '2026-06-26'],
        ['nombre' => '2° cuatrimestre 2026', 'inicio' => '2026-08-03', 'fin' => '2026-11-13'],
    ];

    /** Días previos al fin del cuatrimestre en que se muestra el aviso de vencimiento. */
    public int $diasAvisoVencimiento = 15;

    /** Días previos al fin del cuatrimestre en que se dejan de recibir solicitudes. */
    public int $diasCierreSolicitudes = 2;
}
