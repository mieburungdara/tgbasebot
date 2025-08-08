<?php
defined('BASEPATH') OR exit('No direct script access allowed');

class Cron extends CI_Controller {

    public function __construct() {
        parent::__construct();
        $this->load->model('Settings_model');
    }

    /**
     * Menjalankan skrip cron job siaran dengan validasi token.
     */
    public function run() {
        $cron_secret = $this->Settings_model->get_setting('cron_secret_key');
        $token = $this->input->get('token');

        // Validasi token untuk keamanan
        if (empty($cron_secret) || empty($token) || $token !== $cron_secret) {
            show_error('Akses Ditolak: Token tidak valid atau tidak ada.', 403);
            return;
        }

        // Eksekusi skrip cron
        echo "<pre>";
        echo "Memulai eksekusi cron job siaran...\n\n";

        // Menggunakan include untuk menjalankan skrip dalam konteks ini.
        // Ini memungkinkan outputnya ditampilkan jika diakses melalui browser.
        include(FCPATH . 'cron/process_broadcasts.php');

        echo "\nEksekusi cron job selesai.</pre>";
    }
}
