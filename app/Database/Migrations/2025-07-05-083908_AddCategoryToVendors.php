<?php

namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;

class AddCategoryToVendors extends Migration
{
    public function up()
    {
        $this->forge->addColumn('vendors', [
            'category' => [
                'type'       => "ENUM('stationär', 'mobil')",
                'default'    => 'stationär',
                'null'       => false,
                'after'      => 'address',
            ],
        ]);
    }

    public function down()
    {
        $this->forge->dropColumn('vendors', 'category');
    }
}
