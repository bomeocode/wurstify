<?php

namespace App\Entities;

use CodeIgniter\Entity\Entity;

class Screen extends Entity
{
    protected $dates   = ['created_at', 'updated_at'];
    protected $casts   = [
        'id' => 'integer',
        'user_id' => 'integer',
        'screen_group_id' => 'integer'
    ];
}
