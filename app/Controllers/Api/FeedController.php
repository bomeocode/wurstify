<?php

namespace App\Controllers\Api;

use CodeIgniter\RESTful\ResourceController;
use App\Models\RatingModel;

class FeedController extends ResourceController
{
    public function index()
    {
        $ratingModel = new RatingModel();
        $page = $this->request->getGet('page') ?? 1;
        $perPage = 10;

        // HIER IST DIE KORREKTUR:
        // 1. Zuerst die Gesamtanzahl aller Bewertungen zählen
        $totalRatings = $ratingModel->countAllResults(false);

        // 2. Den Offset für die aktuelle Seite berechnen
        $offset = ($page - 1) * $perPage;

        // 3. Erst jetzt die Daten für die spezifische Seite holen
        $ratings = $ratingModel->getFeedPage($perPage, $offset);

        $renderedRatings = [];
        foreach ($ratings as $rating) {
            $renderedRatings[] = view('partials/rating_card', ['rating' => $rating]);
        }

        $pagerData = [
            'currentPage' => (int)$page,
            'pageCount'   => (int)ceil($totalRatings / $perPage)
        ];

        return $this->respond([
            'ratings_html' => $renderedRatings,
            'pager'        => $pagerData
        ]);
    }

    public function newCount()
    {
        // Wir erwarten einen Zeitstempel als GET-Parameter, z.B. ?since=2025-07-06T10:00:00Z
        $since = $this->request->getGet('since');

        // Wenn kein Zeitstempel mitgeschickt wird, gibt es keine neuen Bewertungen.
        if (!$since) {
            return $this->respond(['new_count' => 0]);
        }

        $ratingModel = new \App\Models\RatingModel();

        // Zähle alle Bewertungen, die neuer sind als der übergebene Zeitstempel
        $count = $ratingModel->where('created_at >', $since)->countAllResults();

        return $this->respond(['new_count' => $count]);
    }
}
