<?php

namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;

class CreateVendorClaimsTable extends Migration
{
    public function up()
    {
        $this->forge->addField([
            'id' => ['type' => 'INT', 'constraint' => 11, 'unsigned' => true, 'auto_increment' => true],
            'vendor_id' => ['type' => 'INT', 'constraint' => 11, 'unsigned' => true],
            'user_id' => ['type' => 'INT', 'constraint' => 11, 'unsigned' => true],
            'claimant_name' => ['type' => 'VARCHAR', 'constraint' => 255],
            'contact_email' => ['type' => 'VARCHAR', 'constraint' => 255],
            'proof_text' => ['type' => 'TEXT'],
            'status' => ['type' => "ENUM('pending', 'approved', 'rejected')", 'default' => 'pending'],
            'ip_address' => ['type' => 'VARCHAR', 'constraint' => 45, 'null' => true],
            'user_agent' => ['type' => 'VARCHAR', 'constraint' => 255, 'null' => true],
            'created_at' => ['type' => 'DATETIME', 'null' => true],
            'updated_at' => ['type' => 'DATETIME', 'null' => true],
        ]);
        $this->forge->addKey('id', true);
        $this->forge->addForeignKey('vendor_id', 'vendors', 'id', 'CASCADE', 'CASCADE');
        $this->forge->addForeignKey('user_id', 'users', 'id', 'CASCADE', 'CASCADE');
        $this->forge->createTable('vendor_claims');
    }

    public function down()
    {
        $this->forge->dropTable('vendor_claims');
    }
}
