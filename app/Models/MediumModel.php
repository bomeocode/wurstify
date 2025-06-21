<?php

namespace App\Models;

use CodeIgniter\Model;

class MediumModel extends Model
{
    protected $table            = 'media';
    protected $primaryKey       = 'id';
    protected $returnType       = \App\Entities\Medium::class;
    protected $allowedFields    = ['uuid', 'original_name', 'stored_name', 'file_type', 'file_size', 'uploaded_by_id'];
    protected $useTimestamps    = true;
    protected $createdField     = 'created_at';
    protected $updatedField     = '';

    protected $afterDelete      = ['deleteFile'];

    protected function deleteFile(array $data)
    {
        // Diese Funktion bleibt unverändert und funktioniert korrekt
        if (isset($data['id']) && isset($data['purge']) && $data['purge'] === true) {
            // Wir müssen den Datensatz frisch aus der DB holen, falls er nicht vollständig ist
            $medium = $this->withDeleted()->find($data['id'][0]);

            if ($medium && !empty($medium->stored_name)) {
                $filePath = WRITEPATH . 'uploads/' . $medium->stored_name;
                if (file_exists($filePath)) {
                    unlink($filePath);
                }
            }
        }
        return $data;
    }
}
