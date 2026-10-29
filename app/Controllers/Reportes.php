<?php

namespace App\Controllers;

use App\Models\AcreditacionModel;
use App\Models\ReclamoModel;
use App\Models\SolicitudModel;
use App\Models\UsuarioModel;

class Reportes extends BaseController
{
    public function index()
    {
        return view('reportes/index', $this->obtenerDatosReporte());
    }

    public function exportarCsv()
    {
        $datos = $this->obtenerDatosReporte();
        $filas = [
            ['Sección', 'Indicador', 'Período', 'Valor'],
        ];
        $etiquetasEstadosSolicitud = [
            'recibida' => 'Recibida',
            'en revision' => 'En revisión',
            'aprobada' => 'Aprobada',
            'rechazada' => 'Rechazada',
        ];
        $etiquetasTiposSolicitud = [
            'inscripcion' => 'Inscripción',
            'renovacion' => 'Renovación',
        ];
        $etiquetasEstadosReclamo = [
            'pendiente' => 'Pendiente',
            'respondido' => 'Respondido',
        ];
        $periodo = $datos['desde'] . ' a ' . $datos['hasta'];

        foreach ($etiquetasEstadosSolicitud as $estado => $etiqueta) {
            $filas[] = ['Solicitudes por estado', $etiqueta, $periodo, $datos['solicitudesPorEstado'][$estado]];
        }
        foreach ($etiquetasTiposSolicitud as $tipo => $etiqueta) {
            $filas[] = ['Solicitudes por tipo', $etiqueta, $periodo, $datos['solicitudesPorTipo'][$tipo]];
        }
        foreach (ReclamoModel::CATEGORIAS as $categoria => $etiqueta) {
            $filas[] = ['Reclamos por categoría', $etiqueta, $periodo, $datos['reclamosPorCategoria'][$categoria]];
        }
        foreach ($etiquetasEstadosReclamo as $estado => $etiqueta) {
            $filas[] = ['Reclamos por estado', $etiqueta, $periodo, $datos['reclamosPorEstado'][$estado]];
        }

        $filas[] = ['Acreditaciones', 'Cantidad', $periodo, (int) ($datos['resumenAcreditaciones']['cantidad'] ?? 0)];
        $filas[] = ['Acreditaciones', 'Monto total', $periodo, number_format((float) ($datos['resumenAcreditaciones']['total'] ?? 0), 2, '.', '')];
        $filas[] = ['Beneficiarios', 'Estudiantes activos', 'Estado actual', $datos['estudiantesPorEstado']['1']];
        $filas[] = ['Beneficiarios', 'Estudiantes inactivos', 'Estado actual', $datos['estudiantesPorEstado']['0']];

        $stream = fopen('php://temp', 'r+');
        if ($stream === false) {
            throw new \RuntimeException('No se pudo preparar el archivo CSV del reporte.');
        }

        foreach ($filas as $fila) {
            if (fputcsv($stream, $fila, ';', '"', '\\') === false) {
                fclose($stream);
                throw new \RuntimeException('No se pudo generar el archivo CSV del reporte.');
            }
        }

        rewind($stream);
        $contenido = stream_get_contents($stream);
        fclose($stream);
        if ($contenido === false) {
            throw new \RuntimeException('No se pudo leer el archivo CSV del reporte.');
        }

        $nombreArchivo = 'reporte_' . $datos['desde'] . '_a_' . $datos['hasta'] . '.csv';

        return $this->response
            ->setHeader('Content-Type', 'text/csv; charset=UTF-8')
            ->setHeader('Content-Disposition', 'attachment; filename="' . $nombreArchivo . '"')
            ->setBody("\xEF\xBB\xBF" . $contenido);
    }

    private function obtenerDatosReporte(): array
    {
        $valoresRecibidos = [
            'desde' => $this->request->getGet('desde'),
            'hasta' => $this->request->getGet('hasta'),
        ];
        $fechas = ['desde' => null, 'hasta' => null];
        $error = null;
        // Validar las fechas recibidas
        foreach ($valoresRecibidos as $campo => $valor) {
            if ($valor !== null && ! is_string($valor)) {
                $error = 'Las fechas del período no son válidas.';
                break;
            }

            $fechas[$campo] = $valor === null ? null : trim($valor);
        }

        if ($error === null && ($fechas['desde'] !== null || $fechas['hasta'] !== null)) {
            $reglas = [
                'desde' => 'permit_empty|valid_date[Y-m-d]',
                'hasta' => 'permit_empty|valid_date[Y-m-d]',
            ];

            if (! $this->validate($reglas, [], $fechas)) {
                $error = implode(' ', $this->validator->getErrors());
            }
        }

        $hoy = date('Y-m-d');
        $desde = $fechas['desde'] ?? null;
        $hasta = $fechas['hasta'] ?? null;
        $desde = $desde === '' || $desde === null ? date('Y-m-d', strtotime('-90 days')) : $desde;
        $hasta = $hasta === '' || $hasta === null ? $hoy : $hasta;

        if ($error === null && $desde > $hasta) {
            $error = 'La fecha desde no puede ser posterior a la fecha hasta.';
        }

        if ($error !== null) {
            $desde = date('Y-m-d', strtotime('-90 days'));
            $hasta = $hoy;
        }

        $solicitudModel = new SolicitudModel();
        $reclamoModel = new ReclamoModel();
        $acreditacionModel = new AcreditacionModel();
        $usuarioModel = new UsuarioModel();

        $solicitudModel->actualizarEstadosVencidos();

        $solicitudesPorEstado = array_fill_keys(
            ['recibida', 'en revision', 'aprobada', 'rechazada'],
            0
        ); //devuelve un arreglo con la cantidad de solicitudes por estado en el período especificado
        foreach ($solicitudModel->contarPorEstado($desde, $hasta) as $fila) {
            if (array_key_exists($fila['estado'], $solicitudesPorEstado)) {
                $solicitudesPorEstado[$fila['estado']] = (int) $fila['total'];
            }
        }
        //devuelve un arreglo con la cantidad de solicitudes por tipo en el período especificado
        $solicitudesPorTipo = array_fill_keys(['inscripcion', 'renovacion'], 0);
        foreach ($solicitudModel->contarPorTipo($desde, $hasta) as $fila) {
            if (array_key_exists($fila['tipo'], $solicitudesPorTipo)) {
                $solicitudesPorTipo[$fila['tipo']] = (int) $fila['total'];
            }
        }
        //devuelve un arreglo con la cantidad de reclamos por categoría en el período especificado
        $reclamosPorCategoria = array_fill_keys(array_keys(ReclamoModel::CATEGORIAS), 0);
        foreach ($reclamoModel->contarPorCategoria($desde, $hasta) as $fila) {
            if (array_key_exists($fila['categoria'], $reclamosPorCategoria)) {
                $reclamosPorCategoria[$fila['categoria']] = (int) $fila['total'];
            }
        }

        //devuelve un arreglo con la cantidad de reclamos por estado en el período especificado
        $reclamosPorEstado = array_fill_keys(['pendiente', 'respondido'], 0);
        foreach ($reclamoModel->contarPorEstado($desde, $hasta) as $fila) {
            if (array_key_exists($fila['estado'], $reclamosPorEstado)) {
                $reclamosPorEstado[$fila['estado']] = (int) $fila['total'];
            }
        }
        //devuelve un arreglo con la cantidad de estudiantes activos e inactivos
        $estudiantesPorEstado = ['1' => 0, '0' => 0];
        foreach ($usuarioModel->contarEstudiantesPorEstado() as $fila) {
            $estado = (string) $fila['activo'];
            if (array_key_exists($estado, $estudiantesPorEstado)) {
                $estudiantesPorEstado[$estado] = (int) $fila['total'];
            }
        }

        return [
            'desde' => $desde,
            'hasta' => $hasta,
            'error' => $error,
            'solicitudesPorEstado' => $solicitudesPorEstado,
            'solicitudesPorTipo' => $solicitudesPorTipo,
            'reclamosPorCategoria' => $reclamosPorCategoria,
            'reclamosPorEstado' => $reclamosPorEstado,
            'resumenAcreditaciones' => $acreditacionModel->resumenEnPeriodo($desde, $hasta),
            'estudiantesPorEstado' => $estudiantesPorEstado,
        ];
    }
}
