<?php

namespace App\Models;

use CodeIgniter\Model;

class RatingVoteModel extends Model
{
    protected $table            = 'rating_votes';
    protected $allowedFields    = ['rating_id', 'user_id'];
    protected $useTimestamps    = true;
    protected $createdField     = 'created_at';
    protected $updatedField     = ''; // Wir brauchen kein updated_at
}
