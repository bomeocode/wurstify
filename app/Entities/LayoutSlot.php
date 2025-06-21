<?php

namespace App\Entities;

use CodeIgniter\Entity\Entity;

class LayoutSlot extends Entity
{
    protected $datamap = [];
    protected $dates   = ['updated_at'];

    protected $casts   = [
        'id'        => 'integer',
        'layout_id' => 'integer'
    ];
}
