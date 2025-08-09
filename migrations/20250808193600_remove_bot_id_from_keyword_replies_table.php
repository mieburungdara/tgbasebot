<?php
defined('BASEPATH') OR exit('No direct script access allowed');

class Migration_Remove_bot_id_from_keyword_replies_table extends CI_Migration {

    public function up()
    {
        // This is a dummy migration file to bypass a persistent error.
        // The actual logic is in Migration_Refactor_keyword_replies_to_global.
        log_message('info', 'Skipping dummy migration for 20250808193600.');
    }

    public function down()
    {
        // This is a dummy migration file. No action needed.
        log_message('info', 'Skipping dummy migration for 20250808193600.');
    }
}
