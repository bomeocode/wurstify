<?php

namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;

class AddQrFieldsToRatings extends Migration
{
    public function up()
    {
        $fields = [
            'type' => [
                'type'       => "ENUM('default', 'qr_code')",
                'default'    => 'default',
                'after'      => 'user_id', // Platziert die Spalte nach der user_id
                'comment'    => 'Unterscheidet normale von QR-Code-Bewertungen.'
            ],
            'qr_nickname' => [
                'type'       => 'VARCHAR',
                'constraint' => 100,
                'null'       => true,
                'after'      => 'type',
                'comment'    => 'Spitzname für anonyme QR-Code-Bewertungen.'
            ],
        ];

        $this->forge->addColumn('ratings', $fields);
    }

    public function down()
    {
        $this->forge->dropColumn('ratings', ['type', 'qr_nickname']);
    }
}
