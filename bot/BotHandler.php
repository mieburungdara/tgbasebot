<?php

/**
 * Class BotHandler
 * Memproses logika bisnis dari bot.
 */
class BotHandler
{
    protected ApiClient $api;

    /**
     * BotHandler constructor.
     * @param ApiClient $api Klien API untuk mengirim balasan.
     */
    public function __construct(ApiClient $api)
    {
        $this->api = $api;
    }

    /**
     * Menangani pembaruan mentah dari webhook.
     * @param string $rawUpdate String JSON dari pembaruan Telegram.
     */
    public function handle(string $rawUpdate): void
    {
        $update = json_decode($rawUpdate, true);

        if (!$update) {
            // Abaikan jika data tidak valid
            return;
        }

        if (isset($update['message'])) {
            $message = $update['message'];
            $chatId = $message['chat']['id'];
            $text = $message['text'] ?? ''; // Gunakan null coalescing untuk menghindari error jika tidak ada teks

            if ($text === '/start') {
                $responseText = 'Halo! Selamat datang di bot modular PHP. Proyek ini siap untuk diunggah ke shared hosting.';
                $this->api->sendMessage($chatId, $responseText);
            } else {
                // Untuk semua pesan lain, bot akan membalasnya (echo)
                $responseText = 'Anda mengirim: ' . $text;
                $this->api->sendMessage($chatId, $responseText);
            }
        }
    }
}
