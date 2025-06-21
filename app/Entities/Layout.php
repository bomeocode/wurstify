<?php

namespace App\Entities;

use CodeIgniter\Entity\Entity;

class Layout extends Entity
{
    protected $datamap = [];
    protected $dates   = ['created_at', 'updated_at', 'deleted_at'];

    // NEU: Sage CodeIgniter, welche Datentypen es erwarten soll.
    protected $casts   = [
        'id'      => 'integer',
        'user_id' => 'integer'
    ];
}
