<?php

namespace App\Controllers\Admin;

use App\Controllers\BaseController;
use App\Models\VendorModel;

class VendorController extends BaseController
{
    public function index()
    {
        $vendorModel = new VendorModel();
        $searchTerm = $this->request->getGet('q');

        $data = [
            'vendors'    => $vendorModel->getPaginatedVendors($searchTerm),
            'pager'      => $vendorModel->pager,
            'searchTerm' => $searchTerm,
        ];
        return view('admin/vendors/index', $data);
    }

    public function edit($id = null)
    {
        $vendorModel = new VendorModel();
        $vendor = $vendorModel->find($id);

        if (!$vendor) {
            return redirect()->to('admin/vendors')->with('error', 'Anbieter nicht gefunden.');
        }

        $data = [
            'vendor' => $vendor,
            'opening_hours' => json_decode($vendor['opening_hours'], true) ?? []
        ];

        return view('admin/vendors/edit', $data);
    }

    public function update($id = null)
    {
        $vendorModel = new VendorModel();
        $vendor = $vendorModel->find($id);
        if (empty($vendor)) {
            return redirect()->back()->with('toast', ['message' => 'Anbieter nicht gefunden.', 'type' => 'danger']);
        }

        $rules = [
            'name'      => 'required|max_length[255]',
            'address'   => 'required|max_length[255]',
            'latitude'  => 'required|decimal',
            'longitude' => 'required|decimal',
            'category'  => 'required|in_list[stationär,mobil]',
            'description'   => 'permit_empty|string',
            'website_url'   => 'permit_empty|valid_url_strict',
            'opening_hours' => 'permit_empty|is_array',
            'social_media'  => 'permit_empty|is_array',
        ];

        if (!$this->validate($rules)) {
            return redirect()->back()->withInput()->with('errors', $this->validator->getErrors());
        }

        $updateData = [
            'name'          => $this->request->getPost('name'),
            'address'       => $this->request->getPost('address'),
            'category'      => $this->request->getPost('category'),
            'description'   => $this->request->getPost('description'),
            'website_url'   => $this->request->getPost('website_url'),
            'latitude'     => $this->request->getPost('latitude'),
            'longitude'     => $this->request->getPost('longitude'),
            'opening_hours' => json_encode($this->request->getPost('opening_hours')),
            'logo_image'    => $this->request->getPost('logo_image'),   // Dieses Feld hat gefehlt
            'cover_image'   => $this->request->getPost('cover_image'),
            'social_media'  => json_encode($this->request->getPost('social_media')),
        ];

        if ($vendorModel->update($id, $updateData)) {
            return redirect()->to(site_url('admin/vendors'))
                ->with('toast', ['message' => 'Anbieter erfolgreich aktualisiert.', 'type' => 'success']);
        }

        return redirect()->back()->withInput()
            ->with('toast', ['message' => 'Aktualisierung fehlgeschlagen.', 'type' => 'danger']);
    }

    public function delete($id = null)
    {
        $vendorModel = new VendorModel();
        if ($vendorModel->find($id)) {
            // Hinweis: Ratings, die mit diesem Vendor verknüpft sind,
            // werden durch die Datenbank-Regel auf NULL gesetzt.
            $vendorModel->delete($id);
            return redirect()->to('admin/vendors')->with('message', 'Anbieter erfolgreich gelöscht.');
        }
        return redirect()->to('admin/vendors')->with('error', 'Anbieter nicht gefunden.');
    }

    public function ajaxImageUpload()
    {
        // Wir validieren das erste Bild, das wir in der Anfrage finden
        $validationRule = [
            'image' => [
                'label' => 'Bilddatei',
                'rules' => 'uploaded[image]|is_image[image]|max_size[image,4096]', // 4MB Limit
            ],
        ];
        if (! $this->validate($validationRule)) {
            return $this->response->setStatusCode(400)->setJSON(['error' => $this->validator->getErrors()['image']]);
        }

        $img = $this->request->getFile('image');

        if ($img !== null && !$img->hasMoved()) {
            // Wir erstellen einen neuen, sicheren Ordner für die Vendor-Bilder
            $path = FCPATH . 'uploads/vendors/';
            if (!is_dir($path)) {
                mkdir($path, 0777, true);
            }

            $newName = $img->getRandomName();
            $img->move($path, $newName);

            return $this->response->setJSON(['filename' => $newName]);
        }

        return $this->response->setStatusCode(500)->setJSON(['error' => 'Datei konnte nicht auf dem Server gespeichert werden.']);
    }
}
