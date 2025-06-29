<?php

namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;

class AddUpdatedAtToFeedback extends Migration
{
    public function up()
    {
        $this->forge->addColumn('feedback', [
            'updated_at' => [
                'type' => 'DATETIME',
                'null' => true,
                'after' => 'created_at'
            ],
        ]);
    }

    public function down()
    {
        $this->forge->dropColumn('feedback', 'updated_at');
    }
}
