<?php
// Aktifkan pelaporan error untuk debugging
error_reporting(E_ALL);
ini_set('display_errors', 1);

// Definisikan path absolut yang kokoh untuk menghindari masalah lingkungan
$root_path = realpath(__DIR__ . '/..');

define('FCPATH', $root_path . '/');
define('BASEPATH', FCPATH . 'system/');
define('APPPATH', FCPATH . 'application/');
define('ENVIRONMENT', $_ENV['CI_ENV'] ?? 'production');

// Ubah direktori kerja ke direktori root proyek
chdir(FCPATH);

// Muat autoloader Composer
require_once FCPATH . 'vendor/autoload.php';

// Muat bootstrap CodeIgniter
require_once BASEPATH . 'core/CodeIgniter.php';

/*
 * --------------------------------------------------------------------
 * PROSES PERMINTAAN BOT
 * --------------------------------------------------------------------
 */

try {
    // Dapatkan instance CodeIgniter
    $CI =& get_instance();

    // Muat model yang diperlukan
    $CI->load->model('Settings_model');
    $CI->load->model('Log_model');

    // Muat file-file bot yang modular
    require_once FCPATH . 'bot/ApiClient.php';
    require_once FCPATH . 'bot/BotHandler.php';

    // Ambil token bot dari database
    $botToken = $CI->Settings_model->get_setting('bot_token');
    if (empty($botToken)) {
        throw new Exception('Token bot tidak ditemukan di database pengaturan.');
    }

    // Inisialisasi komponen bot dengan model log
    $apiClient = new ApiClient($botToken, $CI->Log_model);
    $botHandler = new BotHandler($apiClient, $CI->Log_model);

    // Ambil pembaruan mentah dari input stream
    $rawUpdate = file_get_contents('php://input');

    // Pastikan ada pembaruan sebelum diproses
    if (!empty($rawUpdate)) {
        $botHandler->handle($rawUpdate);
    }

} catch (Throwable $e) { // Tangkap Throwable untuk error fatal di PHP 7+
    // Jika terjadi error, catat pesannya menggunakan Log_model
    // Lakukan pemeriksaan manual untuk model log jika CI instance gagal
    if (class_exists('CI_Controller', false)) {
        $CI =& get_instance();
        if (isset($CI->Log_model)) {
            $CI->Log_model->add_log('error', 'Bot Gagal: ' . $e->getMessage() . ' di ' . $e->getFile() . ' baris ' . $e->getLine());
        } else {
            // Fallback jika Log_model tidak dapat dimuat
            error_log('Bot Gagal: ' . $e->getMessage());
        }
    } else {
        error_log('Bot Gagal (CI tidak terinisialisasi): ' . $e->getMessage());
    }
}

// Selalu kembalikan respon HTTP 200 OK ke Telegram untuk mencegah pengiriman ulang.
http_response_code(200);
