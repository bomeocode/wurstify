<?php

namespace App\Controllers;

use App\Controllers\BaseController;
use CodeIgniter\HTTP\ResponseInterface;

class Ratings extends BaseController
{
    public function index()
    {
        $data = [];
        return view('ratings/index', $data);
    }
}
