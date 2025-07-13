<?php

namespace App\Models;

use CodeIgniter\Model;

class VendorClaimModel extends Model
{
    protected $table = 'vendor_claims';
    protected $allowedFields = [
        'vendor_id',
        'user_id',
        'claimant_name',
        'contact_email',
        'proof_text',
        'status',
        'ip_address',
        'user_agent'
    ];
    protected $useTimestamps = true;
}
