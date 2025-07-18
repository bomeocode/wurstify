<?php

namespace App\Models;

use CodeIgniter\Model;

class RatingModel extends Model
{
    protected $table            = 'ratings';
    protected $primaryKey       = 'id';
    protected $useAutoIncrement = true;
    // protected $returnType       = 'object';
    protected $useTimestamps    = true;
    protected $createdField     = 'created_at';
    protected $updatedField     = 'updated_at';

    /**
     * Die Felder, die jetzt in der 'ratings'-Tabelle erlaubt sind.
     * vendor_name, latitude etc. sind weg, vendor_id ist neu.
     */
    protected $allowedFields    = [
        'user_id',
        'vendor_id',
        'rating_appearance',
        'rating_taste',
        'rating_presentation',
        'rating_price',
        'rating_service',
        'comment',
        'image1',
        'image2',
        'image3',
        'helpful_count',
        'type',
        'qr_nickname'
    ];

    /**
     * Die Validierungsregeln, die zu den neuen Spalten passen.
     */
    protected $validationRules = [
        'user_id'           => 'permit_empty|integer',
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

    public function getFeedPage(int $limit = 10, int $offset = 0)
    {
        // Wir bauen die Abfrage als separates Objekt, um Konflikte zu vermeiden.
        $builder = $this->db->table($this->table);

        $builder->select('
        ratings.id, ratings.user_id, ratings.comment, ratings.created_at,
        ratings.helpful_count,
        ratings.rating_appearance, ratings.rating_taste, ratings.rating_presentation,
        ratings.rating_price, ratings.rating_service,
        ratings.image1, ratings.image2, ratings.image3,
        ratings.type, ratings.qr_nickname,
        vendors.uuid as vendor_uuid,
        vendors.name as vendor_name,
        vendors.address as vendor_address,
        vendors.category as vendor_category,
        users.username,
        users.avatar
    ');
        $builder->join('vendors', 'vendors.id = ratings.vendor_id', 'left');
        $builder->join('users', 'users.id = ratings.user_id', 'left');
        $builder->orderBy('ratings.created_at', 'DESC');
        $builder->limit($limit, $offset);

        return $builder->get()->getResult();
    }

    /**
     * Holt paginierte Feed-Einträge mit expliziten Spalten.
     */
    // In app/Models/RatingModel.php
    public function getPaginatedFeed()
    {
        // Wir wählen alle Spalten explizit aus, um Mehrdeutigkeit zu vermeiden
        return $this->select([
            'ratings.id',
            'ratings.comment',
            'ratings.created_at',
            'ratings.rating_appearance',
            'ratings.rating_taste',
            'ratings.rating_presentation',
            'ratings.rating_price',
            'ratings.rating_service',
            'ratings.image1',
            'ratings.image2',
            'ratings.image3',
            'ratings.type',
            'ratings.qr_nickname',
            'vendors.name as vendor_name',
            'vendors.address as vendor_address',
            'vendors.category as vendor_category',
            'users.username',
            'users.avatar'
        ])
            ->join('vendors', 'vendors.id = ratings.vendor_id', 'left')
            ->join('users', 'users.id = ratings.user_id', 'left')
            ->orderBy('ratings.created_at', 'DESC')
            ->paginate(10);
    }

    /**
     * Zählt alle Einträge für die Paginierung.
     */
    public function countFeedItems(): int
    {
        return $this->countAllResults();
    }

    /**
     * Holt eine paginierte Liste aller Bewertungen für den Admin-Bereich.
     */
    public function getAdminListBuilder(?string $searchTerm = null)
    {
        $builder = $this
            // HIER IST DIE KORREKTUR: Alle benötigten Spalten hinzugefügt
            ->select('
            ratings.*, 
            users.username, 
            vendors.name as vendor_name
        ')
            ->join('users', 'users.id = ratings.user_id', 'left')
            ->join('vendors', 'vendors.id = ratings.vendor_id', 'left');

        if ($searchTerm) {
            $builder->groupStart()
                ->like('users.username', $searchTerm)
                ->orLike('vendors.name', $searchTerm)
                ->orLike('ratings.comment', $searchTerm)
                ->groupEnd();
        }

        return $builder;
    }

    /**
     * Holt eine paginierte Liste von Bewertungen für einen spezifischen Anbieter.
     * @param int $vendorId
     * @return array
     */
    public function getPaginatedRatingsForVendor(int $vendorId)
    {
        return $this->select('ratings.*, users.username, users.avatar')
            ->join('users', 'users.id = ratings.user_id', 'left')
            ->where('ratings.vendor_id', $vendorId)
            ->orderBy('ratings.created_at', 'DESC')
            ->paginate(10); // 10 Bewertungen pro Seite
    }

    /**
     * Zählt alle Bewertungen für einen bestimmten Anbieter.
     */
    public function countForVendor(int $vendorId): int
    {
        return $this->where('vendor_id', $vendorId)->countAllResults();
    }

    /**
     * Holt eine paginierte Liste von Bewertungen für einen bestimmten Anbieter.
     */
    public function getPageForVendor(int $vendorId, int $limit = 10, int $offset = 0)
    {
        return $this->select('
            ratings.*, 
            users.username, 
            users.avatar,
            vendors.name as vendor_name,
            vendors.uuid as vendor_uuid,
            vendors.address as vendor_address,
            vendors.category as vendor_category
        ')
            ->join('users', 'users.id = ratings.user_id', 'left')
            ->join('vendors', 'vendors.id = ratings.vendor_id', 'left') // Wichtig: Join für vendors
            ->where('ratings.vendor_id', $vendorId)
            ->orderBy('ratings.created_at', 'DESC')
            ->limit($limit, $offset)
            ->get()
            ->getResult();
    }

    // In app/Models/RatingModel.php

    /**
     * Holt eine paginierte Liste von Bewertungen für einen bestimmten Anbieter.
     */
    public function getRatingsForVendor(int $vendorId, int $limit = 10, int $offset = 0)
    {
        return $this->select('ratings.*, users.username, users.avatar')
            ->join('users', 'users.id = ratings.user_id', 'left')
            ->where('ratings.vendor_id', $vendorId)
            ->orderBy('ratings.created_at', 'DESC')
            ->limit($limit, $offset)
            ->get()
            ->getResult(); // Gibt Objekte zurück
    }
}
