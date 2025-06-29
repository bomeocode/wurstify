<?php

namespace App\Controllers;

class Feed extends BaseController
{
    public function index()
    {
        return view('feed/index');
    }
}
