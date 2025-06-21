<?php

namespace App\Controllers;

// use App\Controllers\BaseController;
// use CodeIgniter\HTTP\ResponseInterface;

class Dashboard extends BaseController
{
    public function index()
    {
        // Helfer-Funktion, um auf den eingeloggten Benutzer zuzugreifen
        $data['user'] = auth()->user();

        return view('pages/dashboard/index_view', $data);
    }
}
