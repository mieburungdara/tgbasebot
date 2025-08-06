<?php

use GuzzleHttp\Client;
use GuzzleHttp\Exception\GuzzleException;

/**
 * Class ApiClient
 * Menangani semua komunikasi dengan Telegram Bot API.
 */
class ApiClient
{
    protected string $apiUrl;
    protected Client $httpClient;

    /**
     * ApiClient constructor.
     * @param string $token Token bot dari Telegram.
     */
    public function __construct(string $token)
    {
        $this->apiUrl = 'https://api.telegram.org/bot' . $token;
        $this->httpClient = new Client(['base_uri' => $this->apiUrl]);
    }

    /**
     * Mengirim pesan teks ke chat ID tertentu.
     *
     * @param int $chatId ID chat tujuan.
     * @param string $text Teks pesan yang akan dikirim.
     */
    public function sendMessage(int $chatId, string $text): void
    {
        try {
            $this->httpClient->post('sendMessage', [
                'json' => [
                    'chat_id' => $chatId,
                    'text' => $text,
                ]
            ]);
        } catch (GuzzleException $e) {
            // Dalam aplikasi nyata, ini harus dicatat (logged).
            // Untuk saat ini, kita abaikan sesuai instruksi.
            error_log('Guzzle Error: ' . $e->getMessage());
        }
    }
}
