<?php

/**
 * Class ApiClient
 * Menangani semua komunikasi dengan Telegram Bot API menggunakan cURL.
 */
class ApiClient
{
    protected string $apiUrl;
    protected Log_model $logger;
    protected ?int $bot_id;

    /**
     * ApiClient constructor.
     */
    public function __construct(string $token, Log_model $logger, ?int $bot_id = null)
    {
        $this->apiUrl = 'https://api.telegram.org/bot' . $token;
        $this->logger = $logger;
        $this->bot_id = $bot_id;
    }

    /**
     * Menjalankan permintaan cURL ke API Telegram.
     */
    private function _execute_curl(string $method, array $data = []): ?array
    {
        $ch = curl_init($this->apiUrl . '/' . $method);

        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_HEADER, false);
        curl_setopt($ch, CURLOPT_TIMEOUT, 30);
        curl_setopt($ch, CURLOPT_POST, true);
        curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($data));
        curl_setopt($ch, CURLOPT_HTTPHEADER, ['Content-Type: application/json']);
        curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
        curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, false);

        $response_body = curl_exec($ch);
        $error = curl_error($ch);

        curl_close($ch);

        if ($error) {
            $this->logger->add_log('error', 'cURL Error (' . $method . '): ' . $error, null, null, $this->bot_id);
            throw new Exception('cURL Error: ' . $error);
        }

        $response = json_decode($response_body, true);
        if (json_last_error() !== JSON_ERROR_NONE) {
            $this->logger->add_log('error', 'JSON Decode Error: ' . json_last_error_msg(), null, null, $this->bot_id);
            $this->logger->add_log('error', 'Raw Response: ' . $response_body, null, null, $this->bot_id);
            throw new Exception('JSON Decode Error: ' . json_last_error_msg());
        }

        if (isset($response['ok']) && !$response['ok']) {
            throw new Exception('Telegram API Error: ' . ($response['description'] ?? 'Unknown error'));
        }

        return $response;
    }

    /**
     * Mengirim pesan teks ke chat ID tertentu.
     */
    public function sendMessage(int $chatId, string $text): void
    {
        $this->_execute_curl('sendMessage', ['chat_id' => $chatId, 'text' => $text]);
        $this->logger->add_log('outgoing', "To: $chatId | Message: $text", $chatId, null, $this->bot_id);
    }

    /**
     * Mendapatkan informasi dasar tentang bot.
     */
    public function getMe(): ?array
    {
        return $this->_execute_curl('getMe');
    }

    /**
     * Mendapatkan informasi webhook bot saat ini.
     */
    public function getWebhookInfo(): ?array
    {
        return $this->_execute_curl('getWebhookInfo');
    }

    /**
     * Menghapus webhook bot.
     */
    public function deleteWebhook(): ?array
    {
        return $this->_execute_curl('deleteWebhook');
    }

    /**
     * Mengatur URL webhook bot.
     */
    public function setWebhook(string $url): ?array
    {
        return $this->_execute_curl('setWebhook', ['url' => $url]);
    }
}
