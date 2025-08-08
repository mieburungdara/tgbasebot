<?php
defined('BASEPATH') OR exit('No direct script access allowed');

class Migration_Create_bots_table extends CI_Migration {

    public function up()
    {
        if (!$this->db->table_exists('bots'))
        {
            $this->dbforge->add_field(array(
                'id' => array(
                    'type' => 'INT',
                    'constraint' => 11,
                    'unsigned' => TRUE,
                    'auto_increment' => TRUE
                ),
                'name' => array(
                    'type' => 'VARCHAR',
                    'constraint' => '255',
                    'null' => FALSE
                ),
                'token' => array(
                    'type' => 'TEXT',
                    'null' => FALSE,
                ),
                'webhook_token' => array(
                    'type' => 'VARCHAR',
                    'constraint' => 100,
                    'null' => TRUE, // Akan diisi saat bot dibuat
                    'unique' => TRUE
                ),
                'created_at' => array(
                    'type' => 'TIMESTAMP',
                    'default' => 'CURRENT_TIMESTAMP',
                ),
            ));
            $this->dbforge->add_key('id', TRUE);
            $this->dbforge->create_table('bots');
        }
    }

    public function down()
    {
        $this->dbforge->drop_table('bots');
    }
}
