<?php

namespace App\Models;

use CodeIgniter\Model;

class ScreenModel extends Model
{
    protected $table            = 'screens';
    protected $primaryKey       = 'id';
    protected $returnType       = \App\Entities\Screen::class;
    protected $useTimestamps    = true;

    protected $allowedFields    = [
        'uuid',
        'user_id',
        'screen_group_id',
        'layout_uuid',
        'name',
        'location'
    ];
}
