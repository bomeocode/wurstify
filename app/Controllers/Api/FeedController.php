<?php

namespace App\Controllers\Api;

use CodeIgniter\RESTful\ResourceController;
use App\Models\RatingModel;

class FeedController extends ResourceController
{
    public function index()
    {
        $ratingModel = new \App\Models\RatingModel();
        $page = $this->request->getGet('page') ?? 1;
        $perPage = 10;

        // Wir holen die paginierten Datenobjekte vom Model
        $ratings = $ratingModel->getFeedPage($perPage, ($page - 1) * $perPage);
        $totalRatings = $ratingModel->countFeedItems();

        // Wir rendern für jede Bewertung die Partial View
        $renderedHtml = '';
        foreach ($ratings as $rating) {
            $renderedHtml .= view('partials/rating_card', ['rating' => $rating, 'context' => 'feed']);
        }

        // Wir erstellen das Pager-Objekt manuell
        $pagerData = [
            'currentPage' => (int)$page,
            'pageCount'   => (int)ceil($totalRatings / $perPage),
        ];

        // Wir geben das gerenderte HTML und den Pager zurück
        return $this->respond([
            'html'  => $renderedHtml,
            'pager' => $pagerData
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
