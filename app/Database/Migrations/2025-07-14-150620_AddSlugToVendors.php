<?php

namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;

class AddSlugToVendors extends Migration
{
    public function up()
    {
        $this->forge->addColumn('vendors', [
            'slug' => [
                'type'       => 'VARCHAR',
                'constraint' => 150,
                'null'       => true,
                'unique'     => true,
                'after'      => 'name',
            ],
        ]);
    }

    public function down()
    {
        //
    }
}
