<?php

namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;

class CreateUserLevelsTable extends Migration
{
    public function up()
    {
        $this->forge->addField([
            'id' => ['type' => 'INT', 'constraint' => 5, 'unsigned' => true, 'auto_increment' => true],
            'level_number' => ['type' => 'INT', 'constraint' => 5, 'unsigned' => true, 'unique' => true],
            'name' => ['type' => 'VARCHAR', 'constraint' => 100],
            'min_ratings' => ['type' => 'INT', 'constraint' => 11, 'unsigned' => true],
        ]);
        $this->forge->addKey('id', true);
        $this->forge->createTable('user_levels');
    }

    public function down()
    {
        $this->forge->dropTable('user_levels');
    }
}
