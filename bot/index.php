<?php

// Atur zona waktu default untuk menghindari potensi error tanggal/waktu.
date_default_timezone_set('Asia/Jakarta');

// Aktifkan logging error ke file, penting untuk debugging di shared hosting.
ini_set('log_errors', 1);
ini_set('error_log', __DIR__ . '/error.log');
ini_set('display_errors', 0); // Jangan tampilkan error ke pengguna

// Muat autoloader Composer dari direktori root.
require_once __DIR__ . '/../vendor/autoload.php';

// Muat file-file bot yang modular
require_once __DIR__ . '/ApiClient.php';
require_once __DIR__ . '/BotHandler.php';

try {
    // Muat variabel lingkungan dari file .env di direktori root
    $dotenv = Dotenv\Dotenv::createImmutable(__DIR__ . '/..');
    $dotenv->load();

    // Ambil token bot dari environment variable
    $botToken = $_ENV['TELEGRAM_BOT_TOKEN'] ?? null;
    if (empty($botToken)) {
        throw new Exception('TELEGRAM_BOT_TOKEN tidak diatur di file .env.');
    }

    // Inisialisasi komponen-komponen bot
    $apiClient = new ApiClient($botToken);
    $botHandler = new BotHandler($apiClient);

    // Ambil pembaruan mentah dari input stream (data yang dikirim Telegram)
    $update = file_get_contents('php://input');

    // Pastikan ada pembaruan sebelum diproses
    if ($update) {
        $botHandler->handle($update);
    }

} catch (Exception $e) {
    // Jika terjadi error, catat pesannya ke file log.
    error_log('Error: ' . $e->getMessage());
}

// Selalu kembalikan respon HTTP 200 OK ke Telegram.
// Ini memberitahu Telegram bahwa webhook telah menerima pembaruan dengan sukses.
// Jika tidak, Telegram akan mencoba mengirim pembaruan yang sama berulang kali.
http_response_code(200);
