<?php

namespace App\Filters;

use CodeIgniter\Filters\FilterInterface;
use CodeIgniter\HTTP\RequestInterface;
use CodeIgniter\HTTP\ResponseInterface;

class MaintenanceFilter implements FilterInterface
{
    public function before(RequestInterface $request, $arguments = null)
    {
        // Prüfen, ob der Wartungsmodus in der .env-Datei aktiviert ist
        if (config('App')->maintenanceMode === true || getenv('maintenance.mode') === 'true') {

            // Admins dürfen immer rein
            if (auth()->loggedIn() && auth()->user()->inGroup('admin')) {
                return;
            }

            // Zeige die Wartungsseite an und beende die Ausführung
            echo view('maintenance');
            exit();
        }
    }

    public function after(RequestInterface $request, ResponseInterface $response, $arguments = null)
    {
        // Hier müssen wir nichts tun
    }
}
