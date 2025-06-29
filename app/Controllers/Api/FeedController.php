<?php

namespace App\Controllers\Api;

use CodeIgniter\RESTful\ResourceController;
use App\Models\RatingModel;

class FeedController extends ResourceController
{
    public function index()
    {
        $ratingModel = new \App\Models\RatingModel();

        $perPage = 10; // Wie viele Einträge pro Seite
        $page = (int) ($this->request->getGet('page') ?? 1);

        // 1. Manuell die Gesamtanzahl aller Bewertungen aus dem Model holen
        $total = $ratingModel->countFeedItems();

        // 2. Die Paginierungs-Daten manuell berechnen
        $pageCount = (int) ceil($total / $perPage);
        $offset = ($page - 1) * $perPage;

        // 3. Die Daten für die aktuelle Seite mit manuellem Limit/Offset holen
        $ratings = $ratingModel->getFeedPage($perPage, $offset);

        // 4. Unser eigenes, manuelles Pager-Objekt für das JSON erstellen
        $pager_data = [
            'currentPage' => $page,
            'pageCount'   => $pageCount,
            'perPage'     => $perPage,
            'total'       => $total,
        ];

        // 5. Alles als JSON zurückgeben
        $data = [
            'ratings' => $ratings,
            'pager'   => $pager_data,
        ];

        return $this->respond($data);
    }
}
