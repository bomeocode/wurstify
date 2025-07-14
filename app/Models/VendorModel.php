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
    protected $allowedFields    = [
        'uuid',
        'name',
        'address',
        'latitude',
        'longitude',
        'category',
        'owner_user_id',
        'description',
        'opening_hours',
        'website_url',
        'social_media',
        'cover_image',
        'logo_image',
        'slug'
    ];
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
    public function getVendorsWithAverageRatings(?string $uuid = null)
    {
        $builder = $this->select('
            vendors.id,
            vendors.uuid,
            vendors.name as vendor_name,
            vendors.address,
            vendors.latitude,
            vendors.longitude,
            vendors.category,
            vendors.description,
            vendors.opening_hours,
            vendors.website_url,
            vendors.social_media,
            vendors.cover_image,
            vendors.logo_image,
            AVG(ratings.rating_taste) as avg_taste,
            AVG(ratings.rating_appearance) as avg_appearance,
            AVG(ratings.rating_presentation) as avg_presentation,
            AVG(ratings.rating_price) as avg_price,
            AVG(ratings.rating_service) as avg_service,
            COUNT(ratings.id) as total_ratings
        ')
            ->join('ratings', 'ratings.vendor_id = vendors.id', 'left')
            ->groupBy('vendors.id');

        if ($uuid !== null) {
            $builder->where('vendors.uuid', $uuid);
        }

        return $builder->findAll();
    }

    /**
     * Holt eine paginierte und durchsuchbare Liste aller Anbieter.
     * @param string|null $searchTerm
     * @return array
     */
    public function getPaginatedVendors(?string $searchTerm = null)
    {
        $builder = $this->orderBy('name', 'ASC');

        if ($searchTerm) {
            $builder->groupStart()
                ->like('name', $searchTerm)
                ->orLike('address', $searchTerm)
                ->groupEnd();
        }

        return $builder->paginate(15); // 15 Anbieter pro Seite
    }

    public function findBySlug(string $slug)
    {
        return $this->where('slug', $slug)->first();
    }
}
