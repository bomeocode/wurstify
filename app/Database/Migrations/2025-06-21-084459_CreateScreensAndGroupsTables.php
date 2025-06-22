<?php

namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;

class CreateScreensAndGroupsTables extends Migration
{
    public function up()
    {
        // 1. Tabelle für Bildschirm-Gruppen
        $this->forge->addField([
            'id' => ['type' => 'INT', 'constraint' => 11, 'unsigned' => true, 'auto_increment' => true],
            'user_id' => ['type' => 'INT', 'constraint' => 11, 'unsigned' => true],
            'name' => ['type' => 'VARCHAR', 'constraint' => 255, 'comment' => 'z.B. Lehrerzimmer, Foyer EG'],
            'layout_uuid' => ['type' => 'VARCHAR', 'constraint' => 32, 'null' => true, 'comment' => 'Das Layout für diese Gruppe'],
            'created_at' => ['type' => 'DATETIME', 'null' => true],
            'updated_at' => ['type' => 'DATETIME', 'null' => true],
        ]);
        $this->forge->addKey('id', true);
        $this->forge->addForeignKey('user_id', 'users', 'id', 'CASCADE', 'CASCADE');
        $this->forge->addForeignKey('layout_uuid', 'layouts', 'uuid', 'SET NULL', 'CASCADE');
        $this->forge->createTable('screen_groups');

        // 2. Tabelle für einzelne Bildschirme
        $this->forge->addField([
            'id' => ['type' => 'INT', 'constraint' => 11, 'unsigned' => true, 'auto_increment' => true],
            'uuid' => ['type' => 'VARCHAR', 'constraint' => 32, 'unique' => true, 'comment' => 'Die öffentliche Anzeige-URL'],
            'user_id' => ['type' => 'INT', 'constraint' => 11, 'unsigned' => true],
            'screen_group_id' => ['type' => 'INT', 'constraint' => 11, 'unsigned' => true, 'null' => true, 'comment' => 'Zu welcher Gruppe gehört der Screen?'],
            'layout_uuid' => ['type' => 'VARCHAR', 'constraint' => 32, 'null' => true, 'comment' => 'Ein optionales, überschreibendes Layout'],
            'name' => ['type' => 'VARCHAR', 'constraint' => 255, 'comment' => 'z.B. TV Foyer links'],
            'location' => ['type' => 'VARCHAR', 'constraint' => 255, 'null' => true, 'comment' => 'z.B. Raum 204'],
            'created_at' => ['type' => 'DATETIME', 'null' => true],
            'updated_at' => ['type' => 'DATETIME', 'null' => true],
        ]);
        $this->forge->addKey('id', true);
        $this->forge->addForeignKey('user_id', 'users', 'id', 'CASCADE', 'CASCADE');
        $this->forge->addForeignKey('screen_group_id', 'screen_groups', 'id', 'SET NULL', 'CASCADE');
        $this->forge->addForeignKey('layout_uuid', 'layouts', 'uuid', 'SET NULL', 'CASCADE');
        $this->forge->createTable('screens');
    }

    public function down()
    {
        $this->forge->dropTable('screens');
        $this->forge->dropTable('screen_groups');
    }
}
