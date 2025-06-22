<?php

namespace App\Models;

use CodeIgniter\Model;

class RatingModel extends Model
{
    protected $table            = 'ratings';
    protected $primaryKey       = 'id';
    protected $useAutoIncrement = true;
    protected $returnType       = 'array';
    protected $useTimestamps    = true;
    protected $createdField     = 'created_at';
    protected $updatedField     = 'updated_at';

    /**
     * Die Felder, die jetzt in der 'ratings'-Tabelle erlaubt sind.
     * vendor_name, latitude etc. sind weg, vendor_id ist neu.
     */
    protected $allowedFields    = [
        'user_id',
        'vendor_id', // NEU
        'rating_appearance',
        'rating_taste',
        'rating_presentation',
        'rating_price',
        'rating_service',
        'comment'
    ];

    /**
     * Die Validierungsregeln, die zu den neuen Spalten passen.
     */
    protected $validationRules = [
        'user_id'           => 'required',
        'vendor_id'         => 'required|integer|is_not_unique[vendors.id]', // Stellt sicher, dass der Vendor existiert
        'rating_appearance' => 'required|in_list[1,2,3,4,5]',
        'rating_taste'      => 'required|in_list[1,2,3,4,5]',
        'rating_presentation' => 'required|in_list[1,2,3,4,5]',
        'rating_price'      => 'required|in_list[1,2,3,4,5]',
        'rating_service'    => 'required|in_list[1,2,3,4,5]',
    ];

    /**
     * Benutzerdefinierte Fehlermeldungen.
     */
    protected $validationMessages = [
        'vendor_id' => [
            'required' => 'Es muss ein Anbieter zugeordnet sein.',
            'is_not_unique' => 'Der zugeordnete Anbieter existiert nicht in der Datenbank.'
        ]
    ];
}
