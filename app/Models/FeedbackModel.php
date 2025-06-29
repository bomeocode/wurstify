<?php

namespace App\Models;

use CodeIgniter\Model;

class FeedbackModel extends Model
{
    protected $table = 'feedback';
    protected $allowedFields = ['user_id', 'feedback_text', 'user_agent'];
    protected $useTimestamps = true;
}
