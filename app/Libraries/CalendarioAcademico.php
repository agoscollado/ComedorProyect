<?php

namespace App\Libraries;

class CalendarioAcademico
{
    public function cuatrimestreEn(string $fecha): ?array
    {
        $cuatrimestres = config('Cuatrimestres')->cuatrimestres;
        foreach ($cuatrimestres as $cuatrimestre) {
            if ($fecha >= $cuatrimestre['inicio'] && $fecha <= $cuatrimestre['fin']) {
                return $cuatrimestre;
            }
        }
        return null;
    }

    public function solicitudesAbiertas(string $fecha): bool
    {
        $cuatrimestre = $this->cuatrimestreEn($fecha);
        if ($cuatrimestre === null) {
            return false;
        }

        $config = config('Cuatrimestres');
        $fechaLimite = (new \DateTimeImmutable($cuatrimestre['fin']))
            ->modify(sprintf('-%d days', $config->diasCierreSolicitudes))
            ->format('Y-m-d');

        return $fecha <= $fechaLimite;
    }

    public function avisoVencimientoEn(string $fecha): ?array
    {
        $cuatrimestre = $this->cuatrimestreEn($fecha);
        if ($cuatrimestre === null) {
            return null;
        }

        $config = config('Cuatrimestres');
        $inicioAviso = (new \DateTimeImmutable($cuatrimestre['fin']))
            ->modify(sprintf('-%d days', $config->diasAvisoVencimiento))
            ->format('Y-m-d');
        $fechaLimite = (new \DateTimeImmutable($cuatrimestre['fin']))
            ->modify(sprintf('-%d days', $config->diasCierreSolicitudes))
            ->format('Y-m-d');

        if ($fecha < $inicioAviso || $fecha > $fechaLimite) {
            return null;
        }

        $cuatrimestre['fecha_limite_solicitudes'] = $fechaLimite;

        return $cuatrimestre;
    }
}