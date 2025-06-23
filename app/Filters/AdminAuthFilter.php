<?php

namespace App\Filters;

use CodeIgniter\Filters\FilterInterface;
use CodeIgniter\HTTP\RequestInterface;
use CodeIgniter\HTTP\ResponseInterface;

class AdminAuthFilter implements FilterInterface
{
  public function before(RequestInterface $request, $arguments = null)
  {
    // Prüft, ob ein Benutzer eingeloggt ist und ob er zur Gruppe 'superadmin' gehört.
    // Unsere eigene Helfer-Funktion wird hier verwendet.
    if (!auth()->loggedIn() || !user_is_in_group('admin')) {
      // Wenn nicht, wird er zur Startseite umgeleitet und erhält eine Fehlermeldung.
      return redirect()->to('/')
        ->with('toast', [
          'message' => 'Sie haben keine Berechtigung, diesen Bereich zu betreten.',
          'type'    => 'danger'
        ]);
    }
  }

  public function after(RequestInterface $request, ResponseInterface $response, $arguments = null)
  {
    // Hier müssen wir nichts tun.
  }
}
