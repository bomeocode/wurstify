<?php

namespace App\Controllers;

class Help extends BaseController
{
    /**
     * Zeigt die Anleitung.
     * Für das Modal wird nur der reine Inhalt geladen.
     */
    public function index()
    {
        return view('help/guide_content');
    }
}
