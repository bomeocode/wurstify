<?php

namespace App\Controllers\Api;

use CodeIgniter\RESTful\ResourceController;
use App\Models\UserModel;

class UserController extends ResourceController
{
    public function show($id = null)
    {
        $userModel = new UserModel();
        // Wir verwenden unsere erweiterte User-Entität!
        $user = $userModel->find($id);

        if (empty($user)) {
            return $this->failNotFound('Benutzer nicht gefunden.');
        }

        $ratingModel = new \App\Models\RatingModel();
        $ratingCount = $ratingModel->where('user_id', $id)->countAllResults();

        // Wir übergeben sowohl den Benutzer als auch die Anzahl seiner Ratings an die View
        $data = [
            'user'        => $user,
            'ratingCount' => $ratingCount,
        ];

        return view('users/card_content', $data);
    }
}
