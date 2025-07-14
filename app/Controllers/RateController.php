<?php

namespace App\Controllers;

use App\Models\VendorModel;
use App\Models\RatingModel;

class RateController extends BaseController
{
    /**
     * Zeigt das QR-Code-Bewertungsformular an.
     */
    public function index(string $vendorSlug)
    {
        $vendorModel = new VendorModel();
        $vendor = $vendorModel->findBySlug($vendorSlug);

        if (!$vendor) {
            return view('errors/html/error_404', ['message' => 'Dieser QR-Code ist ungültig.']);
        }

        $token = bin2hex(random_bytes(16));
        session()->set('qr_token', $token);

        // Wir laden jetzt die neue, saubere View
        return view('rate/qr_form', [
            'vendor' => $vendor,
            'qr_token' => $token
        ]);
    }

    /**
     * Speichert die Bewertung aus dem QR-Code-Formular.
     */
    public function store(string $vendorSlug)
    {
        // 1. Anbieter finden
        $vendorModel = new VendorModel();
        $vendor = $vendorModel->findBySlug($vendorSlug);
        if (!$vendor) {
            return view('errors/html/error_404', ['message' => 'Anbieter nicht gefunden.']);
        }

        // 2. Sicherheits-Token prüfen
        $sentToken = $this->request->getPost('qr_token');
        $sessionToken = session()->get('qr_token');
        if (empty($sentToken) || empty($sessionToken) || $sentToken !== $sessionToken) {
            return redirect()->back()->with('error', 'Ungültige Anfrage. Bitte versuchen Sie es erneut.');
        }

        // 3. Validierung der Eingaben
        $rules = [
            'qr_nickname' => 'required|string|max_length[100]',
            'rating_appearance' => 'required|in_list[1,2,3,4,5]',
            'rating_presentation' => 'required|in_list[1,2,3,4,5]',
            'rating_taste' => 'required|in_list[1,2,3,4,5]',
            'rating_price' => 'required|in_list[1,2,3,4,5]',
            'rating_service' => 'required|in_list[1,2,3,4,5]',
            'comment' => 'permit_empty|string|max_length[2000]',
        ];
        if (!$this->validate($rules)) {
            return redirect()->back()->withInput()->with('errors', $this->validator->getErrors());
        }

        // 4. Bewertung speichern
        $ratingModel = new RatingModel();
        $ratingData = [
            'vendor_id'     => $vendor['id'],
            'user_id'       => null, // Wichtig: Keine User-ID für anonyme Bewertungen
            'type'          => 'qr_code',
            'qr_nickname'   => $this->request->getPost('qr_nickname'),
            'rating_appearance'  => $this->request->getPost('rating_appearance'),
            'rating_presentation'  => $this->request->getPost('rating_presentation'),
            'rating_taste'  => $this->request->getPost('rating_taste'),
            'rating_price'  => $this->request->getPost('rating_price'),
            'rating_service'  => $this->request->getPost('rating_service'),
            'comment'       => $this->request->getPost('comment'),
        ];

        if ($ratingModel->save($ratingData)) {
            // Token in der Session ungültig machen, um Doppel-Bewertungen zu verhindern
            session()->remove('qr_token');

            // Zeigt eine einfache "Danke"-Seite an.
            return view('rate/thank_you', ['vendorName' => $vendor['name']]);
        }

        return redirect()->back()->with('error', 'Ihre Bewertung konnte nicht gespeichert werden.');
    }
}
