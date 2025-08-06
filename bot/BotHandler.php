<?php

/**
 * Class BotHandler
 * Memproses logika bisnis dari bot.
 */
class BotHandler
{
    protected ApiClient $api;
    protected Log_model $logger;

    /**
     * BotHandler constructor.
     * @param ApiClient $api Klien API untuk mengirim balasan.
     * @param Log_model $logger Instance dari model log CodeIgniter.
     */
    public function __construct(ApiClient $api, Log_model $logger)
    {
        $this->api = $api;
        $this->logger = $logger;
    }

    /**
     * Menangani pembaruan mentah dari webhook.
     * @param string $rawUpdate String JSON dari pembaruan Telegram.
     */
    public function handle(string $rawUpdate): void
    {
        $update = json_decode($rawUpdate, true);

        // Ekstrak info obrolan jika memungkinkan
        $chatId = $update['message']['chat']['id'] ?? null;
        $chatName = null;
        if (isset($update['message']['chat'])) {
            $chat = $update['message']['chat'];
            if (isset($chat['title'])) {
                $chatName = $chat['title']; // Untuk grup
            } else {
                $chatName = trim(($chat['first_name'] ?? '') . ' ' . ($chat['last_name'] ?? ''));
            }
        }

        // Catat setiap pembaruan mentah yang masuk dengan info obrolan
        $this->logger->add_log('incoming', $rawUpdate, $chatId, $chatName);

        if (!$update) {
            // Jika pembaruan tidak valid, log kesalahan (info obrolan akan null)
            $this->logger->add_log('error', 'Pembaruan masuk tidak valid atau bukan JSON.');
            return;
        }

        if (isset($update['message'])) {
            $message = $update['message'];
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
