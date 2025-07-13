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
    // In app/Controllers/Api/VendorController.php

    public function ratings($uuid = null)
    {
        $vendorModel = new \App\Models\VendorModel();
        $vendor = $vendorModel->where('uuid', $uuid)->first();

        if (!$vendor) {
            return $this->failNotFound('Anbieter nicht gefunden.');
        }

        $ratingModel = new \App\Models\RatingModel();
        $page = $this->request->getGet('page') ?? 1;
        $perPage = 10;

        // Wir holen die paginierten Datenobjekte
        $ratings = $ratingModel->getPageForVendor($vendor['id'], $perPage, ($page - 1) * $perPage);
        $totalRatings = $ratingModel->countForVendor($vendor['id']);

        // Rendere die HTML-Karten serverseitig
        $renderedRatings = [];
        foreach ($ratings as $rating) {
            // Wir übergeben den korrekten Kontext für die Anzeige in der Detailansicht
            $renderedRatings[] = view('partials/rating_card', ['rating' => $rating, 'context' => 'vendor_detail']);
        }

        // Erstelle das Pager-Objekt manuell
        $pagerData = [
            'currentPage' => (int)$page,
            'pageCount'   => (int)ceil($totalRatings / $perPage),
        ];

        // Gib das gerenderte HTML und den Pager als sauberes JSON zurück
        return $this->respond([
            'ratings_html' => $renderedRatings,
            'pager'        => $pagerData
        ]);
    }

    public function show($uuid = null)
    {
        $vendorModel = new \App\Models\VendorModel();
        $ratingModel = new \App\Models\RatingModel();

        $vendor = $vendorModel->asArray()->where('uuid', $uuid)->first();
        if (empty($vendor)) {
            return $this->failNotFound('Anbieter nicht gefunden.');
        }

        // Statistiken holen
        $stats = $ratingModel
            ->select('
                AVG(rating_taste) as avg_taste, 
                AVG(rating_appearance) as avg_appearance, 
                AVG(rating_presentation) as avg_presentation, 
                AVG(rating_price) as avg_price, 
                AVG(rating_service) as avg_service, 
                COUNT(id) as total_ratings')
            ->where('vendor_id', $vendor['id'])
            ->first();
        $vendor = array_merge($vendor, $stats ?? []);

        // Bewertungen holen
        $ratings = $ratingModel->getPageForVendor($vendor['id'], 10, 0);
        // Gesamtanzahl für den Pager holen
        $totalRatings = $ratingModel->countForVendor($vendor['id']);

        $renderedRatings = [];
        foreach ($ratings as $rating) {
            $renderedRatings[] = view('partials/rating_card', ['rating' => $rating, 'context' => 'vendor_detail']);
        }

        $pagerData = [
            'currentPage' => 1,
            'pageCount'   => (int)ceil($totalRatings / 10),
        ];

        $data = [
            'vendor'       => $vendor,
            'ratings_html' => $renderedRatings,
            'pager'        => $pagerData,
        ];

        return view('vendor/show_content_only', $data);
    }
}
