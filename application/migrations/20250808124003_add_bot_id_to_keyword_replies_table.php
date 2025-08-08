<?php
defined('BASEPATH') OR exit('No direct script access allowed');

class Migration_Add_bot_id_to_keyword_replies_table extends CI_Migration {

    public function up()
    {
        if (!$this->db->field_exists('bot_id', 'keyword_replies'))
        {
            $fields = array(
                'bot_id' => array(
                    'type' => 'INT',
                    'constraint' => 11,
                    'unsigned' => TRUE,
                    'null' => TRUE
                )
            );
            $this->dbforge->add_column('keyword_replies', $fields);
        }
    }

    public function down()
    {
        $this->dbforge->drop_column('keyword_replies', 'bot_id');
    }
}
