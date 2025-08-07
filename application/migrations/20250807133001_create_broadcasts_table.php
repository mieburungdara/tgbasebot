<?php
defined('BASEPATH') OR exit('No direct script access allowed');

class Migration_Create_broadcasts_table extends CI_Migration {

    public function up()
    {
        $this->dbforge->add_field(array(
            'id' => array(
                'type' => 'INT',
                'constraint' => 11,
                'unsigned' => TRUE,
                'auto_increment' => TRUE
            ),
            'message' => array(
                'type' => 'TEXT',
                'null' => FALSE
            ),
            'status' => array(
                'type' => "ENUM('pending', 'processing', 'completed', 'failed')",
                'default' => 'pending',
                'null' => FALSE
            ),
            'total_recipients' => array(
                'type' => 'INT',
                'constraint' => 11,
                'unsigned' => TRUE,
                'default' => 0
            ),
            'sent_count' => array(
                'type' => 'INT',
                'constraint' => 11,
                'unsigned' => TRUE,
                'default' => 0
            ),
            'failed_count' => array(
                'type' => 'INT',
                'constraint' => 11,
                'unsigned' => TRUE,
                'default' => 0
            ),
            'created_at' => array(
                'type' => 'TIMESTAMP',
                'default' => 'CURRENT_TIMESTAMP'
            ),
            'processing_started_at' => array(
                'type' => 'DATETIME',
                'null' => TRUE
            ),
            'completed_at' => array(
                'type' => 'DATETIME',
                'null' => TRUE
            ),
        ));
        $this->dbforge->add_key('id', TRUE);
        $this->dbforge->create_table('broadcasts');
        $this->db->query('CREATE INDEX status_index ON broadcasts(status)');
    }

    public function down()
    {
        $this->dbforge->drop_table('broadcasts');
    }
}
