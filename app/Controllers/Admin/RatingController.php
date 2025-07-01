<?php

namespace App\Controllers\Admin;

use App\Controllers\BaseController;
use App\Models\RatingModel;

class RatingController extends BaseController
{
    public function __construct()
    {
        helper('text'); // Lädt den Text Helper
    }

    public function index()
    {
        $model = new RatingModel();
        $searchTerm = $this->request->getGet('q');

        // Wir holen uns den Builder vom Model...
        $builder = $model->getAdminListBuilder($searchTerm);

        $data = [
            // ...und führen die Paginierung erst hier im Controller aus.
            'ratings'    => $builder->orderBy('ratings.id', 'DESC')->paginate(20),
            'pager'      => $model->pager,
            'searchTerm' => $searchTerm,
        ];

        return view('admin/ratings/index', $data);
    }

    public function edit($id = null)
    {
        $model = new RatingModel();

        // Wir holen uns den Builder vom Model...
        $builder = $model->getAdminListBuilder();

        // ...und wenden den finalen Filter an, um nur EINEN Eintrag zu bekommen.
        $rating = $builder->where('ratings.id', $id)->first();

        if (!$rating) {
            return redirect()->to('admin/ratings')->with('toast', ['message' => 'Bewertung nicht gefunden.', 'type' => 'danger']);
        }

        return view('admin/ratings/edit', ['rating' => $rating]);
    }

    public function update($id = null)
    {
        $model = new \App\Models\RatingModel();
        if (!$model->find($id)) {
            return redirect()->to('admin/ratings')->with('toast', ['message' => 'Bewertung nicht gefunden.', 'type' => 'danger']);
        }

        // +++ HIER IST DIE KORREKTUR: Fehlende Validierungsregeln +++
        $rules = [
            // 'rating_appearance' => 'required|in_list[1,2,3,4,5]',
            // 'rating_taste'      => 'required|in_list[1,2,3,4,5]',
            // 'rating_presentation' => 'required|in_list[1,2,3,4,5]',
            // 'rating_price'      => 'required|in_list[1,2,3,4,5]',
            // 'rating_service'    => 'required|in_list[1,2,3,4,5]',
            'comment'           => 'permit_empty|string',
            'image1'            => 'permit_empty|string|max_length[255]',
            'image2'            => 'permit_empty|string|max_length[255]',
            'image3'            => 'permit_empty|string|max_length[255]',
        ];

        if (!$this->validate($rules)) {
            // Wenn die Validierung fehlschlägt, leiten wir zurück und zeigen die Fehler an.
            return redirect()->back()->withInput()->with('errors', $this->validator->getErrors());
        }

        $postData = $this->request->getPost();

        if ($model->update($id, $postData)) {
            return redirect()->to('admin/ratings')->with('toast', ['message' => 'Bewertung erfolgreich aktualisiert.', 'type' => 'success']);
        }

        return redirect()->back()->withInput()->with('toast', ['message' => 'Fehler beim Speichern der Bewertung.', 'type' => 'danger']);
    }

    public function delete($id = null)
    {
        $model = new RatingModel();
        $rating = $model->find($id);

        if ($rating) {
            // Bilder vom Server löschen, bevor der DB-Eintrag gelöscht wird
            foreach (['image1', 'image2', 'image3'] as $imgField) {
                if (!empty($rating[$imgField]) && file_exists(FCPATH . 'uploads/ratings/' . $rating[$imgField])) {
                    unlink(FCPATH . 'uploads/ratings/' . $rating[$imgField]);
                }
            }
            $model->delete($id);
            return redirect()->to('admin/ratings')->with('toast', ['message' => 'Bewertung erfolgreich gelöscht.', 'type' => 'success']);
        }

        return redirect()->to('admin/ratings')->with('toast', ['message' => 'Bewertung nicht gefunden.', 'type' => 'danger']);
    }
}
