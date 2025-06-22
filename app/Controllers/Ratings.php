<?php

namespace App\Controllers;

use App\Models\RatingModel;

class Ratings extends BaseController
{
    // Zeigt das Bewertungsformular an
    public function new()
    {
        // Stellt sicher, dass der Benutzer eingeloggt ist
        if (!auth()->loggedIn()) {
            return redirect()->to('login');
        }

        return view('ratings/new'); // Wir erstellen diese View-Datei gleich
    }

    // Speichert eine neue Bewertung
    public function create()
    {
        if (!auth()->loggedIn()) {
            return redirect()->to('login');
        }

        // 1. Validierungsregeln bleiben gleich
        $rules = [
            'vendor_name' => 'required|max_length[255]',
            'rating_appearance' => 'required|in_list[1,2,3,4,5]',
            'rating_taste' => 'required|in_list[1,2,3,4,5]',
            'rating_presentation' => 'required|in_list[1,2,3,4,5]',
            'rating_price' => 'required|in_list[1,2,3,4,5]',
            'rating_service' => 'required|in_list[1,2,3,4,5]',
        ];

        // 2. Validierung durchführen
        if (!$this->validate($rules)) {
            // Bei Validierungsfehlern: Zurück zum Formular und Fehler in Flashdata speichern.
            // Wir übergeben die Fehler als einzelne Nachricht für den Toast.
            $errors = $this->validator->getErrors();
            //return redirect()->back()->withInput()->with('error', array_values($errors)[0]);
            session()->setFlashdata('toast', [
                'message' => array_values($errors)[0],
                'type'    => 'error'
            ]);
            return redirect()->back();
        }

        // 3. Daten für die Datenbank vorbereiten
        $data = [
            'user_id'             => auth()->id(),
            'vendor_name'         => $this->request->getPost('vendor_name'),
            'rating_appearance'   => $this->request->getPost('rating_appearance'),
            'rating_taste'        => $this->request->getPost('rating_taste'),
            'rating_presentation' => $this->request->getPost('rating_presentation'),
            'rating_price'        => $this->request->getPost('rating_price'),
            'rating_service'      => $this->request->getPost('rating_service'),
            'comment'             => $this->request->getPost('comment'),
            'latitude'            => $this->request->getPost('latitude') ?: null,
            'longitude'           => $this->request->getPost('longitude') ?: null,
            'address_manual'      => $this->request->getPost('address_manual'),
        ];

        // 4. Daten speichern UND den Erfolg prüfen
        $ratingModel = new \App\Models\RatingModel();

        if ($ratingModel->insert($data)) {
            session()->setFlashdata('toast', [
                'message' => 'Vielen Dank für deine Bewertung!',
                'type'    => 'success'
            ]);
            return redirect()->to('/');
            // ERFOLG! Zurückleiten mit Erfolgsmeldung für den Toast.
            // return redirect()->to('/')->with('message', 'Vielen Dank für deine Bewertung!');
        }

        // FEHLER BEIM SPEICHERN! Zurückleiten mit Fehlermeldung für den Toast.
        //return redirect()->back()->withInput()->with('error', 'Beim Speichern der Bewertung ist ein Fehler aufgetreten.');
        session()->setFlashdata('toast', [
            'message' => 'Beim Speichern der Bewertung ist ein Fehler aufgetreten.',
            'type'    => 'error'
        ]);
        return redirect()->back();
    }
}
