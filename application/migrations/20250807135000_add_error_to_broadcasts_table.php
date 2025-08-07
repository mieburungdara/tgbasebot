<?php
defined('BASEPATH') OR exit('No direct script access allowed');

class Migration_Add_error_to_broadcasts_table extends CI_Migration {

    public function up()
    {
        $fields = array(
            'last_error_message' => array(
                'type' => 'TEXT',
                'null' => TRUE,
                'after' => 'failed_count'
            )
        );
        $this->dbforge->add_column('broadcasts', $fields);
    }

    public function down()
    {
        $this->dbforge->drop_column('broadcasts', 'last_error_message');
    }
}
