<?php

namespace App\Controllers;

use App\Controllers\BaseController;
use CodeIgniter\HTTP\ResponseInterface;

class PanelResponsable extends BaseController
{
    public function index()
    {
        return view('panelResponsable/index');
    }
}
