<?php
defined('BASEPATH') OR exit('No direct script access allowed');

class Migration_Refactor_keyword_replies_to_global extends CI_Migration {

    public function up()
    {
        $this->db->trans_start();

        // 1. Check if the bot_id column exists. If not, we don't need to do anything.
        if (!$this->db->field_exists('bot_id', 'keyword_replies'))
        {
            $this->db->trans_complete();
            log_message('info', 'Migration for keyword_replies: bot_id column does not exist, skipping.');
            return;
        }

        // 2. Rename old table
        $this->dbforge->rename_table('keyword_replies', 'keyword_replies_old');
        log_message('info', 'Renamed keyword_replies to keyword_replies_old');

        // 3. Create the new table with a unique constraint on the keyword
        $this->dbforge->add_field([
            'id' => [
                'type' => 'INT',
                'constraint' => 11,
                'unsigned' => TRUE,
                'auto_increment' => TRUE
            ],
            'keyword' => [
                'type' => 'VARCHAR',
                'constraint' => '255',
                'unique' => TRUE
            ],
            'reply' => [
                'type' => 'TEXT',
                'null' => FALSE
            ]
        ]);
        $this->dbforge->add_key('id', TRUE);
        $this->dbforge->create_table('keyword_replies');
        log_message('info', 'Created new keyword_replies table.');

        // 4. Copy distinct keywords from the old table to the new one.
        // This query will take the reply from the keyword with the lowest ID in case of duplicates.
        $query = $this->db->query("
            INSERT INTO keyword_replies (keyword, reply)
            SELECT t.keyword, t.reply
            FROM (
                SELECT
                    keyword,
                    reply,
                    ROW_NUMBER() OVER(PARTITION BY keyword ORDER BY id ASC) as rn
                FROM keyword_replies_old
            ) t
            WHERE t.rn = 1
        ");
        log_message('info', 'Copied ' . $query->affected_rows() . ' distinct keywords to the new table.');

        // 5. Drop the old table
        $this->dbforge->drop_table('keyword_replies_old');
        log_message('info', 'Dropped keyword_replies_old table.');

        $this->db->trans_complete();

        if ($this->db->trans_status() === FALSE)
        {
            log_message('error', 'Migration for keyword_replies failed.');
        }
        else
        {
            log_message('info', 'Migration for keyword_replies completed successfully.');
        }
    }

    public function down()
    {
        // Add the bot_id column back. Data will be lost, but schema is restored.
        $fields = [
            'bot_id' => [
                'type' => 'INT',
                'constraint' => 11,
                'unsigned' => TRUE,
                'null' => TRUE,
                'after' => 'id'
            ]
        ];
        // We also need to drop the unique constraint on 'keyword'
        // This is complex and depends on the DB driver.
        // For simplicity, we will assume this downgrade path is for emergencies.
        log_message('warning', "Downgrading 'keyword_replies' will re-add 'bot_id' but won't restore original data or remove unique key constraint automatically.");
        $this->dbforge->add_column('keyword_replies', $fields);
    }
}
