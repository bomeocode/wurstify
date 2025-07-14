<?php

namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;

class ModifyUserIdInRatingsTable extends Migration
{
    public function up()
    {
        $this->forge->modifyColumn('ratings', [
            'user_id' => [
                'type'       => 'INT',
                'constraint' => 11,
                'unsigned'   => true,
                'null'       => true, // Hier ist die Änderung
            ],
        ]);
    }

    public function down()
    {
        // Macht die Änderung rückgängig
        $this->forge->modifyColumn('ratings', [
            'user_id' => [
                'type'       => 'INT',
                'constraint' => 11,
                'unsigned'   => true,
                'null'       => false,
            ],
        ]);
    }
}
