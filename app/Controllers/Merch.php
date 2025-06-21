<?php

namespace App\Controllers;

use App\Controllers\BaseController;
use CodeIgniter\HTTP\ResponseInterface;

class Merch extends BaseController
{
    public function index()
    {
        $data = [];
        return view('merch/index', $data);
    }
}
