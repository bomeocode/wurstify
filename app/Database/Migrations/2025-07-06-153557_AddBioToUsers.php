<?php

namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;

class AddBioToUsers extends Migration
{
    public function up()
    {
        $this->forge->addColumn('users', [
            'bio' => [
                'type'       => 'VARCHAR',
                'constraint' => 255,
                'null'       => true,
                'after'      => 'avatar'
            ],
        ]);
    }

    public function down()
    {
        $this->forge->dropColumn('users', 'bio');
    }
}
