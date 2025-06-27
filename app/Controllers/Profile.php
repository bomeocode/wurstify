<?php

namespace App\Controllers;

use App\Models\UserModel;

class Profile extends BaseController
{
    // Zeigt das Profil-Formular an
    public function show()
    {
        return view('profile/show', [
            'user' => auth()->user()
        ]);
    }

    // Speichert den neuen Benutzernamen und den Avatar-Dateinamen
    public function update()
    {
        $user = auth()->user();

        // ALT (und fehleranfällig in Ihrer Umgebung):
        // $users = auth()->getProvider();

        // NEU (explizit und sicher):
        $users = new UserModel();

        $rules = [
            'username' => "required|alpha_numeric_space|min_length[3]|max_length[30]|is_unique[users.username,id,{$user->id}]",
        ];

        if (! $this->validate($rules)) {
            $error = array_values($this->validator->getErrors())[0];
            return redirect()->back()->withInput()->with('toast', ['message' => $error, 'type' => 'danger']);
        }

        $updateData = [
            'username' => $this->request->getPost('username'),
        ];

        $newAvatar = $this->request->getPost('avatar');
        if ($newAvatar) {
            $updateData['avatar'] = $newAvatar;
        }

        if ($users->update($user->id, $updateData)) {
            return redirect()->to('profile')->with('toast', ['message' => 'Profil erfolgreich aktualisiert.', 'type' => 'success']);
        }

        return redirect()->back()->withInput()->with('toast', ['message' => 'Beim Speichern des Profils ist ein Fehler aufgetreten.', 'type' => 'danger']);
    }
}
