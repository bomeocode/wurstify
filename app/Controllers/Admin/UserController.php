<?php

namespace App\Controllers\Admin;

use App\Controllers\BaseController;
use App\Models\UserModel;
use Config\AuthGroups; // Wir verwenden die Konfigurationsdatei!

class UserController extends BaseController
{
    /**
     * Zeigt eine Liste aller Benutzer an.
     */
    // In app/Controllers/Admin/UserController.php

    public function index()
    {
        $userModel = new UserModel();

        // NEU: Suchbegriff 'q' aus der URL auslesen (GET-Parameter)
        $searchTerm = $this->request->getGet('q');

        $data = [
            // Wir übergeben den Suchbegriff an die Model-Methode
            'users' => $userModel->getUsersWithGroups($searchTerm),
            // WICHTIG: Wir übergeben das Pager-Objekt an die View
            'pager' => $userModel->pager,
            // Wir übergeben den Suchbegriff auch an die View, um ihn im Suchfeld anzuzeigen
            'searchTerm' => $searchTerm,
        ];

        return view('admin/users/index', $data);
    }

    /**
     * Zeigt das Formular zum Bearbeiten eines Benutzers an.
     */
    public function edit($id = null)
    {
        $userModel = new UserModel();
        $authGroupsConfig = new \Config\AuthGroups();

        $user = $userModel->select('users.id, users.username, auth_identities.secret as email')
            ->join('auth_identities', 'auth_identities.user_id = users.id AND auth_identities.type = "email_password"', 'left')
            ->find($id);

        if ($user === null) {
            return redirect()->to('/admin/users')->with('error', 'Benutzer nicht gefunden.');
        }

        // Hole die aktuelle Gruppe des Benutzers separat
        $db = \Config\Database::connect();
        $userGroup = $db->table('auth_groups_users')->where('user_id', $id)->get()->getRow();
        $user->group = $userGroup->group ?? null;

        $data = [
            'user'   => $user,
            'groups' => $authGroupsConfig->groups,
        ];

        return view('admin/users/edit', $data);
    }

    /**
     * Verarbeitet das Update eines Benutzers.
     */
    public function update($id = null)
    {
        $newGroup = $this->request->getPost('group');

        // Validierung: Prüfen, ob die Gruppe existiert
        $authGroupsConfig = new AuthGroups();
        if (!array_key_exists($newGroup, $authGroupsConfig->groups)) {
            return redirect()->back()->with('error', 'Ungültige Gruppe ausgewählt.');
        }

        // Benutzergruppe manuell in der Datenbank aktualisieren
        $db = \Config\Database::connect();
        $db->table('auth_groups_users')
            ->where('user_id', $id)
            ->delete(); // Alte Gruppenzuweisung löschen

        $db->table('auth_groups_users')
            ->insert(['user_id' => $id, 'group' => $newGroup]); // Neue Zuweisung eintragen

        return redirect()->to('/admin/users')->with('message', 'Benutzergruppe erfolgreich aktualisiert.');
    }

    /**
     * Löscht einen Benutzer.
     */
    public function delete($id = null)
    {
        $userModel = new UserModel();
        $user = $userModel->find($id);

        if ($user === null) {
            return redirect()->to('/admin/users')->with('error', 'Benutzer nicht gefunden.');
        }

        // Löscht den User und alle seine verbundenen Daten (identities, groups, etc.)
        $userModel->delete($id, true);

        return redirect()->to('/admin/users')->with('message', 'Benutzer erfolgreich gelöscht.');
    }
}
