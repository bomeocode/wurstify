<?php

namespace App\Controllers;

use App\Models\RatingModel; // Model einbinden

// use App\Controllers\BaseController;
// use CodeIgniter\HTTP\ResponseInterface;

class Dashboard extends BaseController
{
    public function index()
    {
        // Helfer-Funktion, um auf den eingeloggten Benutzer zuzugreifen
        $data['user'] = auth()->user();

        // NEU: Lade alle Bewertungen, die Koordinaten haben
        $ratingModel = new RatingModel();
        $data['ratings'] = $ratingModel
            ->where('latitude IS NOT NULL')
            ->where('longitude IS NOT NULL')
            ->findAll();

        return view('dashboard/index', $data);
    }
}
