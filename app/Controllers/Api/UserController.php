<?php

namespace App\Controllers\Api;

use CodeIgniter\RESTful\ResourceController;
use App\Models\UserModel;

class UserController extends ResourceController
{
    public function show($id = null)
    {
        $users = new UserModel();
        // Wir verwenden unsere erweiterte User-Entität!
        $user = $users->find($id);

        if (empty($user)) {
            return $this->failNotFound('Benutzer nicht gefunden.');
        }

        // Wir übergeben das User-Objekt an die View
        return view('users/card_content', ['user' => $user]);
    }
}
