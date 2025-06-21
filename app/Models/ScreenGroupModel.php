<?php

namespace App\Models;

use CodeIgniter\Model;

class ScreenGroupModel extends Model
{
    protected $table            = 'screen_groups';
    protected $primaryKey       = 'id';
    protected $returnType       = \App\Entities\ScreenGroup::class;
    protected $useTimestamps    = true;

    protected $allowedFields    = [
        'user_id',
        'name',
        'layout_uuid'
    ];
}
