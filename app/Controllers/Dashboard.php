<?php

namespace App\Controllers;

use App\Models\VendorModel; // Model einbinden

// use App\Controllers\BaseController;
// use CodeIgniter\HTTP\ResponseInterface;

class Dashboard extends BaseController
{
    public function index()
    {
        $vendorModel = new VendorModel();

        $data = [
            'user'    => auth()->user(),
            'vendors' => $vendorModel->getVendorsWithAverageRatings()
        ];

        return view('dashboard/index', $data);
    }
}
