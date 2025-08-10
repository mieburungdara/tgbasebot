<?php

defined('BASEPATH') OR exit('No direct script access allowed');

class Migration_Add_handler_type_to_bots_table extends CI_Migration {

    public function up()
    {
        $fields = array(
            'handler_type' => array(
                'type' => 'VARCHAR',
                'constraint' => '255',
                'null' => FALSE,
                'default' => 'DefaultBotHandler'
            )
        );
        $this->dbforge->add_column('bots', $fields);
    }

    public function down()
    {
        $this->dbforge->drop_column('bots', 'handler_type');
    }
}
