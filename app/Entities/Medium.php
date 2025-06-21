<?php

namespace App\Entities;

use CodeIgniter\Entity\Entity;

class Medium extends Entity
{
    protected $dates   = ['created_at'];
    protected $casts   = [
        'uploaded_by_id' => 'integer',
        'file_size'      => 'integer', // Hier können wir auch gleich die Dateigröße mit aufnehmen
    ];

    /**
     * Gibt die Dateigröße in einem lesbaren Format zurück (KB, MB).
     */
    public function getReadableSize(): string
    {
        $bytes = $this->attributes['file_size'] ?? 0;

        if ($bytes >= 1048576) {
            return number_format($bytes / 1048576, 2) . ' MB';
        }

        if ($bytes >= 1024) {
            return number_format($bytes / 1024, 2) . ' KB';
        }

        return $bytes . ' Bytes';
    }
}
