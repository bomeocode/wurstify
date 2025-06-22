<?php

namespace App\Controllers;

use App\Controllers\BaseController;
use CodeIgniter\HTTP\ResponseInterface;

class Settings extends BaseController
{
    public function index()
    {
        $data = [];
        return view('settings/index', $data);
    }

    public function profile()
    {
        $data = [];
        return view('settings/profile', $data);
    }
}
