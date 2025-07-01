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

        return view('admin/vendors/edit', ['vendor' => $vendor]);
    }

    public function update($id = null)
    {
        $vendorModel = new VendorModel();
        $vendor = $vendorModel->find($id);
        if (!$vendor) {
            return redirect()->to('admin/vendors')->with('error', 'Anbieter nicht gefunden.');
        }

        $rules = [
            'name'      => 'required|max_length[255]',
            'address'   => 'required|max_length[255]',
            'latitude'  => 'required|decimal',
            'longitude' => 'required|decimal',
        ];

        if (!$this->validate($rules)) {
            return redirect()->back()->withInput()->with('errors', $this->validator->getErrors());
        }

        $postData = $this->request->getPost();

        if ($vendorModel->update($id, $postData)) {
            return redirect()->to('admin/vendors')->with('message', 'Anbieter erfolgreich aktualisiert.');
        }

        return redirect()->back()->withInput()->with('error', 'Fehler beim Speichern des Anbieters.');
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
}
