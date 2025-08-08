<?php
defined('BASEPATH') OR exit('No direct script access allowed');

class Migration_Add_bot_id_to_bot_logs_table extends CI_Migration {

    public function up()
    {
        if (!$this->db->field_exists('bot_id', 'bot_logs'))
        {
            $fields = array(
                'bot_id' => array(
                    'type' => 'INT',
                    'constraint' => 11,
                    'unsigned' => TRUE,
                    'null' => TRUE // Nullable untuk menangani data yang sudah ada
                )
            );
            $this->dbforge->add_column('bot_logs', $fields);
        }
    }

    public function down()
    {
        $this->dbforge->drop_column('bot_logs', 'bot_id');
    }
}
