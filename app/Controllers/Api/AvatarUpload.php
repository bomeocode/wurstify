<?php

namespace App\Controllers\Api;

use CodeIgniter\RESTful\ResourceController;

class AvatarUpload extends ResourceController
{
    public function upload()
    {
        if (!auth()->loggedIn()) {
            return $this->failUnauthorized('Bitte zuerst einloggen.');
        }

        $validationRule = [
            'image' => [
                'label' => 'Image File',
                'rules' => 'uploaded[image]|is_image[image]|max_size[image,2048]',
            ],
        ];

        if (!$this->validate($validationRule)) {
            return $this->failValidationErrors($this->validator->getErrors());
        }

        $img = $this->request->getFile('image');

        if (!$img->hasMoved()) {
            $newName = $img->getRandomName();
            // In einen anderen Ordner speichern!
            $img->move(FCPATH . 'uploads/avatars', $newName);
            return $this->respondCreated(['filename' => $newName]);
        }
        return $this->failServerError('Die Datei konnte nicht verschoben werden.');
    }
}
