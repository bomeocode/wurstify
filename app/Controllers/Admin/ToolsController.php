<?php

namespace App\Controllers\Admin;

use App\Controllers\BaseController;
use App\Models\RatingModel;

class ToolsController extends BaseController
{
    public function cleanupImages()
    {
        helper('filesystem');
        $ratingModel = new RatingModel();

        // 1. Alle Dateinamen aus dem Upload-Verzeichnis holen
        $path = FCPATH . 'uploads/ratings/';
        $filesOnDisk = get_filenames($path);

        // 2. Alle verwendeten Bild-Dateinamen aus der Datenbank holen
        $usedImages = [];
        $ratings = $ratingModel->select('image1, image2, image3')->findAll();
        foreach ($ratings as $rating) {
            if (!empty($rating['image1'])) {
                $usedImages[] = $rating['image1'];
            }
            if (!empty($rating['image2'])) {
                $usedImages[] = $rating['image2'];
            }
            if (!empty($rating['image3'])) {
                $usedImages[] = $rating['image3'];
            }
        }
        $usedImages = array_unique($usedImages);

        // 3. Vergleichen: Welche Dateien auf der Festplatte werden nicht in der DB verwendet?
        $orphanedFiles = array_diff($filesOnDisk, $usedImages);
        $deletedCount = 0;

        // 4. Verwaiste Dateien löschen
        foreach ($orphanedFiles as $file) {
            if (file_exists($path . $file)) {
                unlink($path . $file);
                $deletedCount++;
            }
        }

        return redirect()->to('admin')->with('toast', [
            'message' => "Aufräumen beendet. {$deletedCount} verwaiste Bilder wurden gelöscht.",
            'type' => 'success'
        ]);
    }
}
