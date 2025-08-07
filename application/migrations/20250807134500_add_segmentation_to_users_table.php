<?php
defined('BASEPATH') OR exit('No direct script access allowed');

class Migration_Add_segmentation_to_users_table extends CI_Migration {

    public function up()
    {
        $fields = array(
            'tags' => array(
                'type' => 'TEXT',
                'null' => TRUE,
                'after' => 'status'
            ),
            'is_test_user' => array(
                'type' => 'TINYINT',
                'constraint' => 1,
                'default' => 0,
                'null' => FALSE,
                'after' => 'tags'
            )
        );
        $this->dbforge->add_column('users', $fields);
        $this->db->query('CREATE INDEX is_test_user_index ON users(is_test_user)');
    }

    public function down()
    {
        $this->dbforge->drop_column('users', 'tags');
        $this->dbforge->drop_column('users', 'is_test_user');
    }
}
