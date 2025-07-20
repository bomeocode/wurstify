<?php

namespace App\Controllers;

class AuthController extends BaseController
{
    /**
     * Zeigt einfach die Standard-Login-View an.
     */
    public function loginView()
    {
        return view('shield/login');
    }

    /**
     * Verarbeitet den Login-Versuch.
     */
    public function handleLogin()
    {
        // NEU: Validierung hinzufügen, um leere Eingaben abzufangen
        $rules = [
            'credential' => 'required',
            'password'   => 'required',
        ];
        $messages = [
            'credential' => ['required' => 'Bitte geben Sie einen Benutzernamen oder eine E-Mail-Adresse ein.'],
            'password'   => ['required' => 'Bitte geben Sie Ihr Passwort ein.'],
        ];

        if (!$this->validate($rules, $messages)) {
            return redirect()->back()->withInput()->with('errors', $this->validator->getErrors());
        }
        // ENDE NEU

        // Der Rest der Logik bleibt unverändert
        $credential = $this->request->getPost('credential');
        $password = $this->request->getPost('password');
        $remember = true; //(bool) $this->request->getPost('remember');

        $isEmail = filter_var($credential, FILTER_VALIDATE_EMAIL);

        if ($isEmail) {
            $credentials = ['email' => $credential, 'password' => $password];
        } else {
            $credentials = ['username' => $credential, 'password' => $password];
        }

        $result = auth()->attempt($credentials, $remember);

        if (! $result->isOK()) {
            return redirect()->route('login')->withInput()->with('error', $result->reason());
        }

        $user = auth()->user();

        // Wenn der Nutzer ein Admin oder Superadmin ist, leiten wir ihn zum Admin-Dashboard.
        if ($user->inGroup('admin', 'superadmin')) {
            return redirect()->to('/admin');
        }

        return redirect()->to(config('Auth')->loginRedirect());
    }
}
