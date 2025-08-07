<?php
defined('BASEPATH') OR exit('No direct script access allowed');

class Migration_Add_targeting_to_broadcasts_table extends CI_Migration {

    public function up()
    {
        if (!$this->db->field_exists('target_tag', 'broadcasts'))
        {
            $target_tag_field = ['target_tag' => ['type' => 'VARCHAR', 'constraint' => 255, 'null' => TRUE]];
            $this->dbforge->add_column('broadcasts', $target_tag_field);
        }

        if (!$this->db->field_exists('is_test_broadcast', 'broadcasts'))
        {
            $is_test_broadcast_field = [
                'is_test_broadcast' => ['type' => 'TINYINT', 'constraint' => 1, 'default' => 0, 'null' => FALSE]
            ];
            $this->dbforge->add_column('broadcasts', $is_test_broadcast_field);
        }
    }

    public function down()
    {
        $this->dbforge->drop_column('broadcasts', 'target_tag');
        $this->dbforge->drop_column('broadcasts', 'is_test_broadcast');
    }
}
