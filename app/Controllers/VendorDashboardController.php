<?php

namespace App\Controllers;

use App\Models\VendorModel;

class VendorDashboardController extends BaseController
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

        return view('vendor_dashboard/index', ['vendor' => $this->vendor]);
    }

    public function edit()
    {
        if (!$this->vendor) {
            return redirect()->to('/')->with('error', 'Ihnen ist kein Anbieter zugewiesen.');
        }

        return view('vendor_dashboard/edit', [
            'vendor' => $this->vendor,
            'opening_hours' => json_decode($this->vendor['opening_hours'], true) ?? [],
        ]);
    }

    public function update()
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
}
