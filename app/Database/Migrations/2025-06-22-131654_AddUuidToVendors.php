<?php

namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;

class AddUuidToVendors extends Migration
{
    public function up()
    {
        $this->forge->addColumn('vendors', [
            'uuid' => [
                'type'       => 'VARCHAR',
                'constraint' => 100,
                'unique'     => true,
                'after'      => 'id',
            ],
        ]);
    }

    public function down()
    {
        $this->forge->dropColumn('vendors', 'uuid');
    }
}
