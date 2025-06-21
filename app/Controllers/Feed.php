<?php

namespace App\Controllers;

use App\Controllers\BaseController;
use CodeIgniter\HTTP\ResponseInterface;

class Feed extends BaseController
{
    public function index()
    {
        $data = [];
        return view('feed/index', $data);
    }
}
