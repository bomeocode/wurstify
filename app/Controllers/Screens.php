<?php

namespace App\Controllers;

use App\Controllers\BaseController;
use App\Models\ScreenModel;
use App\Models\ScreenGroupModel;
use App\Models\LayoutModel; // NEU

class Screens extends BaseController
{
    private ScreenModel $screenModel;
    private ScreenGroupModel $groupModel;
    private LayoutModel $layoutModel; // NEU
    private ?int $currentUserId;

    public function __construct()
    {
        $this->helpers = ['form', 'text'];
        $this->screenModel = new ScreenModel();
        $this->groupModel = new ScreenGroupModel();
        $this->layoutModel = new LayoutModel(); // NEU
        $this->currentUserId = auth()->id();
    }

    /**
     * Zeigt eine Liste aller Bildschirme des Benutzers an,
     * inklusive der Information, zu welcher Gruppe sie gehören.
     */
    public function index()
    {
        // Wir verwenden den Query Builder, um die Tabellen zu verknüpfen (JOIN)
        $screens = $this->screenModel
            ->select('screens.*, screen_groups.name as group_name') // Wähle alle Screen-Spalten und den Gruppennamen
            ->join('screen_groups', 'screen_groups.id = screens.screen_group_id', 'left') // LEFT JOIN, um auch Screens ohne Gruppe zu zeigen
            ->where('screens.user_id', $this->currentUserId)
            ->orderBy('screens.name', 'ASC')
            ->findAll();

        $data = [
            'screens' => $screens,
        ];

        return view('pages/screens/index_view', $data);
    }

    /**
     * Stellt alle Gruppen und Layouts als JSON für das 'Gruppen verwalten'-Modal bereit.
     */
    public function groupsApi()
    {
        // Nur für AJAX-Anfragen
        if (! $this->request->isAJAX()) {
            return $this->response->setStatusCode(403);
        }

        $groups = $this->groupModel->where('user_id', $this->currentUserId)->orderBy('name', 'ASC')->findAll();
        $layouts = $this->layoutModel->where('user_id', $this->currentUserId)->orderBy('name', 'ASC')->findAll();

        return $this->response->setJSON([
            'groups'  => $groups,
            'layouts' => $layouts
        ]);
    }

    /**
     * Erstellt eine neue Bildschirmgruppe via AJAX.
     */
    public function createGroup()
    {
        if (! $this->request->isAJAX()) {
            return $this->response->setStatusCode(403);
        }

        $rules = ['name' => 'required|max_length[255]'];
        if (! $this->validate($rules)) {
            return $this->response->setStatusCode(400)->setJSON(['error' => $this->validator->getErrors()['name']]);
        }

        $data = [
            'user_id' => $this->currentUserId,
            'name'    => $this->request->getPost('name'),
        ];

        $newId = $this->groupModel->insert($data);

        if ($newId === false) {
            return $this->response->setStatusCode(500, 'Gruppe konnte nicht in der Datenbank gespeichert werden.');
        }

        $newGroup = $this->groupModel->find($newId);
        return $this->response->setJSON(['success' => true, 'group' => $newGroup]);
    }

    /**
     * Aktualisiert eine existierende Bildschirmgruppe via AJAX.
     */
    public function updateGroup()
    {
        if (! $this->request->isAJAX()) {
            return $this->response->setStatusCode(403);
        }

        $id = $this->request->getPost('id');
        $group = $this->groupModel->find($id);

        // Sicherheits-Check: Gehört die Gruppe dem User?
        if (!$group || $group->user_id !== $this->currentUserId) {
            return $this->response->setStatusCode(404, 'Gruppe nicht gefunden.');
        }

        $rules = ['name' => 'required|max_length[255]'];
        if (! $this->validate($rules)) {
            return $this->response->setStatusCode(400)->setJSON(['error' => $this->validator->getErrors()['name']]);
        }

        $data = [
            'name'        => $this->request->getPost('name'),
            'layout_uuid' => $this->request->getPost('layout_uuid') ?: null, // Leeren String in NULL umwandeln
        ];

        if ($this->groupModel->update($id, $data)) {
            return $this->response->setJSON(['success' => true, 'message' => 'Gruppe erfolgreich gespeichert.']);
        }

        return $this->response->setStatusCode(500, 'Gruppe konnte nicht gespeichert werden.');
    }

    /**
     * Löscht eine Bildschirmgruppe via AJAX.
     */
    public function deleteGroup()
    {
        if (! $this->request->isAJAX()) {
            return $this->response->setStatusCode(403);
        }

        $id = $this->request->getPost('id');
        $group = $this->groupModel->find($id);

        // Sicherheits-Check
        if (!$group || $group->user_id !== $this->currentUserId) {
            return $this->response->setStatusCode(404, 'Gruppe nicht gefunden.');
        }

        if ($this->groupModel->delete($id)) {
            return $this->response->setJSON(['success' => true, 'message' => 'Gruppe erfolgreich gelöscht.']);
        }

        return $this->response->setStatusCode(500, 'Gruppe konnte nicht gelöscht werden.');
    }
}
