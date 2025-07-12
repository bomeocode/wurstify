<?php

namespace App\Controllers\Api;

use CodeIgniter\RESTful\ResourceController;
use App\Models\UserModel;

class UserController extends ResourceController
{
    // In app/Controllers/Api/UserController.php

    public function show($id = null)
    {
        $userModel = new \App\Models\UserModel();
        $user = $userModel->find($id);

        if (empty($user)) {
            return $this->failNotFound('Benutzer nicht gefunden.');
        }

        $ratingModel = new \App\Models\RatingModel();

        // Wir sammeln alle Daten, die unsere Komponente benötigt
        $dataForComponent = [
            'user'        => $user,
            'level'       => $user->getLevel(),
            'ratingCount' => $ratingModel->where('user_id', $id)->countAllResults()
        ];

        // Wir übergeben dieses eine Datenpaket an die View
        return view('users/card_content', ['data' => $dataForComponent]);
    }
}
