<?php

namespace App\Controllers\Api;

use CodeIgniter\RESTful\ResourceController;
use App\Models\RatingModel;

class RatingController extends ResourceController
{
    public function show($id = null)
    {
        $ratingModel = new RatingModel();
        // Wir holen eine einzelne Bewertung, aber ebenfalls mit allen verknüpften Infos
        $rating = $ratingModel
            ->select('ratings.*, vendors.name as vendor_name, vendors.address as vendor_address, users.username')
            ->join('vendors', 'vendors.id = ratings.vendor_id', 'left')
            ->join('users', 'users.id = ratings.user_id', 'left')
            ->find($id);

        if (empty($rating)) {
            return $this->failNotFound('Bewertung nicht gefunden.');
        }

        // Für die Modal-Ansicht wollen wir nur den reinen HTML-Inhalt
        return view('feed/single_rating_content', ['rating' => $rating]);
    }
}
