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

    // In app/Controllers/Api/RatingController.php

    // In app/Controllers/Api/RatingController.php

    public function toggleVote($ratingId = null)
    {
        if (!auth()->loggedIn()) {
            return $this->failUnauthorized('Sie müssen angemeldet sein, um abzustimmen.');
        }

        $userId = auth()->id();
        $voteModel = new \App\Models\RatingVoteModel();
        $ratingModel = new \App\Models\RatingModel();
        $db = db_connect();

        // +++ HIER IST DIE KORREKTUR +++
        // Prüft, ob ein Eintrag für DIESEN Nutzer und DIESES Rating existiert.
        $existingVote = $voteModel->where('rating_id', $ratingId)
            ->where('user_id', $userId)
            ->first();

        $db->transStart();

        if ($existingVote) {
            // Stimme entfernen
            $voteModel->where('rating_id', $ratingId)->where('user_id', $userId)->delete();
            $ratingModel->where('id', $ratingId)->decrement('helpful_count');
            $voted = false;
        } else {
            // Stimme hinzufügen
            $voteModel->insert(['rating_id' => $ratingId, 'user_id' => $userId]);
            $ratingModel->where('id', $ratingId)->increment('helpful_count');
            $voted = true;
        }

        $db->transComplete();

        if ($db->transStatus() === false) {
            return $this->failServerError('Fehler bei der Abstimmung.');
        }

        $rating = $ratingModel->find($ratingId);
        $newCount = $rating ? $rating['helpful_count'] : 0;

        return $this->respond(['new_count' => $newCount, 'voted' => $voted]);
    }
}
