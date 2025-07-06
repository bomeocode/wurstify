<?php

namespace App\Database\Seeds;

use CodeIgniter\Database\Seeder;

class UserLevel extends Seeder
{
    public function run()
    {
        $data = [
            ['level_number' => 1, 'name' => 'Wurst-Lehrling', 'min_ratings' => 1],
            ['level_number' => 2, 'name' => 'Bratwurst-Geselle', 'min_ratings' => 10],
            ['level_number' => 3, 'name' => 'Grill-Meister', 'min_ratings' => 25],
            ['level_number' => 4, 'name' => 'Wurst-Baron', 'min_ratings' => 50],
            ['level_number' => 5, 'name' => 'Der Wurst-Kaiser', 'min_ratings' => 100],
        ];
        $this->db->table('user_levels')->insertBatch($data);
    }
}
