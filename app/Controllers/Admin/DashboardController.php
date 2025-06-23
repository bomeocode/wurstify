<?php

namespace App\Controllers\Admin;

use App\Controllers\BaseController;
use App\Models\RatingModel;
use App\Models\UserModel;
use App\Models\VendorModel;

class DashboardController extends BaseController
{
  public function index()
  {
    // Modelle laden, die wir für die Statistiken brauchen
    $userModel = new UserModel();
    $vendorModel = new VendorModel();
    $ratingModel = new RatingModel();

    // Daten für die View vorbereiten
    $data = [
      'userCount'   => $userModel->countAllResults(),
      'vendorCount' => $vendorModel->countAllResults(),
      'ratingCount' => $ratingModel->countAllResults(),
    ];

    // Die aufbereitete View laden und die Daten übergeben
    return view('admin/dashboard/index', $data);
  }
}
