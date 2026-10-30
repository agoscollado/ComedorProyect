<?php

namespace App\Controllers;

use App\Controllers\BaseController;
use App\Models\HistorialBeneficioModel;
use App\Models\UsuarioModel;
use DomainException;
use Throwable;

class GestionBeneficiarios extends BaseController
{
    public function listarUsuarios()
    {
        $usuarioModel = new UsuarioModel();
        $usuarios = $usuarioModel
            ->where('rol', 'estudiante')
            ->orderBy('nombre', 'ASC')
            ->paginate(20, 'beneficiarios');

        return view('gestionBeneficiarios/listarUsuarios', [
            'usuarios' => $usuarios,
            'pager' => $usuarioModel->pager,
            'historial' => (new HistorialBeneficioModel())->ultimosCambios(),
        ]);
    }

    public function darDeBaja($id)
    {
        return $this->cambiarBeneficio((int) $id, 0);
    }

    public function activarBeneficio($id)
    {
        return $this->cambiarBeneficio((int) $id, 1);
    }

    private function cambiarBeneficio(int $estudianteId, int $activoNuevo)
    {
        $motivo = $this->request->getPost('motivo');
        if (! is_string($motivo)) {
            return redirect()->back()->with('error', 'Indicá el motivo del cambio de beneficio.');
        }

        $motivo = trim($motivo);
        if (mb_strlen($motivo, 'UTF-8') < 5 || mb_strlen($motivo, 'UTF-8') > 500) {
            return redirect()->back()->with(
                'error',
                'El motivo debe tener entre 5 y 500 caracteres.'
            )->withInput();
        }

        try {
            $cambioRegistrado = (new HistorialBeneficioModel())->registrarCambio(
                $estudianteId,
                (int) session()->get('usuario_id'),
                $activoNuevo,
                $motivo
            );
        } catch (DomainException $exception) {
            return redirect()->to('/gestionBeneficiarios/listarUsuarios')
                ->with('error', $exception->getMessage());
        } catch (Throwable $exception) {
            log_message('error', 'No se pudo cambiar el beneficio del estudiante {id}: {error}', [
                'id' => $estudianteId,
                'error' => $exception->getMessage(),
            ]);

            return redirect()->to('/gestionBeneficiarios/listarUsuarios')
                ->with('error', 'No se pudo actualizar el beneficio. Intentá de nuevo.');
        }

        if (! $cambioRegistrado) {
            return redirect()->to('/gestionBeneficiarios/listarUsuarios')
                ->with('error', 'El beneficio ya tenía ese estado; no se registró un cambio.');
        }

        $mensaje = $activoNuevo === 1
            ? 'Beneficio activado y cambio registrado.'
            : 'Beneficio dado de baja y cambio registrado.';

        return redirect()->to('/gestionBeneficiarios/listarUsuarios')->with('exito', $mensaje);
    }
}
