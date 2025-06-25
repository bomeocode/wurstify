<?php

namespace App\Controllers;

use App\Models\RatingModel;
use App\Models\VendorModel;
use Config\Services;

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

        $rules = [
            'vendor_name' => 'required|max_length[255]',
            'rating_appearance' => 'required|in_list[1,2,3,4,5]',
            'rating_taste' => 'required|in_list[1,2,3,4,5]',
            'rating_presentation' => 'required|in_list[1,2,3,4,5]',
            'rating_price' => 'required|in_list[1,2,3,4,5]',
            'rating_service' => 'required|in_list[1,2,3,4,5]',
        ];

        if (!$this->validate($rules)) {
            $toastData = ['message' => 'Bitte füllen Sie alle Bewertungs-Sterne und den Namen des Anbieters aus.', 'type' => 'danger'];
            return redirect()->back()->withInput()->with('toast', $toastData);
        }

        $vendorModel = new VendorModel();
        $ratingModel = new RatingModel();

        $manualAddress = $this->request->getPost('address_manual');
        $vendorName = $this->request->getPost('vendor_name');
        $lat = null;
        $lon = null;

        if (!empty($manualAddress)) {
            try {
                $apiKey = getenv('google.apiKey');
                if (empty($apiKey)) {
                    throw new \Exception('Google API Key not found in .env file.');
                }

                $client = Services::curlrequest([
                    'baseURI' => 'https://maps.googleapis.com/maps/api/',
                    'timeout' => 5,
                ]);

                $response = $client->get('geocode/json', [
                    'query' => ['address' => $manualAddress, 'key' => $apiKey, 'region' => 'de'],
                ]);

                if ($response->getStatusCode() === 200) {
                    $result = json_decode($response->getBody(), true);

                    if ($result['status'] === 'OK' && !empty($result['results'])) {
                        $firstResult = $result['results'][0];
                        if (isset($firstResult['geometry']['location_type']) && $firstResult['geometry']['location_type'] === 'APPROXIMATE') {
                            $toastData = ['message' => 'Die Adresse ist zu ungenau. Bitte geben Sie mehr Details an.', 'type' => 'warning'];
                            return redirect()->back()->withInput()->with('toast', $toastData);
                        }
                        $location = $firstResult['geometry']['location'];
                        $lat = $location['lat'];
                        $lon = $location['lng'];
                    } else {
                        $toastData = ['message' => 'Die eingegebene Adresse konnte nicht gefunden werden.', 'type' => 'danger'];
                        return redirect()->back()->withInput()->with('toast', $toastData);
                    }
                } else {
                    $toastData = ['message' => 'Der Adress-Dienst ist zurzeit nicht erreichbar.', 'type' => 'danger'];
                    return redirect()->back()->withInput()->with('toast', $toastData);
                }
            } catch (\Exception $e) {
                log_message('error', '[Geocoding] ' . $e->getMessage());
                $toastData = ['message' => 'Ein technischer Fehler bei der Adressprüfung ist aufgetreten.', 'type' => 'danger'];
                return redirect()->back()->withInput()->with('toast', $toastData);
            }
        }

        if (empty($lat) || empty($lon)) {
            $toastData = ['message' => 'Bitte geben Sie eine gültige Adresse für den Anbieter an.', 'type' => 'danger'];
            return redirect()->back()->withInput()->with('toast', $toastData);
        }

        $existingVendor = $vendorModel->findNearby($lat, $lon);
        $vendorId = null;

        if ($existingVendor) {
            $vendorId = $existingVendor['id'];
        } else {
            $newVendorData = ['name' => $vendorName, 'address' => $manualAddress, 'latitude' => $lat, 'longitude' => $lon];
            $vendorId = $vendorModel->insert($newVendorData);
            if (!$vendorId) {
                $toastData = ['message' => 'Der neue Anbieter konnte nicht in der Datenbank gespeichert werden.', 'type' => 'danger'];
                return redirect()->back()->withInput()->with('toast', $toastData);
            }
        }

        $ratingData = [
            'user_id' => auth()->id(),
            'vendor_id' => $vendorId,
            'rating_appearance' => $this->request->getPost('rating_appearance'),
            'rating_taste' => $this->request->getPost('rating_taste'),
            'rating_presentation' => $this->request->getPost('rating_presentation'),
            'rating_price' => $this->request->getPost('rating_price'),
            'rating_service' => $this->request->getPost('rating_service'),
            'comment' => $this->request->getPost('comment'),
            'image1'    => $this->request->getPost('image1'),
            'image2'    => $this->request->getPost('image2'),
            'image3'    => $this->request->getPost('image3'),
        ];

        if ($ratingModel->insert($ratingData)) {
            $toastData = ['message' => 'Vielen Dank! Deine Bewertung wurde gespeichert.', 'type' => 'success'];
            return redirect()->to('/dashboard')->with('toast', $toastData);
        }

        $toastData = ['message' => 'Beim Speichern der finalen Bewertung ist ein Fehler aufgetreten.', 'type' => 'danger'];
        return redirect()->back()->withInput()->with('toast', $toastData);
    }
}
