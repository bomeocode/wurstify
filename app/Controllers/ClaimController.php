<?php

namespace App\Controllers;

use App\Models\VendorModel;

class ClaimController extends BaseController
{
    public function showForm($vendorUuid)
    {
        $vendorModel = new VendorModel();
        $vendor = $vendorModel->where('uuid', $vendorUuid)->first();

        if (!$vendor) {
            // Zeige eine Fehlermeldung oder leite um
            return redirect()->to('/')->with('error', 'Anbieter nicht gefunden.');
        }

        // Wir laden die View für das Formular, das sich im Offcanvas öffnet
        return view('claim/form', ['vendor' => $vendor]);
    }
}
