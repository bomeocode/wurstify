<?php

namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;

class AddImagesToRatings extends Migration
{
    public function up()
    {
        $this->forge->addColumn('ratings', [
            'image1' => [
                'type'       => 'VARCHAR',
                'constraint' => 255,
                'null'       => true,
            ],
            'image2' => [
                'type'       => 'VARCHAR',
                'constraint' => 255,
                'null'       => true,
            ],
            'image3' => [
                'type'       => 'VARCHAR',
                'constraint' => 255,
                'null'       => true,
            ],
        ]);
    }

    public function down()
    {
        $this->forge->dropColumn('ratings', ['image1', 'image2', 'image3']);
    }
}
