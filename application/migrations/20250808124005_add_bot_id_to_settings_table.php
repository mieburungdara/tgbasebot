<?php
defined('BASEPATH') OR exit('No direct script access allowed');

class Migration_Add_bot_id_to_settings_table extends CI_Migration {

    public function up()
    {
        if (!$this->db->field_exists('bot_id', 'settings'))
        {
            $fields = array(
                'bot_id' => array(
                    'type' => 'INT',
                    'constraint' => 11,
                    'unsigned' => TRUE,
                    'null' => TRUE
                )
            );
            $this->dbforge->add_column('settings', $fields);

            // Menambahkan composite unique index untuk memastikan key unik per bot
            $this->db->query('ALTER TABLE settings ADD CONSTRAINT bot_id_key_unique UNIQUE (bot_id, `key`)');
        }
    }

    public function down()
    {
        $this->dbforge->drop_column('settings', 'bot_id');
    }
}
