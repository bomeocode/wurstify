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

    // In app/Controllers/Api/VendorController.php

    // In app/Controllers/Api/VendorController.php

    public function ratings($uuid = null)
    {
        $vendorModel = new \App\Models\VendorModel();
        $vendor = $vendorModel->asArray()->where('uuid', $uuid)->first();

        if (!$vendor) {
            return $this->failNotFound('Anbieter nicht gefunden.');
        }

        $ratingModel = new \App\Models\RatingModel();

        $perPage = 10;
        $page = (int) ($this->request->getGet('page') ?? 1);

        // 1. Gesamtanzahl holen
        $total = $ratingModel->countForVendor($vendor['id']);

        // 2. Pager-Daten berechnen
        $pageCount = (int) ceil($total / $perPage);

        // +++ DEBUG-BLOCK START +++
        // Wir stoppen die Ausführung hier und schauen uns die berechneten Werte an.
        $debugData = [
            'status' => 'Debugging Paginierungs-Daten...',
            'vendor_id_used_for_count' => $vendor['id'],
            'total_ratings_found_for_vendor' => $total,
            'calculated_page_count' => $pageCount,
            'current_page_requested' => $page
        ];
        //dd($debugData);
        // +++ DEBUG-BLOCK ENDE +++

        $offset = ($page - 1) * $perPage;
        $ratings = $ratingModel->getPageForVendor($vendor['id'], $perPage, $offset);

        $pager_data = [
            'currentPage' => $page,
            'pageCount'   => $pageCount,
            'perPage'     => $perPage,
            'total'       => $total,
        ];

        $data = ['ratings' => $ratings, 'pager'   => $pager_data,];
        return $this->respond($data);
    }

    // In app/Controllers/Api/VendorController.php

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
