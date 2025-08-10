<?php
defined('BASEPATH') OR exit('No direct script access allowed');

class Settings extends MY_Controller {

    public function __construct()
    {
        parent::__construct();
        $this->load->model('Settings_model');
        $this->load->model('Log_model');
        $this->load->helper('url');
        $this->load->helper('form');
        require_once FCPATH . 'bot/ApiClient.php';
    }

    public function index()
    {
        if (!$this->selected_bot) {
            $this->load->view('settings_view', ['error_message' => 'Silakan pilih bot terlebih dahulu.']);
            return;
        }

        $data['bot_token'] = $this->Settings_model->get_setting('bot_token', $this->selected_bot_id);
        // Kita bisa menyimpan token di tabel bots atau di settings. Untuk sekarang, kita asumsikan di settings.
        // Sebaiknya dipindahkan ke tabel bots.
        $data['bot_token'] = $this->selected_bot['token'];

        $data['success_message'] = $this->session->flashdata('success_message');
        $data['error_message'] = $this->session->flashdata('error_message');
        $data['webhook_info'] = $this->session->flashdata('webhook_info');

        if ($this->input->post('save_settings'))
        {
            // Logika ini perlu diperbarui untuk menyimpan ke tabel bots
            // $this->Settings_model->save_setting('bot_token', $this->input->post('bot_token'), $this->selected_bot_id);
            $this->session->set_flashdata('success_message', 'Pengaturan disimpan (logika update perlu diimplementasikan).');
            redirect('settings');
        }

        $this->load->view('settings_view', $data);
    }

    private function _get_api_client(): ?ApiClient
    {
        if (!$this->selected_bot) return null;

        $token = $this->selected_bot['token'];
        if (empty($token)) {
            $this->session->set_flashdata('error_message', 'Token untuk bot yang dipilih tidak ditemukan.');
            return null;
        }
        return new ApiClient($token, $this->Log_model, $this->selected_bot_id);
    }

    public function set_webhook()
    {
        if (!$this->selected_bot || empty($this->selected_bot['webhook_token'])) {
            $this->session->set_flashdata('error_message', 'Bot belum dipilih atau tidak memiliki token webhook.');
            redirect('settings');
            return;
        }

        $webhookUrl = site_url('bot/webhook/' . $this->selected_bot['webhook_token']);

        if ($api = $this->_get_api_client()) {
            $result = $api->setWebhook($webhookUrl);
            if ($result && ($result['ok'] ?? false)) {
                $this->session->set_flashdata('success_message', 'Webhook berhasil diatur ke: ' . $webhookUrl);
            } else {
                $this->session->set_flashdata('error_message', 'Gagal mengatur webhook. Respon: ' . ($result['description'] ?? 'Error tidak diketahui.'));
            }
        }
        redirect('settings');
    }

    public function get_webhook_info()
    {
        if ($api = $this->_get_api_client()) {
            $info = $api->getWebhookInfo();
            if ($info && ($info['ok'] ?? false)) {
                $this->session->set_flashdata('webhook_info', print_r($info['result'], true));
                $this->session->set_flashdata('success_message', 'Informasi webhook berhasil diambil.');
            } else {
                $this->session->set_flashdata('error_message', 'Gagal mengambil info webhook. Respon: ' . ($info['description'] ?? 'Error tidak diketahui.'));
            }
        }
        redirect('settings');
    }

    public function delete_webhook()
    {
        if ($api = $this->_get_api_client()) {
            $result = $api->deleteWebhook();
            if ($result && ($result['ok'] ?? false)) {
                $this->session->set_flashdata('success_message', 'Webhook berhasil dihapus.');
            } else {
                $this->session->set_flashdata('error_message', 'Gagal menghapus webhook. Respon: ' . ($result['description'] ?? 'Error tidak diketahui.'));
            }
        }
        redirect('settings');
    }

    public function forge_database()
    {
        if (ENVIRONMENT === 'production') {
            $this->session->set_flashdata('error_message', 'Operasi reset database tidak diizinkan di lingkungan produksi.');
            redirect('settings');
            return;
        }

        try {
            $this->load->dbforge();

            // Disable foreign key checks
            if ($this->db->dbdriver === 'sqlite3') {
                $this->db->query('PRAGMA foreign_keys = OFF;');
            } else {
                $this->db->query('SET FOREIGN_KEY_CHECKS = 0');
            }

            // Drop all tables except migrations
            $tables = $this->db->list_tables();
            foreach ($tables as $table) {
                if ($table !== 'migrations') {
                    $this->dbforge->drop_table($table, TRUE, TRUE);
                }
            }

            // Recreate tables
            $this->_create_bots_table();
            $this->_create_bot_logs_table();
            $this->_create_settings_table();
            $this->_create_users_table();
            $this->_create_keyword_replies_table();
            $this->_create_broadcasts_table();

            // Re-enable foreign key checks
            if ($this->db->dbdriver === 'sqlite3') {
                $this->db->query('PRAGMA foreign_keys = ON;');
            } else {
                $this->db->query('SET FOREIGN_KEY_CHECKS = 1');
            }

            // Update migration version to the latest to prevent re-running
            if ($this->db->table_exists('migrations')) {
                $this->db->update('migrations', ['version' => '20250808193600']);
            }

            $this->session->set_flashdata('success_message', 'Database berhasil di-forge. Semua tabel telah dibuat ulang ke keadaan awal.');
        } catch (Exception $e) {
            $this->session->set_flashdata('error_message', 'Terjadi kesalahan saat forge database: ' . $e->getMessage());
        }

        redirect('settings');
    }

    private function _create_bots_table()
    {
        $this->dbforge->add_field([
            'id' => ['type' => 'INT', 'constraint' => 11, 'unsigned' => TRUE, 'auto_increment' => TRUE],
            'name' => ['type' => 'VARCHAR', 'constraint' => '255'],
            'token' => ['type' => 'TEXT'],
            'webhook_token' => ['type' => 'VARCHAR', 'constraint' => 100, 'null' => TRUE, 'unique' => TRUE],
            'created_at' => ['type' => 'TIMESTAMP', 'default' => 'CURRENT_TIMESTAMP'],
        ]);
        $this->dbforge->add_key('id', TRUE);
        $this->dbforge->create_table('bots');
    }

    private function _create_bot_logs_table()
    {
        $this->dbforge->add_field([
            'id' => ['type' => 'INT', 'constraint' => 11, 'unsigned' => TRUE, 'auto_increment' => TRUE],
            'bot_id' => ['type' => 'INT', 'constraint' => 11, 'unsigned' => TRUE],
            'update_id' => ['type' => 'VARCHAR', 'constraint' => '255', 'null' => TRUE],
            'message_data' => ['type' => 'TEXT', 'null' => TRUE],
            'chat_id' => ['type' => 'VARCHAR', 'constraint' => '255', 'null' => TRUE],
            'chat_type' => ['type' => 'VARCHAR', 'constraint' => '255', 'null' => TRUE],
            'created_at' => ['type' => 'TIMESTAMP', 'default' => 'CURRENT_TIMESTAMP'],
        ]);
        $this->dbforge->add_key('id', TRUE);
        $this->dbforge->create_table('bot_logs');
    }

    private function _create_settings_table()
    {
        $this->dbforge->add_field([
            'id' => ['type' => 'INT', 'constraint' => 11, 'unsigned' => TRUE, 'auto_increment' => TRUE],
            'bot_id' => ['type' => 'INT', 'constraint' => 11, 'unsigned' => TRUE, 'null' => TRUE],
            'key' => ['type' => 'VARCHAR', 'constraint' => '255'],
            'value' => ['type' => 'TEXT', 'null' => TRUE],
        ]);
        $this->dbforge->add_key('id', TRUE);
        $this->dbforge->create_table('settings');
        $this->db->query('CREATE UNIQUE INDEX key_bot_id_unique ON settings (key, bot_id)');
    }

    private function _create_users_table()
    {
        $this->dbforge->add_field([
            'id' => ['type' => 'INT', 'constraint' => 11, 'unsigned' => TRUE, 'auto_increment' => TRUE],
            'bot_id' => ['type' => 'INT', 'constraint' => 11, 'unsigned' => TRUE],
            'telegram_id' => ['type' => 'BIGINT'],
            'first_name' => ['type' => 'VARCHAR', 'constraint' => '255'],
            'last_name' => ['type' => 'VARCHAR', 'constraint' => '255', 'null' => TRUE],
            'username' => ['type' => 'VARCHAR', 'constraint' => '255', 'null' => TRUE],
            'language_code' => ['type' => 'VARCHAR', 'constraint' => '10', 'null' => TRUE],
            'is_bot' => ['type' => 'BOOLEAN', 'default' => FALSE],
            'status' => ['type' => 'VARCHAR', 'constraint' => '50', 'default' => 'active'],
            'segment' => ['type' => 'VARCHAR', 'constraint' => '255', 'null' => TRUE],
            'created_at' => ['type' => 'TIMESTAMP', 'default' => 'CURRENT_TIMESTAMP'],
            'updated_at' => ['type' => 'TIMESTAMP', 'default' => 'CURRENT_TIMESTAMP'],
        ]);
        $this->dbforge->add_key('id', TRUE);
        $this->dbforge->create_table('users');
        $this->db->query('CREATE UNIQUE INDEX telegram_bot_id_unique ON users (telegram_id, bot_id)');
    }

    private function _create_keyword_replies_table()
    {
        $this->dbforge->add_field([
            'id' => ['type' => 'INT', 'constraint' => 11, 'unsigned' => TRUE, 'auto_increment' => TRUE],
            'keyword' => ['type' => 'VARCHAR', 'constraint' => '255'],
            'reply_message' => ['type' => 'TEXT'],
            'is_global' => ['type' => 'BOOLEAN', 'default' => FALSE],
            'bot_id' => ['type' => 'INT', 'constraint' => 11, 'unsigned' => TRUE, 'null' => TRUE],
            'created_at' => ['type' => 'TIMESTAMP', 'default' => 'CURRENT_TIMESTAMP'],
        ]);
        $this->dbforge->add_key('id', TRUE);
        $this->dbforge->create_table('keyword_replies');
    }

    private function _create_broadcasts_table()
    {
        $this->dbforge->add_field([
            'id' => ['type' => 'INT', 'constraint' => 11, 'unsigned' => TRUE, 'auto_increment' => TRUE],
            'bot_id' => ['type' => 'INT', 'constraint' => 11, 'unsigned' => TRUE],
            'message' => ['type' => 'TEXT'],
            'target_segment' => ['type' => 'VARCHAR', 'constraint' => '255', 'null' => TRUE],
            'status' => ['type' => 'VARCHAR', 'constraint' => '50', 'default' => 'pending'],
            'total_recipients' => ['type' => 'INT', 'null' => TRUE],
            'sent_count' => ['type' => 'INT', 'default' => 0],
            'failed_count' => ['type' => 'INT', 'default' => 0],
            'error_message' => ['type' => 'TEXT', 'null' => TRUE],
            'created_at' => ['type' => 'TIMESTAMP', 'default' => 'CURRENT_TIMESTAMP'],
            'scheduled_at' => ['type' => 'DATETIME', 'null' => TRUE],
            'completed_at' => ['type' => 'DATETIME', 'null' => TRUE],
        ]);
        $this->dbforge->add_key('id', TRUE);
        $this->dbforge->create_table('broadcasts');
    }
}
