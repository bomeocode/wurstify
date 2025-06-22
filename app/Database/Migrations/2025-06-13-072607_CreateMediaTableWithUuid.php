<?php

namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;

class CreateMediaTable extends Migration
{
    public function up()
    {
        $this->forge->addField([
            'id' => [
                'type'           => 'INT',
                'constraint'     => 11,
                'unsigned'       => true,
                'auto_increment' => true,
            ],
            'uuid' => [
                'type'       => 'VARCHAR',
                'constraint' => 32,
                'unique'     => true,
                'comment'    => 'Öffentliche, einzigartige ID für URLs.',
            ],
            'original_name' => [
                'type'       => 'VARCHAR',
                'constraint' => '255',
                'comment'    => 'Der ursprüngliche Dateiname.',
            ],
            'stored_name' => [
                'type'       => 'VARCHAR',
                'constraint' => '255',
                'comment'    => 'Der zufällige, gespeicherte Dateiname.',
            ],
            'file_type' => [
                'type'       => 'VARCHAR',
                'constraint' => '255',
                'comment'    => 'Der MIME-Type der Datei.',
            ],
            'file_size' => [
                'type'       => 'INT',
                'constraint' => 11,
                'unsigned'   => true,
                'comment'    => 'Dateigröße in Bytes.',
            ],
            'uploaded_by_id' => [
                'type'       => 'INT',
                'constraint' => 11,
                'unsigned'   => true,
                'comment'    => 'FK zum Users-Table (Shield).',
            ],
            'created_at' => [
                'type' => 'DATETIME',
                'null' => true,
            ],
        ]);
        $this->forge->addKey('id', true);
        $this->forge->addForeignKey('uploaded_by_id', 'users', 'id', 'CASCADE', 'CASCADE');
        $this->forge->createTable('media');
    }

    public function down()
    {
        $this->forge->dropTable('media');
    }
}
