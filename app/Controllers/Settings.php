<?php

namespace App\Controllers;

use App\Controllers\BaseController;
use CodeIgniter\HTTP\ResponseInterface;

class Settings extends BaseController
{
    public function index()
    {
        $data = [];
        return view('pages/settings/index_view', $data);
    }
}
