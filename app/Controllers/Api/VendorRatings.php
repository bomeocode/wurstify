<?php

namespace App\Controllers\Api;

use CodeIgniter\RESTful\ResourceController;
use App\Models\RatingModel;

class VendorRatings extends ResourceController
{
    public function index($vendor_uuid = null)
    {
        $ratingModel = new RatingModel();

        // Bewertungen mit Benutzerinfos holen, paginiert
        $ratings = $ratingModel
            ->select('ratings.*, users.username')
            ->join('users', 'users.id = ratings.user_id')
            ->join('vendors', 'vendors.id = ratings.vendor_id')
            ->where('vendors.uuid', $vendor_uuid)
            ->orderBy('ratings.created_at', 'DESC')
            ->paginate(10); // 10 Bewertungen pro Seite

        // Wichtig für unendliches Scrollen: Wir geben nur den HTML-Teil zurück
        $data = [
            'ratings' => $ratings,
            'pager'   => $ratingModel->pager,
        ];

        return $this->respond($data);
    }
}
