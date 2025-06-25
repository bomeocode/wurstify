<?php

namespace App\Controllers\Api;

use CodeIgniter\RESTful\ResourceController;

class RatingImageUpload extends ResourceController
{
    public function upload()
    {
        if (!auth()->loggedIn()) {
            return $this->failUnauthorized('Bitte zuerst einloggen.');
        }

        $validationRule = [
            'image' => [
                'label' => 'Image File',
                'rules' => 'uploaded[image]'
                    . '|is_image[image]'
                    . '|mime_in[image,image/jpg,image/jpeg,image/gif,image/png,image/webp]'
                    . '|max_size[image,20480]', // max. 2MB
            ],
        ];

        if (!$this->validate($validationRule)) {
            return $this->failValidationErrors($this->validator->getErrors());
        }

        $img = $this->request->getFile('image');

        if (!$img->hasMoved()) {
            // Erzeugt einen neuen, zufälligen Dateinamen
            $newName = $img->getRandomName();
            // Verschiebt die Datei in den public/uploads/ratings Ordner
            $img->move(FCPATH . 'uploads/ratings', $newName);

            return $this->respondCreated(['filename' => $newName]);
        }

        return $this->failServerError('Die Datei konnte nicht verschoben werden.');
    }

    public function delete($id = null)
    {
        if (!auth()->loggedIn()) {
            return $this->failUnauthorized('Bitte zuerst einloggen.');
        }

        // Wir erwarten JSON-Daten mit dem zu löschenden Dateinamen
        $json = $this->request->getJSON();
        $filename = $json->filename ?? null;

        if (empty($filename)) {
            return $this->failValidationErrors('Kein Dateiname angegeben.');
        }

        // WICHTIGE SICHERHEITSMASSNAHME:
        // Verhindert Directory Traversal-Angriffe (z.B. ../../.../andere_datei.txt)
        // basename() extrahiert nur den reinen Dateinamen.
        $sanitizedFilename = basename($filename);

        // Konstruiert den vollständigen und sicheren Pfad zur Datei
        $path = FCPATH . 'uploads/ratings/' . $sanitizedFilename;

        if (is_file($path)) {
            if (unlink($path)) {
                // Erfolg! Datei wurde gelöscht.
                return $this->respondDeleted(['status' => 'success', 'file' => $sanitizedFilename]);
            }
            // Fehler, falls die Datei nicht gelöscht werden konnte (z.B. wegen Berechtigungen)
            return $this->failServerError('Datei konnte nicht gelöscht werden.');
        }

        // Fehler, wenn die Datei gar nicht existiert
        return $this->failNotFound('Datei nicht gefunden.');
    }
}
