<?php

namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;

class AddHelpfulVotes extends Migration
{
    public function up()
    {
        // 1. Erweitere die 'ratings'-Tabelle um einen Zähler
        $this->forge->addColumn('ratings', [
            'helpful_count' => [
                'type'       => 'INT',
                'constraint' => 11,
                'unsigned'   => true,
                'default'    => 0,
                'after'      => 'comment',
            ],
        ]);

        // 2. Erstelle eine neue Tabelle 'rating_votes', um die Stimmen zu speichern
        $this->forge->addField([
            'id' => ['type' => 'INT', 'constraint' => 11, 'unsigned' => true, 'auto_increment' => true],
            'rating_id' => ['type' => 'INT', 'constraint' => 11, 'unsigned' => true],
            'user_id' => ['type' => 'INT', 'constraint' => 11, 'unsigned' => true],
            'created_at' => ['type' => 'DATETIME', 'null' => true],
        ]);
        $this->forge->addKey('id', true);
        // Stellt sicher, dass jeder Nutzer nur einmal pro Bewertung abstimmen kann
        $this->forge->addUniqueKey(['rating_id', 'user_id']);
        $this->forge->addForeignKey('rating_id', 'ratings', 'id', 'CASCADE', 'CASCADE');
        $this->forge->addForeignKey('user_id', 'users', 'id', 'CASCADE', 'CASCADE');
        $this->forge->createTable('rating_votes');
    }

    public function down()
    {
        $this->forge->dropTable('rating_votes');
        $this->forge->dropColumn('ratings', 'helpful_count');
    }
}
