<?php
defined('BASEPATH') OR exit('No direct script access allowed');

class Migration_Add_bot_id_to_broadcasts_table extends CI_Migration {

    public function up()
    {
        if (!$this->db->field_exists('bot_id', 'broadcasts'))
        {
            $fields = array(
                'bot_id' => array(
                    'type' => 'INT',
                    'constraint' => 11,
                    'unsigned' => TRUE,
                    'null' => TRUE
                )
            );
            $this->dbforge->add_column('broadcasts', $fields);
        }
    }

    public function down()
    {
        $this->dbforge->drop_column('broadcasts', 'bot_id');
    }
}
