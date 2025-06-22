<?php

// In ...._RefactorRatingsForVendors.php
namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;

class RefactorRatingsForVendors extends Migration
{
    public function up()
    {
        // 1. vendor_id Spalte hinzufügen
        $this->forge->addColumn('ratings', [
            'vendor_id' => [
                'type'       => 'INT',
                'constraint' => 11,
                'unsigned'   => true,
                'after'      => 'user_id' // Positioniert die Spalte nach user_id
            ]
        ]);

        // 2. Fremdschlüssel setzen
        $this->forge->addForeignKey('vendor_id', 'vendors', 'id', 'CASCADE', 'CASCADE');

        // 3. Alte, redundante Spalten entfernen
        $this->forge->dropColumn('ratings', ['vendor_name', 'latitude', 'longitude', 'address_manual']);
    }

    public function down()
    {
        // Macht die Änderungen rückgängig, falls nötig
        $this->forge->dropForeignKey('ratings', 'ratings_vendor_id_foreign');
        $this->forge->dropColumn('ratings', 'vendor_id');

        $this->forge->addColumn('ratings', [
            'vendor_name' => ['type' => 'VARCHAR', 'constraint' => '255'],
            'latitude' => ['type' => 'DECIMAL', 'constraint' => '10,8', 'null' => true],
            'longitude' => ['type' => 'DECIMAL', 'constraint' => '11,8', 'null' => true],
            'address_manual' => ['type' => 'VARCHAR', 'constraint' => '255', 'null' => true],
        ]);
    }
}
