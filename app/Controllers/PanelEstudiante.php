<?php

namespace App\Controllers;

use App\Controllers\BaseController;
use CodeIgniter\HTTP\ResponseInterface;

class PanelEstudiante extends BaseController
{
    public function index()
    {
        return view('panelEstudiante/index');
    }
}
