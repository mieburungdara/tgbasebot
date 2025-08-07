<?php
defined('BASEPATH') OR exit('No direct script access allowed');

class Migration_Add_status_to_users_table extends CI_Migration {

    public function up()
    {
        if (!$this->db->field_exists('status', 'users'))
        {
            $fields = array(
                'status' => array(
                    'type' => 'TEXT',
                    'constraint' => 20,
                    'default' => 'active',
                    'null' => FALSE
                )
            );
            $this->dbforge->add_column('users', $fields);
            $this->db->query('CREATE INDEX status_index ON users(status)');
        }
    }

    public function down()
    {
        // Hapus indeks terlebih dahulu jika ada, sintaks bisa bervariasi antar DB
        // Untuk SQLite (default CI DB), penghapusan kolom tidak didukung secara langsung
        // jadi kita biarkan saja atau gunakan metode yang lebih kompleks jika diperlukan.
        // Untuk MySQL/PostgreSQL, ini akan berfungsi:
        // $this->db->query('DROP INDEX status_index ON users');
        $this->dbforge->drop_column('users', 'status');
    }
}
