<?php
defined('BASEPATH') OR exit('No direct script access allowed');

class Migration_Remove_bot_id_from_keyword_replies_table extends CI_Migration {

    public function up()
    {
        if ($this->db->field_exists('bot_id', 'keyword_replies'))
        {
            // $this->dbforge->drop_column('keyword_replies', 'bot_id');
            // Dropping columns is not supported by the SQLite3 driver.
            // This migration is being bypassed to prevent errors. The column will remain in the database,
            // but it is unused by the application.
        }
    }

    public function down()
    {
        $fields = array(
            'bot_id' => array(
                'type' => 'INT',
                'constraint' => 11,
                'unsigned' => TRUE,
                'null' => TRUE
            )
        );
        // Hanya tambahkan jika kolom tidak ada, untuk keamanan
        if (!$this->db->field_exists('bot_id', 'keyword_replies'))
        {
            $this->dbforge->add_column('keyword_replies', $fields);
        }
    }
}
