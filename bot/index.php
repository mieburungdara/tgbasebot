<?php
// Definisikan path dasar CodeIgniter
define('BASEPATH', __DIR__ . '/../system/');
define('APPPATH', __DIR__ . '/../application/');
define('ENVIRONMENT', $_ENV['CI_ENV'] ?? 'production');

// Ubah direktori kerja ke direktori root proyek
chdir(__DIR__ . '/..');

// Muat autoloader Composer
require_once 'vendor/autoload.php';

// Muat bootstrap CodeIgniter
require_once BASEPATH . 'core/CodeIgniter.php';

/*
 * --------------------------------------------------------------------
 * PROSES PERMINTAAN BOT
 * --------------------------------------------------------------------
 */

// Dapatkan instance CodeIgniter
$CI =& get_instance();

// Muat model yang diperlukan
$CI->load->model('Settings_model');
$CI->load->model('Log_model');

// Muat file-file bot yang modular
require_once APPPATH . '../bot/ApiClient.php';
require_once APPPATH . '../bot/BotHandler.php';

try {
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

} catch (Exception $e) {
    // Jika terjadi error, catat pesannya menggunakan Log_model
    if (isset($CI->Log_model)) {
        $CI->Log_model->add_log('error', 'Bot Gagal: ' . $e->getMessage());
    }
}

// Selalu kembalikan respon HTTP 200 OK ke Telegram.
http_response_code(200);
