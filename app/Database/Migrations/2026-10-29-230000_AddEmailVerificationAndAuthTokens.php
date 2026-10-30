<?php

namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;

class AddEmailVerificationAndAuthTokens extends Migration
{
    public function up()
    {
        $this->forge->addColumn('usuarios', [
            'email_verified_at' => [
                'type' => 'DATETIME',
                'null' => true,
                'after' => 'email',
            ],
            'auth_version' => [
                'type' => 'INT',
                'unsigned' => true,
                'default' => 0,
                'after' => 'activo',
            ],
        ]);

        $this->db->table('usuarios')
            ->set('email_verified_at', 'CURRENT_TIMESTAMP', false)
            ->update();

        $this->forge->addField([
            'id' => [
                'type' => 'INT',
                'unsigned' => true,
                'auto_increment' => true,
            ],
            'user_id' => [
                'type' => 'INT',
                'unsigned' => true,
            ],
            'purpose' => [
                'type' => 'VARCHAR',
                'constraint' => 32,
            ],
            'token_hash' => [
                'type' => 'CHAR',
                'constraint' => 64,
            ],
            'expires_at' => [
                'type' => 'DATETIME',
            ],
            'created_at' => [
                'type' => 'DATETIME',
            ],
        ]);
        $this->forge->addPrimaryKey('id');
        $this->forge->addUniqueKey('token_hash', 'uq_auth_tokens_hash');
        $this->forge->addKey(['user_id', 'purpose'], false, false, 'idx_auth_tokens_user_purpose');
        $this->forge->addForeignKey('user_id', 'usuarios', 'id', 'CASCADE', 'CASCADE');
        $this->forge->createTable('auth_tokens');
    }

    public function down()
    {
        $this->forge->dropTable('auth_tokens', true);
        $this->forge->dropColumn('usuarios', 'auth_version');
        $this->forge->dropColumn('usuarios', 'email_verified_at');
    }
}
