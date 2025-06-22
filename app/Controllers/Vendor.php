<?php

namespace App\Controllers;

use App\Models\VendorModel;

class Vendor extends BaseController
{
    public function show($uuid = null)
    {
        if (!$uuid) {
            throw \CodeIgniter\Exceptions\PageNotFoundException::forPageNotFound();
        }

        $vendorModel = new VendorModel();
        // Hier können wir die gleiche Funktion wie für die Karte wiederverwenden!
        $vendor = $vendorModel->where('uuid', $uuid)->getVendorsWithAverageRatings();

        if (empty($vendor)) {
            throw \CodeIgniter\Exceptions\PageNotFoundException::forPageNotFound();
        }

        $data = [
            // Wir übergeben das erste (und einzige) Ergebnis an die View
            'vendor' => $vendor[0]
        ];

        return view('vendor/show', $data);
    }
}
