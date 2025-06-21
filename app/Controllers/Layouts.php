<?php

namespace App\Controllers;

use App\Controllers\BaseController;
use App\Models\LayoutModel;
use App\Models\LayoutSlotModel;

class Layouts extends BaseController
{
    private LayoutModel $model;
    private LayoutSlotModel $slotModel;
    private ?int $currentUserId;

    public function __construct()
    {
        $this->helpers = ['form', 'text'];
        $this->model = new LayoutModel();
        $this->slotModel = new LayoutSlotModel();
        $this->currentUserId = auth()->id();
    }

    /**
     * Zeigt eine Liste aller Layouts des aktuellen Benutzers.
     */
    public function index()
    {
        $data = [
            'layouts' => $this->model
                ->where('user_id', $this->currentUserId)
                ->orderBy('name', 'ASC')
                ->findAll(),
        ];

        return view('pages/layouts/index', $data);
    }

    /**
     * Zeigt das Formular zum Erstellen eines neuen Layouts.
     */
    public function new()
    {
        /** @var \Config\Layouts $layoutConfig */
        $layoutConfig = config('Layouts');

        $data = [
            'templates' => $layoutConfig->templates,
        ];

        return view('pages/layouts/new', $data);
    }

    /**
     * Verarbeitet die Erstellung eines neuen Layouts und leitet zum Editor weiter.
     */
    public function create()
    {
        /** @var \Config\Layouts $layoutConfig */
        $layoutConfig = config('Layouts');

        // 1. Validierung
        $rules = [
            'name' => 'required|string|max_length[255]',
            'layout_template' => 'required|in_list[' . implode(',', array_keys($layoutConfig->templates)) . ']',
        ];

        if (! $this->validate($rules)) {
            return redirect()->back()->withInput()->with('errors', $this->validator->getErrors());
        }

        // 2. Daten vorbereiten
        $uuid = bin2hex(random_bytes(16)); // Erzeuge eine sichere, 32-stellige UUID

        $data = [
            'uuid'            => $uuid,
            'user_id'         => $this->currentUserId,
            'name'            => $this->request->getPost('name'),
            'layout_template' => $this->request->getPost('layout_template'),
        ];

        // 3. In die Datenbank einfügen
        if ($this->model->insert($data)) {
            // Erfolgreich -> Weiterleiten zum Editor mit einer Erfolgsnachricht
            session()->setFlashdata('toast', [
                'message' => 'Layout erfolgreich erstellt. Sie können nun Inhalte zuweisen.',
                'type'    => 'success'
            ]);
            // Wir leiten zum (zukünftigen) Editor weiter
            return redirect()->to('/layouts/edit/' . $uuid);
        }

        // Fehlerfall
        return redirect()->back()->withInput()->with('error', 'Das Layout konnte nicht gespeichert werden.');
    }

    public function edit(string $uuid)
    {
        // 1. Finde das Layout und prüfe den Besitzer (bleibt gleich)
        $layout = $this->model->where('uuid', $uuid)->first();
        if (!$layout || $layout->user_id !== $this->currentUserId) {
            throw \CodeIgniter\Exceptions\PageNotFoundException::forPageNotFound();
        }

        // 2. Hole alle Slot-Daten für dieses Layout (bleibt gleich)
        $slots = $this->slotModel->where('layout_id', $layout->id)->findAll();

        // 3. NEU: Bereite die Daten für die View vor (inkl. Medien-Infos)
        $slotsData = [];
        $mediaUuids = [];
        foreach ($slots as $slot) {
            // Sammle alle Medien-UUIDs, die wir brauchen
            if (!empty($slot->media_uuid)) {
                $mediaUuids[] = $slot->media_uuid;
            }
            $slotsData[$slot->slot_name] = $slot;
        }

        $mediaLookup = [];
        if (!empty($mediaUuids)) {
            // Hole alle benötigten Medien-Infos mit einer einzigen Abfrage
            $mediaModel = new \App\Models\MediumModel();
            $mediaItems = $mediaModel->whereIn('uuid', $mediaUuids)->findAll();

            // Erstelle ein Nachschlage-Array für einfachen Zugriff in der View
            foreach ($mediaItems as $item) {
                $mediaLookup[$item->uuid] = $item;
            }
        }

        $data = [
            'layout'      => $layout,
            'slots'       => $slotsData,
            'mediaLookup' => $mediaLookup, // Übergib das neue Array an die View
        ];

        return view('pages/layouts/edit', $data);
    }


    public function updateSlot(string $layoutUuid, string $slotName)
    {
        if (! $this->request->isAJAX()) {
            return $this->response->setStatusCode(403, 'Forbidden');
        }

        // 1. Finde das Layout und prüfe den Besitzer (Sicherheit!)
        $layout = $this->model->where('uuid', $layoutUuid)->first();
        if (!$layout || $layout->user_id !== $this->currentUserId) {
            return $this->response->setStatusCode(404, 'Layout nicht gefunden.');
        }

        // 2. Hole die Daten aus der POST-Anfrage
        $mediaUuid = $this->request->getPost('media_uuid');
        if (empty($mediaUuid)) {
            return $this->response->setStatusCode(400, 'Keine Medien-UUID übergeben.');
        }

        // 3. "Upsert"-Logik: Finde einen existierenden Slot oder erstelle einen neuen
        $slot = $this->slotModel
            ->where('layout_id', $layout->id)
            ->where('slot_name', $slotName)
            ->first();

        $slotData = [
            'layout_id'  => $layout->id,
            'slot_name'  => $slotName,
            'media_uuid' => $mediaUuid,
            'widget_type' => null,
            'widget_data' => null,
        ];

        $success = $slot ? $this->slotModel->update($slot->id, $slotData) : $this->slotModel->insert($slotData);

        if ($success) {
            $mediaModel = new \App\Models\MediumModel();
            $assignedMedium = $mediaModel->where('uuid', $mediaUuid)->first();

            return $this->response->setJSON([
                'success' => true,
                'message' => "Slot '{$slotName}' erfolgreich aktualisiert.",
                'slot' => [
                    'name'          => $slotName,
                    'preview_url'   => site_url('media/serve/' . $assignedMedium->uuid),
                    'original_name' => $assignedMedium->original_name,
                    'file_type'     => $assignedMedium->file_type // <-- DIESE ZEILE HINZUFÜGEN
                ]
            ]);
        }

        return $this->response->setStatusCode(500, 'Fehler beim Speichern des Slots.');
    }

    /**
     * Löscht ein Layout und alle zugehörigen Slots (via DB Cascade).
     * @param string $uuid Die UUID des zu löschenden Layouts.
     */
    public function delete(string $uuid)
    {
        // 1. Finde das Layout und prüfe den Besitzer (bleibt unverändert wichtig)
        $layout = $this->model->where('uuid', $uuid)->first();

        if (!$layout || (int)$layout->user_id !== $this->currentUserId) {
            throw \CodeIgniter\Exceptions\PageNotFoundException::forPageNotFound();
        }

        // 2. Manuelles Löschen statt auf CASCADE zu vertrauen
        // Zuerst alle zugehörigen "Kind"-Einträge (Slots) löschen
        //$this->slotModel->where('layout_id', $layout->id)->delete();

        // 3. Erst danach den "Eltern"-Eintrag (Layout) löschen
        if ($this->model->delete($layout->id)) {

            // Die setFlashdata-Funktion können wir jetzt wieder sicher verwenden
            session()->setFlashdata('toast', [
                'message' => "Das Layout '{$layout->name}' wurde erfolgreich gelöscht.",
                'type'    => 'success'
            ]);
        } else {
            session()->setFlashdata('toast', [
                'message' => 'Das Layout konnte nicht gelöscht werden.',
                'type'    => 'danger'
            ]);
        }

        // 4. Jetzt wird der Redirect erreicht werden
        return redirect()->to('/layouts');
    }
}
