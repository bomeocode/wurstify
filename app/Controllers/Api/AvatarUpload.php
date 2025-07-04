<?php

namespace App\Controllers\Api;

use CodeIgniter\RESTful\ResourceController;

class AvatarUpload extends ResourceController
{
    public function upload()
    {
        $validationRule = [
            'image' => [
                'label' => 'Bilddatei',
                'rules' => 'uploaded[image]|is_image[image]|max_size[image,20480]',
            ],
        ];

        if (!$this->validate($validationRule)) {
            return $this->failValidationErrors($this->validator->getErrors());
        }

        $img = $this->request->getFile('image');

        if ($img !== null && !$img->hasMoved()) {
            $newName = $img->getRandomName();
            $img->move(FCPATH . 'uploads/avatars', $newName);
            return $this->respondCreated(['filename' => $newName]);
        }
        return $this->failServerError('Die Datei konnte nicht verschoben werden.');
    }
}
