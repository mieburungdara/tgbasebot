<?php
defined('BASEPATH') OR exit('No direct script access allowed');

class Migration_Add_targeting_to_broadcasts_table extends CI_Migration {

    public function up()
    {
        $fields = array(
            'target_tag' => array(
                'type' => 'VARCHAR',
                'constraint' => 255,
                'null' => TRUE, // NULL berarti semua pengguna
                'after' => 'message'
            ),
            'is_test_broadcast' => array(
                'type' => 'TINYINT',
                'constraint' => 1,
                'default' => 0,
                'null' => FALSE,
                'after' => 'target_tag'
            )
        );
        $this->dbforge->add_column('broadcasts', $fields);
    }

    public function down()
    {
        $this->dbforge->drop_column('broadcasts', 'target_tag');
        $this->dbforge->drop_column('broadcasts', 'is_test_broadcast');
    }
}
