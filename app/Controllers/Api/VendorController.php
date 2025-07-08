<?php

namespace App\Controllers\Api;

use CodeIgniter\RESTful\ResourceController;
use App\Models\VendorModel;
use App\Models\RatingModel;

class VendorController extends ResourceController
{
    /**
     * Liefert paginierte Bewertungen für einen spezifischen Anbieter.
     */
    public function ratings($uuid = null)
    {
        $vendorModel = new \App\Models\VendorModel();
        $vendor = $vendorModel->where('uuid', $uuid)->first();

        if (!$vendor) {
            return $this->failNotFound('Anbieter nicht gefunden.');
        }

        $page = $this->request->getGet('page') ?? 1;
        $perPage = 10; // Anzahl der Bewertungen pro Ladevorgang

        $ratingModel = new RatingModel();

        // Wir holen die Bewertungen für die aktuelle Seite
        $ratings = $ratingModel->getPageForVendor($vendor['id'], $perPage, ($page - 1) * $perPage);

        // Zähle die Gesamtanzahl der Bewertungen für diesen Anbieter
        $totalRatings = $ratingModel->where('vendor_id', $vendor['id'])->countAllResults();

        // Rendere für jede Bewertung die Partial View
        $renderedRatings = [];
        foreach ($ratings as $rating) {
            $renderedRatings[] = view('partials/rating_card', ['rating' => $rating]);
        }

        // Bereite die Pager-Informationen vor
        $pagerData = [
            'currentPage' => (int)$page,
            'pageCount'   => (int)ceil($totalRatings / $perPage),
        ];

        return $this->respond([
            'ratings_html' => $renderedRatings,
            'pager'        => $pagerData,
        ]);
    }

    /**
     * Liefert die HTML-Ansicht für die Details eines einzelnen Anbieters.
     */
    public function show($uuid = null)
    {
        $vendorModel = new \App\Models\VendorModel();

        // Wir holen den Anbieter und seine durchschnittlichen Bewertungen
        $vendor = $vendorModel->getVendorsWithAverageRatings($uuid);

        if (empty($vendor)) {
            return $this->failNotFound('Anbieter nicht gefunden.');
        }

        $data = ['vendor' => $vendor[0]]; // getVendorsWith... gibt ein Array zurück

        // Wir geben die "Content-Only"-View für unser Modal zurück
        return view('vendor/show_content_only', $data);
    }
}
