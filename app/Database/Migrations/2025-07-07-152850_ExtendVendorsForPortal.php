<?php

namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;

class ExtendVendorsForPortal extends Migration
{
    public function up()
    {
        $this->forge->addColumn('vendors', [
            'owner_user_id' => [
                'type'       => 'INT',
                'constraint' => 11,
                'unsigned'   => true,
                'null'       => true,
                'after'      => 'id',
            ],
            'description' => [
                'type' => 'TEXT',
                'null' => true,
                'after' => 'category',
            ],
            'opening_hours' => [
                'type' => 'TEXT', // TEXT ist gut für JSON-Daten
                'null' => true,
                'after' => 'description',
            ],
            'website_url' => [
                'type'       => 'VARCHAR',
                'constraint' => 255,
                'null'       => true,
                'after'      => 'opening_hours',
            ],
            'social_media' => [
                'type' => 'TEXT', // TEXT ist gut für JSON-Daten
                'null' => true,
                'after' => 'website_url',
            ],
            'cover_image' => [
                'type'       => 'VARCHAR',
                'constraint' => 255,
                'null'       => true,
                'after'      => 'social_media',
            ],
            'logo_image' => [
                'type'       => 'VARCHAR',
                'constraint' => 255,
                'null'       => true,
                'after'      => 'cover_image',
            ],
        ]);

        // Fügt den Fremdschlüssel für den Besitzer hinzu
        $this->forge->addForeignKey('owner_user_id', 'users', 'id', 'SET NULL', 'SET NULL');
    }

    public function down()
    {
        $this->forge->dropForeignKey('vendors', 'vendors_owner_user_id_foreign');
        $this->forge->dropColumn('vendors', [
            'owner_user_id',
            'description',
            'opening_hours',
            'website_url',
            'social_media',
            'cover_image',
            'logo_image',
        ]);
    }
}
