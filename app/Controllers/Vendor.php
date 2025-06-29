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
        $vendor = $vendorModel->where('uuid', $uuid)->getVendorsWithAverageRatings();

        if (empty($vendor)) {
            throw \CodeIgniter\Exceptions\PageNotFoundException::forPageNotFound();
        }

        $data = ['vendor' => $vendor[0]];

        if ($this->request->isAJAX()) {
            return view('vendor/show_content_only', $data);
        }

        return view('vendor/show', $data);
    }
}
