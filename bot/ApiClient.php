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
    protected Log_model $logger;

    /**
     * ApiClient constructor.
     * @param string $token Token bot dari Telegram.
     * @param Log_model $logger Instance dari model log CodeIgniter.
     */
    public function __construct(string $token, Log_model $logger)
    {
        $this->apiUrl = 'https://api.telegram.org/bot' . $token;
        $this->httpClient = new Client([
            'base_uri' => $this->apiUrl,
            'verify' => false, // Nonaktifkan verifikasi SSL
        ]);
        $this->logger = $logger;
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

            // Catat pesan keluar yang berhasil
            $this->logger->add_log('outgoing', "To: $chatId | Message: $text");

        } catch (GuzzleException $e) {
            // Catat error ke database jika pengiriman gagal
            $this->logger->add_log('error', 'Guzzle Error: ' . $e->getMessage());
        }
    }

    /**
     * Mendapatkan informasi webhook bot saat ini.
     *
     * @return array|null Informasi webhook atau null jika terjadi error.
     */
    public function getWebhookInfo(): ?array
    {
        try {
            $response = $this->httpClient->get('getWebhookInfo');
            $body = json_decode((string) $response->getBody(), true);

            if ($response->getStatusCode() == 200 && ($body['ok'] ?? false)) {
                return $body; // Return the full response
            }

            $this->logger->add_log('error', 'Failed to get webhook info: ' . ($body['description'] ?? 'Unknown error'));
            return null;

        } catch (GuzzleException $e) {
            $this->logger->add_log('error', 'Guzzle Error (getWebhookInfo): ' . $e->getMessage());
            return null;
        }
    }

    /**
     * Menghapus webhook bot.
     *
     * @return array|null Hasil dari operasi atau null jika terjadi error.
     */
    public function deleteWebhook(): ?array
    {
        try {
            $response = $this->httpClient->get('deleteWebhook');
            $body = json_decode((string) $response->getBody(), true);

            if ($response->getStatusCode() == 200 && ($body['ok'] ?? false)) {
                return $body;
            }

            $this->logger->add_log('error', 'Failed to delete webhook: ' . ($body['description'] ?? 'Unknown error'));
            return null;

        } catch (GuzzleException $e) {
            $this->logger->add_log('error', 'Guzzle Error (deleteWebhook): ' . $e->getMessage());
            return null;
        }
    }
}
