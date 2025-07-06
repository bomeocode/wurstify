<?php

namespace App\Controllers\Admin;

use App\Controllers\BaseController;
use App\Models\UserLevelModel;

class UserLevelController extends BaseController
{
    /**
     * Zeigt eine Liste aller Benutzer-Stufen an.
     */
    public function index()
    {
        $levelModel = new UserLevelModel();
        $data = [
            'levels' => $levelModel->orderBy('level_number', 'ASC')->findAll(),
        ];
        return view('admin/levels/index', $data);
    }

    /**
     * Zeigt das Bearbeitungsformular für eine einzelne Stufe an.
     */
    public function edit($id = null)
    {
        $levelModel = new UserLevelModel();
        $data = [
            'level' => $levelModel->find($id)
        ];

        if (empty($data['level'])) {
            return redirect()->to(route_to('admin_levels_index'))
                ->with('toast', ['message' => 'Diese Stufe wurde nicht gefunden.', 'type' => 'danger']);
        }

        return view('admin/levels/edit', $data);
    }

    /**
     * Verarbeitet die Aktualisierung einer Stufe.
     */
    public function update($id = null)
    {
        $levelModel = new UserLevelModel();
        $level = $levelModel->find($id);

        if (empty($level)) {
            return redirect()->back()->with('toast', ['message' => 'Stufe nicht gefunden.', 'type' => 'danger']);
        }

        $rules = [
            'name'        => 'required|string|max_length[100]',
            'min_ratings' => 'required|integer|greater_than_equal_to[0]',
        ];

        if (!$this->validate($rules)) {
            return redirect()->back()->withInput()->with('errors', $this->validator->getErrors());
        }

        $updateData = [
            'name'        => $this->request->getPost('name'),
            'min_ratings' => $this->request->getPost('min_ratings'),
        ];

        if ($levelModel->update($id, $updateData)) {
            return redirect()->to(route_to('admin_levels_index'))
                ->with('toast', ['message' => 'Benutzer-Stufe erfolgreich aktualisiert.', 'type' => 'success']);
        }

        return redirect()->back()->withInput()
            ->with('toast', ['message' => 'Aktualisierung fehlgeschlagen.', 'type' => 'danger']);
    }
}
