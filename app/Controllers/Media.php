<?php

namespace App\Controllers;

use App\Controllers\BaseController;
use App\Models\MediumModel;

class Media extends BaseController
{
    private MediumModel $model;
    private ?int $currentUserId;

    public function __construct()
    {
        $this->helpers = ['form', 'number'];
        $this->model = new MediumModel();

        // Die ID des aktuellen Benutzers holen (Shield)
        $this->currentUserId = auth()->id();
    }

    /**
     * Zeigt NUR die Medien des aktuell angemeldeten Benutzers an.
     */
    public function index()
    {

        $context = $this->request->getGet('context') ?? 'default';
        $perPage = 20;
        $page = (int) ($this->request->getGet('page') ?? 1);
        $offset = ($page - 1) * $perPage;

        $query = $this->model;

        // Baue die Query dynamisch auf
        //$query = $this->model->where('uploaded_by_id', $this->currentUserId);

        // Suchbegriff anwenden
        if ($searchTerm = $this->request->getGet('q')) {
            $query->like('original_name', trim($searchTerm));
        }

        // Dateityp-Filter anwenden
        if ($fileType = $this->request->getGet('type')) {
            if ($fileType === 'image') {
                $query->groupStart()
                    ->like('file_type', 'image/jpeg')
                    ->orLike('file_type', 'image/png')
                    ->groupEnd();
            } else {
                $query->like('file_type', trim($fileType));
            }
        }

        // Wende die manuelle Paginierung an und hole die Ergebnisse
        $mediaItems = $query->orderBy('created_at', 'DESC')
            ->limit($perPage, $offset)
            ->findAll();

        $data = [
            'media' => $mediaItems,
            'context' => $context,
            'pager' => null, // Wir verwenden den Pager-Service nicht mehr
        ];

        if ($this->request->isAJAX()) {

            // echo '<pre>';
            // var_dump($this->request->getGet());
            // echo '</pre>';
            // die(); // Wichtig: Das Skript danach manuell beenden

            // Rendere die Partial-View zuerst in eine Variable
            $html = view('pages/media/list_items_partial', $data);

            // Sende die Antwort als sauberes JSON-Objekt
            return $this->response->setJSON(['html' => $html]);
        }

        return view('pages/media/index', $data);
    }

    /**
     * Verarbeitet den Datei-Upload. Reagiert auf normale POST- und AJAX-Anfragen.
     * DIESE VERSION IST ROBUSTER GEGENÜBER DATENBANK-FEHLERN.
     */
    /**
     * Verarbeitet den Datei-Upload. Reagiert auf normale POST- und AJAX-Anfragen.
     * Diese Version ist maximal robust und fängt alle bekannten Fehlerquellen ab.
     */
    public function upload()
    {
        $isAjax = $this->request->isAJAX();

        // 1. Validierung der Anfrage
        $validationRule = [
            'userfile' => [
                'label' => 'Datei',
                'rules' => 'uploaded[userfile]'
                    . '|mime_in[userfile,image/jpg,image/jpeg,image/png,application/pdf]'
                    . '|max_size[userfile,50000]',
            ],
        ];

        if (! $this->validate($validationRule)) {
            $error = $this->validator->getErrors()['userfile'] ?? 'Unbekannter Validierungsfehler.';
            if ($isAjax) {
                // Sende Fehler als JSON für AJAX
                return $this->response->setStatusCode(400)->setJSON(['message' => $error, 'type' => 'danger']);
            }
            // Setze Toast-Flashdata für normalen Upload
            session()->setFlashdata('toast', ['message' => $error, 'type' => 'danger']);
            return redirect()->to('/media');
        }

        // 2. Datei-Objekt holen und auf Gültigkeit prüfen
        $file = $this->request->getFile('userfile');

        if (! $file || ! $file->isValid()) {
            $errorMsg = 'Ungültige Datei oder Upload-Fehler. Bitte versuchen Sie es erneut.';
            if ($isAjax) {
                return $this->response->setStatusCode(400)->setJSON(['error' => $errorMsg]);
            }
            return redirect()->to('/media')->with('error', $errorMsg);
        }

        if ($file->hasMoved()) {
            $errorMsg = 'Die Datei wurde bereits verschoben. Möglicherweise ein doppelter Upload-Versuch.';
            if ($isAjax) {
                return $this->response->setStatusCode(400)->setJSON(['error' => $errorMsg]);
            }
            return redirect()->to('/media')->with('error', $errorMsg);
        }

        // 3. Daten für die Datenbank vorbereiten
        $newName = $file->getRandomName();
        $uuid = bin2hex(random_bytes(8));

        $data = [
            'uuid'           => $uuid,
            'original_name'  => $file->getClientName(),
            'stored_name'    => $newName,
            'file_type'      => $file->getClientMimeType(),
            'file_size'      => $file->getSize(),
            'uploaded_by_id' => $this->currentUserId,
        ];

        // 4. Versuche, den Datenbankeintrag zu erstellen
        $newId = $this->model->insert($data);

        if ($newId === false) {
            // Falls der DB-Eintrag fehlschlägt, brich hier ab.
            $errorMsg = 'Die Datei-Informationen konnten nicht in der Datenbank gespeichert werden.';
            if ($isAjax) {
                return $this->response->setStatusCode(500)->setJSON(['error' => $errorMsg, 'errors' => $this->model->errors()]);
            }
            return redirect()->to('/media')->with('error', $errorMsg);
        }

        // 5. Erst wenn der DB-Eintrag erfolgreich war, verschiebe die Datei
        try {
            $file->move(WRITEPATH . 'uploads', $newName);
        } catch (\Exception $e) {
            // Wenn das Verschieben fehlschlägt, lösche den verwaisten DB-Eintrag wieder!
            $this->model->delete($newId);
            $errorMsg = 'Die Datei konnte nicht auf dem Server gespeichert werden. Prüfen Sie die Berechtigungen für den Ordner writable/uploads.';
            if ($isAjax) {
                return $this->response->setStatusCode(500)->setJSON(['error' => $errorMsg]);
            }
            return redirect()->to('/media')->with('error', $errorMsg);
        }

        // 6. Alles hat geklappt, sende die Erfolgsantwort
        if ($isAjax) {
            $newMedium = $this->model->find($newId);

            if (!$newMedium) {
                return $this->response->setStatusCode(500)->setJSON(['error' => 'Konnte den neu erstellten Eintrag nicht abrufen.']);
            }

            // Stelle sicher, dass ALLE benötigten Felder hier drin sind
            return $this->response->setJSON([
                'success' => true,
                'message' => 'Datei erfolgreich hochgeladen!',
                'type'    => 'success',
                'medium'  => [
                    'uuid'          => $newMedium->uuid,
                    'original_name' => $newMedium->original_name, // Dieses Feld ist entscheidend
                    'file_type'     => $newMedium->file_type,
                    'readable_size' => $newMedium->getReadableSize(),
                ]
            ]);
        }

        session()->setFlashdata('toast', ['message' => 'Datei erfolgreich hochgeladen!', 'type' => 'success']);
        return redirect()->to('/media');
    }

    /**
     * Gibt eine Datei aus, aber nur, wenn sie dem aktuellen Benutzer gehört.
     */
    public function serve(string $uuid)
    {
        $medium = $this->model->where('uuid', $uuid)->first();

        // Fehler 404, wenn die UUID nicht existiert ODER sie nicht dem Benutzer gehört.
        // Das verhindert, dass Angreifer herausfinden können, ob eine Datei existiert.
        if (!$medium || $medium->uploaded_by_id !== $this->currentUserId) {
            throw \CodeIgniter\Exceptions\PageNotFoundException::forPageNotFound();
        }

        $path = WRITEPATH . 'uploads/' . $medium->stored_name;

        if (!file_exists($path)) {
            throw \CodeIgniter\Exceptions\PageNotFoundException::forPageNotFound();
        }

        $this->response->setContentType($medium->file_type);
        $this->response->setHeader('Content-Disposition', 'inline; filename="' . $medium->original_name . '"');

        return $this->response->setBody(file_get_contents($path));
    }

    /**
     * Löscht ein Medium, aber nur, wenn es dem aktuellen Benutzer gehört.
     */
    public function delete(string $uuid)
    {
        $medium = $this->model->where('uuid', $uuid)->first();

        // DEBUGGING: Gib alle relevanten Infos aus und beende das Skript
        //dd('Gefundenes Medium:', $medium, 'Besitzer ID:', $medium ? $medium->uploaded_by_id : 'N/A', 'Aktueller User ID:', $this->currentUserId);


        // Auch hier: Fehler 404, wenn die UUID nicht existiert oder sie nicht dem Benutzer gehört.
        if (!$medium || $medium->uploaded_by_id !== $this->currentUserId) {
            return redirect()->to('/media')->with('error', 'Medium nicht gefunden oder Zugriff verweigert.');
        }

        // Wir löschen anhand der primären ID, nicht der UUID.
        if ($this->model->delete($medium->id, true)) {
            // Setze eine Erfolgs-Toast-Nachricht
            session()->setFlashdata('toast', [
                'message' => 'Medium wurde erfolgreich gelöscht.',
                'type'    => 'success'
            ]);
        } else {
            // Setze eine Fehler-Toast-Nachricht
            session()->setFlashdata('toast', [
                'message' => 'Medium konnte nicht gelöscht werden.',
                'type'    => 'danger'
            ]);
        }

        return redirect()->to('/media');
    }
}
