<?php
defined('BASEPATH') OR exit('No direct script access allowed');

class Cron extends CI_Controller {

    public function __construct() {
        parent::__construct();
        $this->load->model('Settings_model');
    }

    /**
     * Menjalankan skrip cron job siaran untuk bot tertentu dengan validasi token.
     */
    public function run($bot_id = 0) {
        $bot_id = (int)$bot_id;
        if (empty($bot_id)) {
            show_error('Bot ID tidak valid.', 400);
            return;
        }

        $cron_secret = $this->Settings_model->get_setting('cron_secret_key', $bot_id);
        $token = $this->input->get('token');

        // Validasi token untuk keamanan
        if (empty($cron_secret) || empty($token) || !hash_equals($cron_secret, $token)) {
            show_error('Akses Ditolak: Token tidak valid atau tidak ada.', 403);
            return;
        }

        // Eksekusi skrip cron
        echo "<pre>";
        echo "Memulai eksekusi cron job siaran untuk Bot ID: {$bot_id}...\n\n";

        // Teruskan bot_id ke skrip yang di-include
        $target_bot_id = $bot_id;

        // Menggunakan include untuk menjalankan skrip dalam konteks ini.
        include(FCPATH . 'cron/process_broadcasts.php');

        echo "\nEksekusi cron job selesai.</pre>";
    }
}
