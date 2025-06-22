<?php

namespace App\Models;

use CodeIgniter\Model;

class VendorModel extends Model
{
    protected $table            = 'vendors';
    protected $primaryKey       = 'id';
    protected $useAutoIncrement = true;
    protected $returnType       = 'array';
    protected $useSoftDeletes   = false;
    protected $allowedFields    = ['uuid', 'name', 'address', 'latitude', 'longitude']; // uuid hier hinzufügen
    protected $useTimestamps    = true;
    protected $beforeInsert     = ['generateUUID'];

    /**
     * Findet einen Anbieter innerhalb eines sehr kleinen Radius (z.B. 50 Meter)
     * basierend auf den Koordinaten. Verwendet die Haversine-Formel für die Abstandsberechnung.
     *
     * @param float $lat
     * @param float $lon
     * @param float $radiusInKm
     * @return array|null
     */
    public function findNearby(float $lat, float $lon, float $radiusInKm = 0.05)
    {
        $builder = $this->db->table($this->table);

        // Haversine-Formel in SQL
        $builder->select("id, name, ( 6371 * acos( cos( radians({$lat}) ) * cos( radians( latitude ) ) * cos( radians( longitude ) - radians({$lon}) ) + sin( radians({$lat}) ) * sin( radians( latitude ) ) ) ) AS distance");
        $builder->having('distance <', $radiusInKm);
        $builder->orderBy('distance', 'ASC');
        $builder->limit(1);

        $query = $builder->get();

        return $query->getRowArray();
    }

    protected function generateUUID(array $data)
    {
        $data['data']['uuid'] = service('uuid')->uuid4()->toString();
        return $data;
    }

    public function getVendorsWithAverageRatings()
    {
        return $this->select('
            vendors.uuid, vendors.name as vendor_name, vendors.latitude, vendors.longitude,
            AVG(ratings.rating_taste) as avg_taste,
            AVG(ratings.rating_appearance) as avg_appearance,
            AVG(ratings.rating_presentation) as avg_presentation,
            AVG(ratings.rating_price) as avg_price,
            AVG(ratings.rating_service) as avg_service,
            COUNT(ratings.id) as total_ratings
        ')
            ->join('ratings', 'ratings.vendor_id = vendors.id', 'left')
            ->where('vendors.latitude IS NOT NULL')
            ->where('vendors.longitude IS NOT NULL')
            ->groupBy('vendors.id')
            ->findAll();
    }
}
