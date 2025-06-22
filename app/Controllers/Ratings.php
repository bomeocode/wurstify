<?php

namespace App\Controllers;

use App\Models\RatingModel;
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

        // 1. Validierungsregeln bleiben gleich
        $rules = [
            'vendor_name' => 'required|max_length[255]',
            'rating_appearance' => 'required|in_list[1,2,3,4,5]',
            'rating_taste' => 'required|in_list[1,2,3,4,5]',
            'rating_presentation' => 'required|in_list[1,2,3,4,5]',
            'rating_price' => 'required|in_list[1,2,3,4,5]',
            'rating_service' => 'required|in_list[1,2,3,4,5]',
        ];

        if (!$this->validate($rules)) {
            $errors = $this->validator->getErrors();
            session()->setFlashdata('toast', [
                'message' => array_values($errors)[0],
                'type'    => 'error'
            ]);
            return redirect()->back()->withInput();
            //return redirect()->back()->withInput()->with('error', array_values($errors)[0]);
        }

        $latitude = $this->request->getPost('latitude');
        $longitude = $this->request->getPost('longitude');
        $manualAddress = $this->request->getPost('address_manual');

        // NEU: Geocoding-Logik mit der Google API
        if (empty($latitude) && !empty($manualAddress)) {
            try {
                $apiKey = getenv('google.apiKey'); // API-Schlüssel sicher aus .env laden
                if (empty($apiKey)) {
                    throw new \Exception('Google API Key not found in .env file.');
                }

                $client = \Config\Services::curlrequest([
                    'baseURI' => 'https://maps.googleapis.com/maps/api/',
                    'timeout' => 5,
                ]);

                $response = $client->get('geocode/json', [
                    'query' => [
                        'address' => $manualAddress,
                        'key'     => $apiKey,
                        'region'  => 'de' // Ergebnisse für Deutschland bevorzugen
                    ],
                ]);

                if ($response->getStatusCode() === 200) {
                    $result = json_decode($response->getBody(), true);

                    // Google's Antwort-Struktur prüfen
                    if ($result['status'] === 'OK' && !empty($result['results'])) {
                        $firstResult = $result['results'][0];

                        // Wir akzeptieren nur Ergebnisse, die eine gewisse Genauigkeit haben.
                        // 'APPROXIMATE' deutet auf ein ungenaues Ergebnis hin.
                        if ($firstResult['geometry']['location_type'] === 'APPROXIMATE') {
                            // Adresse ist zu ungenau (z.B. nur "Deutschland")
                            $toastData = [
                                'message' => 'Die Adresse ist zu ungenau. Bitte gib mehr Details an.',
                                'type'    => 'danger'
                            ];
                            return redirect()->back()->withInput()->with('toast', $toastData);
                            // return redirect()->back()->withInput()->with('error', 'Die Adresse ist zu ungenau. Bitte geben Sie mehr Details an.');
                        }

                        // Erfolg! Koordinaten extrahieren.
                        $location = $firstResult['geometry']['location'];
                        $latitude = $location['lat'];
                        $longitude = $location['lng'];
                    } else {
                        // Adresse nicht gefunden (Status ist z.B. 'ZERO_RESULTS')
                        $toastData = [
                            'message' => 'Die eingegebene Adresse konnte nicht gefunden werden.',
                            'type'    => 'danger'
                        ];
                        return redirect()->back()->withInput()->with('toast', $toastData);
                        //return redirect()->back()->withInput()->with('error', 'Die eingegebene Adresse konnte nicht gefunden werden.');
                    }
                } else {
                    $toastData = [
                        'message' => 'Der Google Geocoding-Dienst ist zurzeit nicht erreichbar.',
                        'type'    => 'danger'
                    ];
                    return redirect()->back()->withInput()->with('toast', $toastData);
                    //return redirect()->back()->withInput()->with('error', 'Der Google Geocoding-Dienst ist zurzeit nicht erreichbar.');
                }
            } catch (\Exception $e) {
                log_message('error', '[Geocoding] ' . $e->getMessage());
                $toastData = [
                    'message' => 'Ein technischer Fehler bei der Adressprüfung ist aufgetreten.',
                    'type'    => 'danger'
                ];
                return redirect()->back()->withInput()->with('toast', $toastData);
                // return redirect()->back()->withInput()->with('error', 'Ein technischer Fehler bei der Adressprüfung ist aufgetreten.');
            }
        }

        // Daten für die Datenbank vorbereiten (jetzt mit potenziell neuen Koordinaten)
        $data = [
            'user_id'             => auth()->id(),
            'vendor_name'         => $this->request->getPost('vendor_name'),
            'rating_appearance'   => $this->request->getPost('rating_appearance'),
            'rating_taste'        => $this->request->getPost('rating_taste'),
            'rating_presentation' => $this->request->getPost('rating_presentation'),
            'rating_price'        => $this->request->getPost('rating_price'),
            'rating_service'      => $this->request->getPost('rating_service'),
            'comment'             => $this->request->getPost('comment'),
            'latitude'            => $latitude,
            'longitude'           => $longitude,
            'address_manual'      => $manualAddress,
        ];

        // 4. Daten speichern UND den Erfolg prüfen
        $ratingModel = new \App\Models\RatingModel();

        if ($ratingModel->insert($data)) {
            $toastData = [
                'message' => 'Vielen Dank für deine Bewertung!',
                'type'    => 'success'
            ];
            return redirect()->to('/')->with('toast', $toastData);;
        }

        $toastData = [
            'message' => 'Beim Speichern der Bewertung ist ein Fehler aufgetreten.',
            'type'    => 'error'
        ];
        return redirect()->back()->with('toast', $toastData);;
    }
}
