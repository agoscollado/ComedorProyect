<?php

use App\Libraries\CalendarioAcademico;
use PHPUnit\Framework\TestCase;

/**
 * @internal
 */
final class CalendarioAcademicoTest extends TestCase
{
    public function testComputesCuatrimestreSubmissionAndReminderWindows(): void
    {
        $calendario = $this->calendario();

        $this->assertSame('2° cuatrimestre 2026', $calendario->cuatrimestreEn('2026-08-03')['nombre']);
        $this->assertNull($calendario->cuatrimestreEn('2026-07-01'));
        $this->assertTrue($calendario->solicitudesAbiertas('2026-11-11'));
        $this->assertFalse($calendario->solicitudesAbiertas('2026-11-12'));
        $this->assertNull($calendario->avisoVencimientoEn('2026-10-28'));
        $this->assertSame(
            '2026-11-11',
            $calendario->avisoVencimientoEn('2026-10-29')['fecha_limite_solicitudes'],
        );
        $this->assertNull($calendario->avisoVencimientoEn('2026-11-12'));
    }

    public function testLoadsAndValidatesProjectConfiguration(): void
    {
        $calendario = new CalendarioAcademico();

        $this->assertSame('2° cuatrimestre 2026', $calendario->cuatrimestreEn('2026-08-03')['nombre']);
    }

    public function testRejectsInvalidInputDate(): void
    {
        $this->expectException(\InvalidArgumentException::class);

        $this->calendario()->cuatrimestreEn('2026-02-30');
    }

    public function testRejectsInputDateWithNullBytes(): void
    {
        $this->expectException(\InvalidArgumentException::class);

        $this->calendario()->cuatrimestreEn("2026-01-01\0");
    }

    public function testRejectsMissingConfigurationProperties(): void
    {
        $this->expectException(\InvalidArgumentException::class);

        new CalendarioAcademico((object) []);
    }

    public function testRejectsOverlappingOrUnorderedCuatrimestres(): void
    {
        $this->expectException(\InvalidArgumentException::class);

        $this->calendario([
            ['nombre' => 'Segundo', 'inicio' => '2026-08-01', 'fin' => '2026-12-01'],
            ['nombre' => 'Primero', 'inicio' => '2026-03-01', 'fin' => '2026-06-01'],
        ]);
    }

    public function testRejectsInconsistentReminderAndClosingDays(): void
    {
        $this->expectException(\InvalidArgumentException::class);

        $this->calendario(diasAvisoVencimiento: 1, diasCierreSolicitudes: 2);
    }

    public function testRejectsMissingCuatrimestreFields(): void
    {
        $this->expectException(\InvalidArgumentException::class);

        $this->calendario([
            ['nombre' => 'Cuatrimestre incompleto', 'inicio' => '2026-03-01'],
        ]);
    }

    public function testRejectsInvalidConfiguredDates(): void
    {
        $this->expectException(\InvalidArgumentException::class);

        $this->calendario([
            ['nombre' => 'Fecha inválida', 'inicio' => '2026-02-30', 'fin' => '2026-06-01'],
        ]);
    }

    private function calendario(
        ?array $cuatrimestres = null,
        int $diasAvisoVencimiento = 15,
        int $diasCierreSolicitudes = 2,
    ): CalendarioAcademico {
        return new CalendarioAcademico((object) [
            'cuatrimestres' => $cuatrimestres ?? [
                ['nombre' => '1° cuatrimestre 2026', 'inicio' => '2026-03-11', 'fin' => '2026-06-26'],
                ['nombre' => '2° cuatrimestre 2026', 'inicio' => '2026-08-03', 'fin' => '2026-11-13'],
            ],
            'diasAvisoVencimiento' => $diasAvisoVencimiento,
            'diasCierreSolicitudes' => $diasCierreSolicitudes,
        ]);
    }
}
