<?php

namespace App\Models;

use CodeIgniter\Model;

class LayoutSlotModel extends Model
{
    protected $table            = 'layout_slots';
    protected $primaryKey       = 'id';
    protected $returnType       = \App\Entities\LayoutSlot::class;
    protected $useTimestamps    = false; // Wir haben hier nur ein 'updated_at' Feld
    protected $updatedField     = 'updated_at';


    protected $allowedFields    = [
        'layout_id',
        'slot_name',
        'media_uuid',
        'widget_type',
        'widget_data'
    ];
}
