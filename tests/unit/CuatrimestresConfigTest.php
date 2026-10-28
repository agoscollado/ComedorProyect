<?php

use Config\Cuatrimestres;
use PHPUnit\Framework\TestCase;

/**
 * @internal
 */
final class CuatrimestresConfigTest extends TestCase
{
    public function testAcademicPeriodsCanBeConfiguredOutsideTheSourceCode(): void
    {
        $periodos = [
            ['nombre' => 'Período de prueba', 'inicio' => '2027-03-01', 'fin' => '2027-06-30'],
        ];
        $clave = 'academic.terms';
        $valorAnterior = $_ENV[$clave] ?? null;

        try {
            $_ENV[$clave] = json_encode($periodos, JSON_THROW_ON_ERROR);

            $configuracion = new Cuatrimestres();

            $this->assertSame($periodos, $configuracion->cuatrimestres);
        } finally {
            if ($valorAnterior === null) {
                unset($_ENV[$clave]);
            } else {
                $_ENV[$clave] = $valorAnterior;
            }
        }
    }

    public function testInvalidAcademicPeriodConfigurationFailsExplicitly(): void
    {
        $clave = 'academic.terms';
        $valorAnterior = $_ENV[$clave] ?? null;

        try {
            $_ENV[$clave] = 'not-json';

            $this->expectException(InvalidArgumentException::class);
            new Cuatrimestres();
        } finally {
            if ($valorAnterior === null) {
                unset($_ENV[$clave]);
            } else {
                $_ENV[$clave] = $valorAnterior;
            }
        }
    }
}
