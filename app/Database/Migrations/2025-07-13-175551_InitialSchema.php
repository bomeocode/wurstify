<?php

namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;

class InitialSchema extends Migration
{
    public function up()
    {
        // --------------------------------------------------------------------
        // Tabelle: users (als Erstes erstellen, da viele andere davon abhängen)
        // --------------------------------------------------------------------
        $this->forge->addField([
            'id'             => ['type' => 'INT', 'constraint' => 11, 'unsigned' => true, 'auto_increment' => true],
            'username'       => ['type' => 'VARCHAR', 'constraint' => '30', 'null' => true],
            'avatar'         => ['type' => 'VARCHAR', 'constraint' => '255', 'null' => true],
            'bio'            => ['type' => 'VARCHAR', 'constraint' => '255', 'null' => true],
            'status'         => ['type' => 'VARCHAR', 'constraint' => '255', 'null' => true],
            'status_message' => ['type' => 'VARCHAR', 'constraint' => '255', 'null' => true],
            'active'         => ['type' => 'TINYINT', 'constraint' => 1, 'null' => false, 'default' => 0],
            'last_active'    => ['type' => 'DATETIME', 'null' => true],
            'created_at'     => ['type' => 'DATETIME', 'null' => true],
            'updated_at'     => ['type' => 'DATETIME', 'null' => true],
            'deleted_at'     => ['type' => 'DATETIME', 'null' => true],
        ]);
        $this->forge->addKey('id', true);
        $this->forge->addUniqueKey('username');
        $this->forge->createTable('users');

        // --------------------------------------------------------------------
        // Tabelle: vendors
        // --------------------------------------------------------------------
        $this->forge->addField([
            'id'              => ['type' => 'INT', 'constraint' => 11, 'unsigned' => true, 'auto_increment' => true],
            'owner_user_id'   => ['type' => 'INT', 'constraint' => 11, 'unsigned' => true, 'null' => true],
            'uuid'            => ['type' => 'VARCHAR', 'constraint' => '100', 'null' => true],
            'name'            => ['type' => 'VARCHAR', 'constraint' => '255', 'null' => false],
            'address'         => ['type' => 'VARCHAR', 'constraint' => '255', 'null' => true],
            'latitude'        => ['type' => 'DECIMAL', 'constraint' => '10,8', 'null' => false],
            'longitude'       => ['type' => 'DECIMAL', 'constraint' => '11,8', 'null' => false],
            'category'        => ['type' => 'ENUM', 'constraint' => ['stationär', 'mobil'], 'default' => 'stationär', 'null' => false],
            'description'     => ['type' => 'TEXT', 'null' => true],
            'opening_hours'   => ['type' => 'TEXT', 'null' => true],
            'website_url'     => ['type' => 'VARCHAR', 'constraint' => '255', 'null' => true],
            'social_media'    => ['type' => 'TEXT', 'null' => true],
            'cover_image'     => ['type' => 'VARCHAR', 'constraint' => '255', 'null' => true],
            'logo_image'      => ['type' => 'VARCHAR', 'constraint' => '255', 'null' => true],
            'created_at'      => ['type' => 'DATETIME', 'null' => true],
            'updated_at'      => ['type' => 'DATETIME', 'null' => true],
        ]);
        $this->forge->addKey('id', true);
        $this->forge->addUniqueKey('uuid');
        // FK wird nach Erstellung der 'users' Tabelle hinzugefügt, falls nötig
        $this->forge->createTable('vendors');

        // --------------------------------------------------------------------
        // Tabelle: ratings (hängt von users und vendors ab)
        // --------------------------------------------------------------------
        $this->forge->addField([
            'id'                  => ['type' => 'INT', 'constraint' => 11, 'unsigned' => true, 'auto_increment' => true],
            'user_id'             => ['type' => 'INT', 'constraint' => 11, 'unsigned' => true],
            'vendor_id'           => ['type' => 'INT', 'constraint' => 11, 'unsigned' => true, 'null' => true],
            'rating_appearance'   => ['type' => 'TINYINT', 'constraint' => 1, 'unsigned' => true, 'default' => 0],
            'rating_taste'        => ['type' => 'TINYINT', 'constraint' => 1, 'unsigned' => true, 'default' => 0],
            'rating_presentation' => ['type' => 'TINYINT', 'constraint' => 1, 'unsigned' => true, 'default' => 0],
            'rating_price'        => ['type' => 'TINYINT', 'constraint' => 1, 'unsigned' => true, 'default' => 0],
            'rating_service'      => ['type' => 'TINYINT', 'constraint' => 1, 'unsigned' => true, 'default' => 0],
            'comment'             => ['type' => 'TEXT', 'null' => true],
            'helpful_count'       => ['type' => 'INT', 'constraint' => 11, 'unsigned' => true, 'default' => 0],
            'image1'              => ['type' => 'VARCHAR', 'constraint' => '255', 'null' => true],
            'image2'              => ['type' => 'VARCHAR', 'constraint' => '255', 'null' => true],
            'image3'              => ['type' => 'VARCHAR', 'constraint' => '255', 'null' => true],
            'created_at'          => ['type' => 'DATETIME', 'null' => true],
            'updated_at'          => ['type' => 'DATETIME', 'null' => true],
        ]);
        $this->forge->addKey('id', true);
        $this->forge->addForeignKey('user_id', 'users', 'id', 'CASCADE', 'CASCADE');
        $this->forge->addForeignKey('vendor_id', 'vendors', 'id', 'SET NULL', 'CASCADE');
        $this->forge->createTable('ratings');

        // --------------------------------------------------------------------
        // Tabelle: rating_votes (hängt von users und ratings ab)
        // --------------------------------------------------------------------
        $this->forge->addField([
            'id'         => ['type' => 'INT', 'constraint' => 11, 'unsigned' => true, 'auto_increment' => true],
            'rating_id'  => ['type' => 'INT', 'constraint' => 11, 'unsigned' => true],
            'user_id'    => ['type' => 'INT', 'constraint' => 11, 'unsigned' => true],
            'created_at' => ['type' => 'DATETIME', 'null' => true],
        ]);
        $this->forge->addKey('id', true);
        $this->forge->addUniqueKey(['rating_id', 'user_id']);
        $this->forge->addForeignKey('rating_id', 'ratings', 'id', 'CASCADE', 'CASCADE');
        $this->forge->addForeignKey('user_id', 'users', 'id', 'CASCADE', 'CASCADE');
        $this->forge->createTable('rating_votes');

        // --------------------------------------------------------------------
        // Weitere Tabellen
        // --------------------------------------------------------------------

        // feedback
        $this->forge->addField([
            'id'            => ['type' => 'INT', 'constraint' => 11, 'unsigned' => true, 'auto_increment' => true],
            'user_id'       => ['type' => 'INT', 'constraint' => 11, 'unsigned' => true, 'null' => true],
            'feedback_text' => ['type' => 'TEXT', 'null' => false],
            'user_agent'    => ['type' => 'VARCHAR', 'constraint' => '255', 'null' => true],
            'created_at'    => ['type' => 'DATETIME', 'null' => true],
            'updated_at'    => ['type' => 'DATETIME', 'null' => true],
        ]);
        $this->forge->addKey('id', true);
        $this->forge->addForeignKey('user_id', 'users', 'id', 'SET NULL', 'SET NULL');
        $this->forge->createTable('feedback');

        // layouts
        $this->forge->addField([
            'id'              => ['type' => 'INT', 'constraint' => 11, 'unsigned' => true, 'auto_increment' => true],
            'uuid'            => ['type' => 'VARCHAR', 'constraint' => '32', 'null' => false],
            'user_id'         => ['type' => 'INT', 'constraint' => 11, 'unsigned' => true],
            'name'            => ['type' => 'VARCHAR', 'constraint' => '255', 'null' => false],
            'layout_template' => ['type' => 'VARCHAR', 'constraint' => '50', 'null' => false, 'comment' => 'Dateiname der Vorlage, z.B. 3-col'],
            'created_at'      => ['type' => 'DATETIME', 'null' => true],
            'updated_at'      => ['type' => 'DATETIME', 'null' => true],
        ]);
        $this->forge->addKey('id', true);
        $this->forge->addUniqueKey('uuid');
        $this->forge->addForeignKey('user_id', 'users', 'id', 'CASCADE', 'CASCADE');
        $this->forge->createTable('layouts');

        // media
        $this->forge->addField([
            'id'               => ['type' => 'INT', 'constraint' => 11, 'unsigned' => true, 'auto_increment' => true],
            'uuid'             => ['type' => 'VARCHAR', 'constraint' => '32', 'null' => false, 'comment' => 'Öffentliche, einzigartige ID für URLs.'],
            'original_name'    => ['type' => 'VARCHAR', 'constraint' => '255', 'null' => false, 'comment' => 'Der ursprüngliche Dateiname.'],
            'stored_name'      => ['type' => 'VARCHAR', 'constraint' => '255', 'null' => false, 'comment' => 'Der zufällige, gespeicherte Dateiname.'],
            'file_type'        => ['type' => 'VARCHAR', 'constraint' => '255', 'null' => false, 'comment' => 'Der MIME-Type der Datei.'],
            'file_size'        => ['type' => 'INT', 'constraint' => 11, 'unsigned' => true, 'null' => false, 'comment' => 'Dateigröße in Bytes.'],
            'uploaded_by_id'   => ['type' => 'INT', 'constraint' => 11, 'unsigned' => true, 'comment' => 'FK zum Users-Table (Shield).'],
            'created_at'       => ['type' => 'DATETIME', 'null' => true],
        ]);
        $this->forge->addKey('id', true);
        $this->forge->addUniqueKey('uuid');
        $this->forge->addForeignKey('uploaded_by_id', 'users', 'id', 'CASCADE', 'CASCADE');
        $this->forge->createTable('media');

        // settings
        $this->forge->addField([
            'id'         => ['type' => 'INT', 'constraint' => 9, 'auto_increment' => true],
            'class'      => ['type' => 'VARCHAR', 'constraint' => '255', 'null' => false],
            'key'        => ['type' => 'VARCHAR', 'constraint' => '255', 'null' => false],
            'value'      => ['type' => 'TEXT', 'null' => true],
            'type'       => ['type' => 'VARCHAR', 'constraint' => '31', 'null' => false, 'default' => 'string'],
            'context'    => ['type' => 'VARCHAR', 'constraint' => '255', 'null' => true],
            'created_at' => ['type' => 'DATETIME', 'null' => false],
            'updated_at' => ['type' => 'DATETIME', 'null' => false],
        ]);
        $this->forge->addKey('id', true);
        $this->forge->createTable('settings');

        // user_levels
        $this->forge->addField([
            'id'          => ['type' => 'INT', 'constraint' => 5, 'unsigned' => true, 'auto_increment' => true],
            'level_number' => ['type' => 'INT', 'constraint' => 5, 'unsigned' => true, 'null' => false],
            'name'        => ['type' => 'VARCHAR', 'constraint' => '100', 'null' => false],
            'min_ratings' => ['type' => 'INT', 'constraint' => 11, 'unsigned' => true, 'null' => false],
        ]);
        $this->forge->addKey('id', true);
        $this->forge->addUniqueKey('level_number');
        $this->forge->createTable('user_levels');
    }

    //--------------------------------------------------------------------

    public function down()
    {
        // Alle Tabellen in umgekehrter Reihenfolge der Erstellung löschen,
        // um Foreign-Key-Konflikte zu vermeiden. Das 'true' als zweiter
        // Parameter fügt ein "IF EXISTS" hinzu, um Fehler zu vermeiden.

        $this->forge->dropTable('rating_votes', true);
        $this->forge->dropTable('ratings', true);
        $this->forge->dropTable('vendors', true);
        $this->forge->dropTable('media', true);
        $this->forge->dropTable('layouts', true);
        $this->forge->dropTable('feedback', true);
        $this->forge->dropTable('auth_remember_tokens', true);
        $this->forge->dropTable('auth_permissions_users', true);
        $this->forge->dropTable('auth_logins', true);
        $this->forge->dropTable('auth_identities', true);
        $this->forge->dropTable('auth_groups_users', true);
        $this->forge->dropTable('user_levels', true);
        $this->forge->dropTable('settings', true);

        // Die 'users'-Tabelle als eine der letzten löschen
        $this->forge->dropTable('users', true);

        // Die Tabelle 'migrations' wird von CodeIgniter verwaltet und sollte
        // in der Regel NICHT manuell gelöscht werden.
    }
}
