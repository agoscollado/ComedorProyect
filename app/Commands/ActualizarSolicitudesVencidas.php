<?php

namespace App\Commands;

use App\Models\SolicitudModel;
use CodeIgniter\CLI\BaseCommand;
use CodeIgniter\CLI\CLI;

class ActualizarSolicitudesVencidas extends BaseCommand
{
    protected $group = 'Solicitudes';
    protected $name = 'solicitudes:actualizar-vencidas';
    protected $description = 'Pasa a revisión las solicitudes recibidas hace más de un día.';

    public function run(array $params)
    {
        (new SolicitudModel())->actualizarEstadosVencidos();

        CLI::write('Se actualizaron las solicitudes vencidas.', 'green');
    }
}
