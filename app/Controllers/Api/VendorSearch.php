<?php

namespace App\Controllers\Api;

use CodeIgniter\RESTful\ResourceController;
use App\Models\VendorModel;

class VendorSearch extends ResourceController
{
    public function index()
    {
        $vendorModel = new VendorModel();

        $lat = $this->request->getGet('lat');
        $lon = $this->request->getGet('lon');
        $query = $this->request->getGet('q');

        // Fall 1: Suche nach Koordinaten (vom Ortungs-Button)
        if ($lat && $lon) {
            // Wir suchen in einem größeren Radius, z.B. 25km
            $vendors = $vendorModel->findAllNearby((float)$lat, (float)$lon, 25);
            return $this->respond($vendors);
        }

        // Fall 2: Suche nach Text/PLZ (vom Eingabefeld)
        if ($query) {
            // Hierfür müssen wir die Adresse zuerst in Koordinaten umwandeln (geocoden)
            // HINWEIS: Dieser Code-Teil ist fast identisch zu dem aus Ihrem RatingsController
            try {
                $apiKey = getenv('google.apiKey');
                $client = \Config\Services::curlrequest(['baseURI' => 'https://maps.googleapis.com/maps/api/']);
                $response = $client->get('geocode/json', ['query' => ['address' => $query, 'key' => $apiKey, 'components' => 'country:DE']]);
                $result = json_decode($response->getBody(), true);

                if ($result['status'] === 'OK' && !empty($result['results'])) {
                    $location = $result['results'][0]['geometry']['location'];

                    //dd($location);

                    // Jetzt wo wir Koordinaten haben, suchen wir nahegelegene Anbieter
                    $vendors = $vendorModel->findAllNearby((float)$location['lat'], (float)$location['lng'], 50);
                    return $this->respond($vendors);
                }
            } catch (\Exception $e) {
                return $this->failServerError('Fehler bei der Adress-Suche.');
            }
        }

        // Wenn keine Parameter angegeben wurden, eine leere Liste zurückgeben
        return $this->respond([]);
    }
}
