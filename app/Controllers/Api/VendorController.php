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

        $ratingModel = new \App\Models\RatingModel();
        $page = $this->request->getGet('page') ?? 1;
        $perPage = 10;

        // Wir holen die paginierten Datenobjekte vom Model.
        // Wichtig: Wir verwenden die gleiche Model-Funktion wie der Feed, wenn möglich,
        // oder eine, die dieselben Datenfelder zurückgibt.
        $ratings = $ratingModel->getPageForVendor($vendor['id'], $perPage, ($page - 1) * $perPage);
        $totalRatings = $ratingModel->where('vendor_id', $vendor['id'])->countAllResults();

        // Wir rendern für jede Bewertung die Partial View
        $renderedRatings = [];
        foreach ($ratings as $rating) {
            // WICHTIG: Wir übergeben den korrekten Kontext!
            $data = [
                'rating'  => $rating,
                'context' => 'vendor_detail'
            ];
            $renderedRatings[] = view('partials/rating_card', $data);
        }

        // Wir erstellen das Pager-Objekt manuell
        $pagerData = [
            'currentPage' => (int)$page,
            'pageCount'   => (int)ceil($totalRatings / $perPage),
        ];

        // Wir geben das gerenderte HTML und den Pager zurück
        return $this->respond([
            'ratings_html' => $renderedRatings,
            'pager'        => $pagerData
        ]);
    }

    /**
     * Liefert die HTML-Ansicht für die Details eines einzelnen Anbieters.
     */
    // In app/Controllers/Api/VendorController.php

    // In app/Controllers/Api/VendorController.php

    // In app/Controllers/Api/VendorController.php

    public function show($uuid = null)
    {
        $vendorModel = new \App\Models\VendorModel();
        $ratingModel = new \App\Models\RatingModel();

        // Wir holen uns den Vendor als Array
        $vendor = $vendorModel
            ->select('vendors.*, vendors.name as vendor_name')
            ->asArray()
            ->where('uuid', $uuid)
            ->first();

        if (empty($vendor)) {
            return $this->failNotFound('Anbieter nicht gefunden.');
        }

        // Wir berechnen die Durchschnittsbewertungen
        $stats = $ratingModel
            ->select('AVG(rating_taste) as avg_taste, AVG(rating_appearance) as avg_appearance, AVG(rating_presentation) as avg_presentation, AVG(rating_price) as avg_price, AVG(rating_service) as avg_service, COUNT(id) as total_ratings')
            ->where('vendor_id', $vendor['id'])
            ->first();

        // Wir fügen die Statistik-Daten zum Vendor-Array hinzu
        $vendor = array_merge($vendor, $stats);

        $ratings = $ratingModel->getPageForVendor($vendor['id'], 10, 0);
        $totalRatings = $ratingModel->countForVendor($vendor['id']);

        $renderedRatings = [];
        foreach ($ratings as $rating) {
            $renderedRatings[] = view('partials/rating_card', ['rating' => $rating, 'context' => 'vendor_detail']);
        }

        $pagerData = [
            'currentPage' => 1,
            'pageCount'   => (int)ceil($totalRatings / 10)
        ];

        $data = [
            'vendor'       => $vendor,
            'ratings_html' => $renderedRatings,
            'pager'        => $pagerData
        ];

        return view('vendor/show_content_only', $data);
    }
}
