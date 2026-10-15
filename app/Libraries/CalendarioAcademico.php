<?php

namespace App\Libraries;

use Config\Cuatrimestres;
use DateTimeImmutable;
use InvalidArgumentException;

class CalendarioAcademico
{
    private array $cuatrimestres;

    private int $diasAvisoVencimiento;

    private int $diasCierreSolicitudes;

    public function __construct(?object $configuracion = null)
    {
        $configuracion ??= config(Cuatrimestres::class);

        if (
            !isset($configuracion->cuatrimestres, $configuracion->diasAvisoVencimiento, $configuracion->diasCierreSolicitudes)
            || !is_array($configuracion->cuatrimestres)
            || $configuracion->cuatrimestres === []
        ) {
            throw new InvalidArgumentException('La configuración de cuatrimestres está incompleta.');
        }

        if (
            !is_int($configuracion->diasAvisoVencimiento)
            || !is_int($configuracion->diasCierreSolicitudes)
            || $configuracion->diasAvisoVencimiento < $configuracion->diasCierreSolicitudes
            || $configuracion->diasCierreSolicitudes < 0
        ) {
            throw new InvalidArgumentException('Los días de aviso y cierre no son válidos.');
        }

        $finAnterior = null;
        foreach ($configuracion->cuatrimestres as $cuatrimestre) {
            if (
                !is_array($cuatrimestre)
                || !isset($cuatrimestre['nombre'], $cuatrimestre['inicio'], $cuatrimestre['fin'])
                || !is_string($cuatrimestre['nombre'])
                || !is_string($cuatrimestre['inicio'])
                || !is_string($cuatrimestre['fin'])
                || trim($cuatrimestre['nombre']) === ''
                || !$this->fechaValida($cuatrimestre['inicio'])
                || !$this->fechaValida($cuatrimestre['fin'])
                || $cuatrimestre['inicio'] > $cuatrimestre['fin']
                || ($finAnterior !== null && $cuatrimestre['inicio'] <= $finAnterior)
            ) {
                throw new InvalidArgumentException('Hay un cuatrimestre inválido en la configuración.');
            }

            $duracion = (new DateTimeImmutable($cuatrimestre['inicio']))
                ->diff(new DateTimeImmutable($cuatrimestre['fin']))
                ->days;
            if (
                $configuracion->diasAvisoVencimiento > $duracion
                || $configuracion->diasCierreSolicitudes > $duracion
            ) {
                throw new InvalidArgumentException('Los días de aviso o cierre exceden la duración de un cuatrimestre.');
            }

            $finAnterior = $cuatrimestre['fin'];
        }

        $this->cuatrimestres = $configuracion->cuatrimestres;
        $this->diasAvisoVencimiento = $configuracion->diasAvisoVencimiento;
        $this->diasCierreSolicitudes = $configuracion->diasCierreSolicitudes;
    }

    public function cuatrimestreEn(string $fecha): ?array
    {
        if (!$this->fechaValida($fecha)) {
            throw new InvalidArgumentException('La fecha debe tener formato Y-m-d y ser válida.');
        }

        foreach ($this->cuatrimestres as $cuatrimestre) {
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

        $fechaLimite = (new DateTimeImmutable($cuatrimestre['fin']))
            ->modify("-{$this->diasCierreSolicitudes} days")
            ->format('Y-m-d');

        return $fecha <= $fechaLimite;
    }

    public function avisoVencimientoEn(string $fecha): ?array
    {
        $cuatrimestre = $this->cuatrimestreEn($fecha);
        if ($cuatrimestre === null) {
            return null;
        }

        $inicioAviso = (new DateTimeImmutable($cuatrimestre['fin']))
            ->modify("-{$this->diasAvisoVencimiento} days")
            ->format('Y-m-d');
        $fechaLimite = (new DateTimeImmutable($cuatrimestre['fin']))
            ->modify("-{$this->diasCierreSolicitudes} days")
            ->format('Y-m-d');

        if ($fecha < $inicioAviso || $fecha > $fechaLimite) {
            return null;
        }

        $cuatrimestre['fecha_limite_solicitudes'] = $fechaLimite;

        return $cuatrimestre;
    }

    private function fechaValida(string $fecha): bool
    {
        try {
            $fechaObjeto = DateTimeImmutable::createFromFormat('!Y-m-d', $fecha);
        } catch (\ValueError) {
            return false;
        }

        $errores = DateTimeImmutable::getLastErrors();

        return $fechaObjeto !== false
            && ($errores === false || ($errores['warning_count'] === 0 && $errores['error_count'] === 0))
            && $fechaObjeto->format('Y-m-d') === $fecha;
    }
}
