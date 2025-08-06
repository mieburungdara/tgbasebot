<?php

/**
 * Class ApiClient
 * Menangani semua komunikasi dengan Telegram Bot API menggunakan cURL.
 */
class ApiClient
{
    protected string $apiUrl;
    protected Log_model $logger;

    /**
     * ApiClient constructor.
     * @param string $token Token bot dari Telegram.
     * @param Log_model $logger Instance dari model log CodeIgniter.
     */
    public function __construct(string $token, Log_model $logger)
    {
        $this->apiUrl = 'https://api.telegram.org/bot' . $token;
        $this->logger = $logger;
    }

    /**
     * Menjalankan permintaan cURL ke API Telegram.
     *
     * @param string $method Metode API yang akan dipanggil.
     * @param array $data Data yang akan dikirim (untuk permintaan POST).
     * @return array|null Respon yang didekode dari API atau null jika terjadi error.
     */
    private function _execute_curl(string $method, array $data = []): ?array
    {
        $ch = curl_init($this->apiUrl . '/' . $method);

        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_HEADER, false);
        curl_setopt($ch, CURLOPT_TIMEOUT, 30);

        // Selalu gunakan POST seperti permintaan sebelumnya
        curl_setopt($ch, CURLOPT_POST, true);
        curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($data));

        // Atur header untuk mengirim JSON
        curl_setopt($ch, CURLOPT_HTTPHEADER, ['Content-Type: application/json']);

        // Nonaktifkan verifikasi SSL (seperti pada konfigurasi Guzzle sebelumnya)
        curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
        curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, false);

        $response_body = curl_exec($ch);
        $error = curl_error($ch);

        curl_close($ch);

        if ($error) {
            $this->logger->add_log('error', 'cURL Error (' . $method . '): ' . $error);
            return null;
        }

        $response = json_decode($response_body, true);
        if (json_last_error() !== JSON_ERROR_NONE) {
            $this->logger->add_log('error', 'JSON Decode Error: ' . json_last_error_msg());
            $this->logger->add_log('error', 'Raw Response: ' . $response_body);
            return null;
        }

        return $response;
    }

    /**
     * Mengirim pesan teks ke chat ID tertentu.
     *
     * @param int $chatId ID chat tujuan.
     * @param string $text Teks pesan yang akan dikirim.
     */
    public function sendMessage(int $chatId, string $text): void
    {
        $this->_execute_curl('sendMessage', ['chat_id' => $chatId, 'text' => $text]);
        $this->logger->add_log('outgoing', "To: $chatId | Message: $text");
    }

    /**
     * Mendapatkan informasi webhook bot saat ini.
     *
     * @return array|null Informasi webhook atau null jika terjadi error.
     */
    public function getWebhookInfo(): ?array
    {
        $response = $this->_execute_curl('getWebhookInfo');

        if ($response && ($response['ok'] ?? false)) {
            return $response;
        }

        $description = $response['description'] ?? 'Tidak ada deskripsi.';
        $this->logger->add_log('error', 'Gagal mendapatkan info webhook via cURL: ' . $description);

        return $response; // Kembalikan respon apa pun agar controller bisa menampilkannya
    }

    /**
     * Menghapus webhook bot.
     *
     * @return array|null Hasil dari operasi atau null jika terjadi error.
     */
    public function deleteWebhook(): ?array
    {
        $response = $this->_execute_curl('deleteWebhook');

        if ($response && ($response['ok'] ?? false)) {
            return $response;
        }

        $description = $response['description'] ?? 'Tidak ada deskripsi.';
        $this->logger->add_log('error', 'Gagal menghapus webhook via cURL: ' . $description);

        return $response;
    }

    /**
     * Mengatur URL webhook bot.
     *
     * @param string $url URL webhook yang akan diatur.
     * @return array|null Hasil dari operasi atau null jika terjadi error.
     */
    public function setWebhook(string $url): ?array
    {
        $response = $this->_execute_curl('setWebhook', ['url' => $url]);

        if ($response && ($response['ok'] ?? false)) {
            return $response;
        }

        $description = $response['description'] ?? 'Tidak ada deskripsi.';
        $this->logger->add_log('error', 'Gagal mengatur webhook via cURL: ' . $description);

        return $response;
    }
}
