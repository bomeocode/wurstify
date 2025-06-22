<?php

namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;

class CreateLayoutsTables extends Migration
{
    public function up()
    {
        // Tabelle 'layouts'
        $this->forge->addField([
            'id' => ['type' => 'INT', 'constraint' => 11, 'unsigned' => true, 'auto_increment' => true],
            'uuid' => ['type' => 'VARCHAR', 'constraint' => 32, 'unique' => true],
            'user_id' => ['type' => 'INT', 'constraint' => 11, 'unsigned' => true],
            'name' => ['type' => 'VARCHAR', 'constraint' => '255'],
            'layout_template' => ['type' => 'VARCHAR', 'constraint' => 50, 'comment' => 'Dateiname der Vorlage, z.B. 3-col'],
            'created_at' => ['type' => 'DATETIME', 'null' => true],
            'updated_at' => ['type' => 'DATETIME', 'null' => true],
        ]);
        $this->forge->addKey('id', true);
        $this->forge->addForeignKey('user_id', 'users', 'id', 'CASCADE', 'CASCADE');
        $this->forge->createTable('layouts');

        // Tabelle 'layout_slots'
        $this->forge->addField([
            'id' => ['type' => 'INT', 'constraint' => 11, 'unsigned' => true, 'auto_increment' => true],
            'layout_id' => ['type' => 'INT', 'constraint' => 11, 'unsigned' => true],
            'slot_name' => ['type' => 'VARCHAR', 'constraint' => 50],
            'media_uuid' => ['type' => 'VARCHAR', 'constraint' => 32, 'null' => true],
            'widget_type' => ['type' => 'VARCHAR', 'constraint' => 50, 'null' => true],
            'widget_data' => ['type' => 'TEXT', 'null' => true],
            'updated_at' => ['type' => 'DATETIME', 'null' => true],
        ]);
        $this->forge->addKey('id', true);
        $this->forge->addForeignKey('layout_id', 'layouts', 'id', 'CASCADE', 'CASCADE');
        $this->forge->addForeignKey('media_uuid', 'media', 'uuid', 'SET NULL', 'CASCADE');
        $this->forge->createTable('layout_slots');
    }

    public function down()
    {
        $this->forge->dropTable('layout_slots');
        $this->forge->dropTable('layouts');
    }
}
