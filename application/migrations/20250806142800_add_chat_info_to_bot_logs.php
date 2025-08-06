<?php
defined('BASEPATH') OR exit('No direct script access allowed');

class Migration_Add_chat_info_to_bot_logs extends CI_Migration {

    public function up()
    {
        $fields = array(
            'chat_id' => array(
                'type' => 'BIGINT',
                'constraint' => 20,
                'null' => TRUE,
                'after' => 'log_message'
            ),
            'chat_name' => array(
                'type' => 'VARCHAR',
                'constraint' => '255',
                'null' => TRUE,
                'after' => 'chat_id'
            )
        );
        $this->dbforge->add_column('bot_logs', $fields);
    }

    public function down()
    {
        $this->dbforge->drop_column('bot_logs', 'chat_id');
        $this->dbforge->drop_column('bot_logs', 'chat_name');
    }
}
