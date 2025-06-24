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
    protected $allowedFields    = ['uuid', 'name', 'address', 'latitude', 'longitude'];
    protected $useTimestamps    = true;
    protected $beforeInsert     = ['generateUUID'];

    /**
     * Callback-Funktion, die vor jedem Insert aufgerufen wird,
     * um automatisch eine UUID zu generieren.
     */
    protected function generateUUID(array $data)
    {
        $data['data']['uuid'] = $this->createUuidV4();
        return $data;
    }

    private function createUuidV4(): string
    {
        // Erzeugt 16 Bytes (128 bits) an zufälligen Daten
        $data = random_bytes(16);

        // Setzt das 7. Byte für die Version 4 gemäß RFC 4122
        $data[6] = chr(ord($data[6]) & 0x0f | 0x40);

        // Setzt das 9. Byte gemäß RFC 4122
        $data[8] = chr(ord($data[8]) & 0x3f | 0x80);

        // Formatiert die 16 Bytes in eine 32-stellige hexadezimale Zeichenkette
        return vsprintf('%s%s-%s-%s-%s-%s%s%s', str_split(bin2hex($data), 4));
    }

    /**
     * Findet einen Anbieter innerhalb eines sehr kleinen Radius (z.B. 50 Meter)
     */
    public function findNearby(float $lat, float $lon, float $radiusInKm = 0.05)
    {
        $builder = $this->db->table($this->table);
        $builder->select("id, name, ( 6371 * acos( cos( radians({$lat}) ) * cos( radians( latitude ) ) * cos( radians( longitude ) - radians({$lon}) ) + sin( radians({$lat}) ) * sin( radians( latitude ) ) ) ) AS distance");
        $builder->having('distance <', $radiusInKm);
        $builder->orderBy('distance', 'ASC');
        $builder->limit(1);
        $query = $builder->get();
        return $query->getRowArray();
    }

    public function findAllNearby(float $lat, float $lon, float $radiusInKm = 25): array
    {
        $builder = $this->db->table($this->table);

        // Haversine-Formel in SQL, um die Distanz zu berechnen
        $builder->select("*, ( 6371 * acos( cos( radians({$lat}) ) * cos( radians( latitude ) ) * cos( radians( longitude ) - radians({$lon}) ) + sin( radians({$lat}) ) * sin( radians( latitude ) ) ) ) AS distance");
        $builder->having('distance <', $radiusInKm);
        $builder->orderBy('distance', 'ASC');

        $query = $builder->get();

        return $query->getResultArray(); // WICHTIG: Gibt alle Ergebnisse als Array zurück
    }

    /**
     * Holt alle Anbieter mit deren Durchschnittsbewertungen für die Kartenanzeige.
     */
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
