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
        // Catat setiap pembaruan mentah yang masuk
        $this->logger->add_log('incoming', $rawUpdate);

        $update = json_decode($rawUpdate, true);

        if (!$update) {
            $this->logger->add_log('error', 'Pembaruan masuk tidak valid atau bukan JSON.');
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
