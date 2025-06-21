<?php

namespace App\Models;

use CodeIgniter\Model;

class LayoutModel extends Model
{
    protected $table            = 'layouts';
    protected $primaryKey       = 'id';
    protected $returnType       = \App\Entities\Layout::class;
    protected $useTimestamps    = true; // Wichtig, damit created_at/updated_at automatisch gesetzt werden

    // DIE LÖSUNG: Hier alle Felder eintragen, die über das Model gespeichert werden dürfen.
    protected $allowedFields    = [
        'uuid',
        'user_id',
        'name',
        'layout_template'
    ];
}
