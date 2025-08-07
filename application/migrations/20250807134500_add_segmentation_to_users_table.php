<?php
defined('BASEPATH') OR exit('No direct script access allowed');

class Migration_Add_segmentation_to_users_table extends CI_Migration {

    public function up()
    {
        if (!$this->db->field_exists('tags', 'users'))
        {
            $tags_field = ['tags' => ['type' => 'TEXT', 'null' => TRUE]];
            $this->dbforge->add_column('users', $tags_field);
        }

        if (!$this->db->field_exists('is_test_user', 'users'))
        {
            $test_user_field = [
                'is_test_user' => ['type' => 'TINYINT', 'constraint' => 1, 'default' => 0, 'null' => FALSE]
            ];
            $this->dbforge->add_column('users', $test_user_field);
            $this->db->query('CREATE INDEX is_test_user_index ON users(is_test_user)');
        }
    }

    public function down()
    {
        $this->dbforge->drop_column('users', 'tags');
        $this->dbforge->drop_column('users', 'is_test_user');
    }
}
