<?php
defined('BASEPATH') OR exit('No direct script access allowed');

class Migration_Create_keyword_replies_table extends CI_Migration {

    public function up()
    {
        $this->dbforge->add_field(array(
            'id' => array(
                'type' => 'INTEGER',
                'constraint' => 11,
                'unsigned' => TRUE,
                'auto_increment' => TRUE
            ),
            'keyword' => array(
                'type' => 'VARCHAR',
                'constraint' => '255',
                'unique' => TRUE,
            ),
            'reply' => array(
                'type' => 'TEXT',
                'null' => FALSE,
            ),
            'created_at' => array(
                'type' => 'TIMESTAMP',
                'default' => 'CURRENT_TIMESTAMP',
            ),
        ));
        $this->dbforge->add_key('id', TRUE);
        $this->dbforge->create_table('keyword_replies');
    }

    public function down()
    {
        $this->dbforge->drop_table('keyword_replies');
    }
}
