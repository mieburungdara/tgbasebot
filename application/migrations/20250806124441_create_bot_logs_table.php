<?php
defined('BASEPATH') OR exit('No direct script access allowed');

class Migration_Create_bot_logs_table extends CI_Migration {

    public function up()
    {
        $this->dbforge->add_field(array(
            'id' => array(
                'type' => 'INTEGER',
                'constraint' => 11,
                'unsigned' => TRUE,
                'auto_increment' => TRUE
            ),
            'log_type' => array(
                'type' => 'VARCHAR',
                'constraint' => '50',
            ),
            'log_message' => array(
                'type' => 'TEXT',
                'null' => TRUE,
            ),
            'created_at' => array(
                'type' => 'DATETIME',
                'null' => TRUE,
            ),
        ));
        $this->dbforge->add_key('id', TRUE);
        $this->dbforge->create_table('bot_logs');
    }

    public function down()
    {
        $this->dbforge->drop_table('bot_logs');
    }
}
