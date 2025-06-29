<?php

namespace App\Controllers;

use App\Models\UserModel;

class Profile extends BaseController
{
    public function show()
    {
        return view('profile/show', [
            'user' => auth()->user()
        ]);
    }

    public function updateDetails()
    {
        $user = auth()->user();

        $rules = [
            'username' => "required|alpha_numeric_space|min_length[3]|max_length[30]|is_unique[users.username,id,{$user->id}]",
        ];

        // NEU: Das "Wörterbuch" für die "Details"-Validierung
        $messages = [
            'username' => [
                'required'            => 'Bitte geben Sie einen Benutzernamen an.',
                'alpha_numeric_space' => 'Der Benutzername darf nur Buchstaben, Zahlen und Leerzeichen enthalten.',
                'min_length'          => 'Der Benutzername muss mindestens 3 Zeichen lang sein.',
                'max_length'          => 'Der Benutzername darf maximal 30 Zeichen lang sein.',
                'is_unique'           => 'Dieser Benutzername ist leider schon vergeben.',
            ]
        ];

        // NEU: Das $messages-Array wird als zweiter Parameter übergeben
        if (! $this->validate($rules, $messages)) {
            $error = array_values($this->validator->getErrors())[0];
            return redirect()->back()->withInput()->with('toast', ['message' => $error, 'type' => 'danger']);
        }

        $users = new UserModel();
        $updateData = [
            'username' => $this->request->getPost('username'),
            'avatar'   => $this->request->getPost('avatar'),
        ];

        if ($users->update($user->id, $updateData)) {
            return redirect()->to('profile')->with('toast', ['message' => 'Profildetails erfolgreich aktualisiert.', 'type' => 'success']);
        }

        return redirect()->back()->withInput()->with('toast', ['message' => 'Fehler beim Speichern der Details.', 'type' => 'danger']);
    }

    // In app/Controllers/ProfileController.php

    // In app/Controllers/ProfileController.php

    public function updatePassword()
    {
        $rules = [
            'old_password'     => 'required',
            'password'         => 'required|strong_password',
            'password_confirm' => 'required|matches[password]',
        ];
        $messages = [
            'old_password' => ['required' => 'Bitte geben Sie Ihr altes Passwort an.'],
            'password' => [
                'required'        => 'Bitte geben Sie ein neues Passwort an.',
                'strong_password' => 'Das Passwort muss mind. 8 Zeichen, Groß-, Kleinbuchstaben und Zahlen enthalten.',
            ],
            'password_confirm' => [
                'required' => 'Bitte bestätigen Sie Ihr neues Passwort.',
                'matches'  => 'Die Passwörter stimmen nicht überein.',
            ],
        ];

        if (! $this->validate($rules, $messages)) {
            $error = array_values($this->validator->getErrors())[0];
            return redirect()->back()->withInput()->with('toast', ['message' => $error, 'type' => 'danger']);
        }

        // --- FINALE, ROBUSTE KORREKTUR ---

        // Schritt 1: Das alte Passwort aus dem Formular holen.
        $oldPassword = $this->request->getPost('old_password');

        // Schritt 2: Den Benutzer-Datensatz explizit und frisch aus der Datenbank laden,
        // um sicherzustellen, dass wir den echten Passwort-Hash haben.
        $userModel = new UserModel();
        $dbUser    = $userModel->find(auth()->id());

        // Schritt 3: Das Passwort mit der nativen PHP-Funktion vergleichen.
        // Dies ist der Kern der Überprüfung und unabhängig von Shield-Methoden.
        if (!password_verify($oldPassword, $dbUser->password_hash)) {
            return redirect()->back()->withInput()->with('toast', ['message' => 'Das eingegebene alte Passwort ist nicht korrekt.', 'type' => 'danger']);
        }

        // Schritt 4: Wenn alles korrekt ist, das neue Passwort setzen und speichern.
        // Wir verwenden hier wieder unser explizit geladenes Model, um sicherzugehen.
        $user = auth()->user();
        $user->setPassword($this->request->getPost('password'));
        $userModel->save($user);

        return redirect()->to('profile')->with('toast', ['message' => 'Passwort erfolgreich geändert.', 'type' => 'success']);
    }
}
