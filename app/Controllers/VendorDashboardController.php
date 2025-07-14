<?php

namespace App\Controllers;

use CodeIgniter\RESTful\ResourceController;
use App\Models\VendorModel;

class VendorDashboardController extends ResourceController
{
    private $vendor;
    private $vendorModel;
    private $userId;

    public function __construct()
    {
        $this->vendorModel = new VendorModel();
        $this->userId = auth()->id();

        // Finde den Anbieter, der dem aktuell eingeloggten Benutzer gehört.
        $this->vendor = $this->vendorModel->where('owner_user_id', $this->userId)->asArray()->first();
    }

    public function index()
    {
        if (!$this->vendor) {
            return redirect()->to('/')->with('error', 'Ihnen ist kein Anbieter zugewiesen.');
        }

        // Wir holen uns das RatingModel, um die Statistiken zu berechnen
        $ratingModel = new \App\Models\RatingModel();

        // Wir berechnen die Durchschnittsbewertungen für den Anbieter
        $stats = $ratingModel
            ->select('
                AVG(rating_taste) as avg_taste, 
                AVG(rating_appearance) as avg_appearance, 
                AVG(rating_presentation) as avg_presentation, 
                AVG(rating_price) as avg_price, 
                AVG(rating_service) as avg_service,
                COUNT(id) as total_ratings')
            ->where('vendor_id', $this->vendor['id'])
            ->first();

        // Wir fügen die Statistik-Daten zum Vendor-Array hinzu
        $vendorData = array_merge($this->vendor, $stats ?? []);
        return view('vendor_dashboard/index', [
            'vendor' => $vendorData,
            'currentController' => 'VendorDashboardController',
            'currentMethod' => 'index',
        ]);
    }

    public function edit($id = null)
    {
        if (!$this->vendor) {
            return redirect()->to('/')->with('error', 'Ihnen ist kein Anbieter zugewiesen.');
        }

        return view('vendor_dashboard/edit', [
            'vendor' => $this->vendor,
            'opening_hours' => json_decode($this->vendor['opening_hours'], true) ?? [],
            'currentController' => 'VendorDashboardController',
            'currentMethod' => 'edit',
        ]);
    }

    public function update($id = null)
    {
        if (!$this->vendor) {
            return redirect()->to('/')->with('error', 'Ihnen ist kein Anbieter zugewiesen.');
        }

        $rules = [
            'name'          => 'required|string|max_length[255]',
            'description'   => 'permit_empty|string',
            'website_url'   => 'permit_empty|valid_url_strict',
            'opening_hours' => 'permit_empty|is_array',
            'social_media'  => 'permit_empty|is_array',
            'logo_image'    => 'permit_empty|string',
            'cover_image'   => 'permit_empty|string',
        ];

        if (!$this->validate($rules)) {
            // HIER IST DIE KORREKTUR:
            // Wir nehmen die erste Fehlermeldung und packen sie in einen Toast.
            $error = array_values($this->validator->getErrors())[0];
            return redirect()->back()->withInput()->with('toast', ['message' => $error, 'type' => 'danger']);
        }

        $updateData = [
            'name'          => $this->request->getPost('name'),
            'description'   => $this->request->getPost('description'),
            'website_url'   => $this->request->getPost('website_url'),
            'opening_hours' => json_encode($this->request->getPost('opening_hours')),
            'social_media'  => json_encode($this->request->getPost('social_media')),
            'logo_image'    => $this->request->getPost('logo_image'),
            'cover_image'   => $this->request->getPost('cover_image'),
        ];

        if ($this->vendorModel->update($this->vendor['id'], $updateData)) {
            return redirect()->to(route_to('vendor_dashboard'))
                ->with('toast', ['message' => 'Ihre Daten wurden erfolgreich aktualisiert.', 'type' => 'success']);
        }

        return redirect()->back()->withInput()
            ->with('toast', ['message' => 'Aktualisierung fehlgeschlagen.', 'type' => 'danger']);
    }

    public function ajaxImageUpload()
    {
        $validationRule = [
            'image' => [
                'label' => 'Bilddatei',
                'rules' => 'uploaded[image]|is_image[image]|max_size[image,4096]', // 4MB Limit
            ],
        ];
        if (! $this->validate($validationRule)) {
            // Wir verwenden hier jetzt den ResourceController, daher sind diese Methoden verfügbar
            return $this->failValidationErrors($this->validator->getErrors());
        }

        $img = $this->request->getFile('image');

        if ($img !== null && !$img->hasMoved()) {
            $path = FCPATH . 'uploads/vendors/';
            if (!is_dir($path)) {
                mkdir($path, 0777, true);
            }

            $newName = $img->getRandomName();
            $img->move($path, $newName);

            return $this->respondCreated(['filename' => $newName]);
        }

        return $this->failServerError('Datei konnte nicht auf dem Server gespeichert werden.');
    }
}
