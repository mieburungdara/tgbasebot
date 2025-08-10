<?php

require_once __DIR__ . '/HandlerInterface.php';

class DefaultBotHandler implements HandlerInterface
{
    protected ApiClient $api;
    protected Log_model $logger;
    protected UserModel $userModel;
    protected KeywordModel $keywordModel;
    protected Settings_model $settingsModel;
    protected int $bot_id;

    public function __construct(ApiClient $api, Log_model $logger, UserModel $userModel, KeywordModel $keywordModel, Settings_model $settingsModel, int $bot_id)
    {
        $this->api = $api;
        $this->logger = $logger;
        $this->userModel = $userModel;
        $this->keywordModel = $keywordModel;
        $this->settingsModel = $settingsModel;
        $this->bot_id = $bot_id;
    }

    public function handle(string $rawUpdate): void
    {
        // Perbarui timestamp untuk pemeriksaan kesehatan bot
        $this->settingsModel->save_setting('last_incoming_message', date('Y-m-d H:i:s'), $this->bot_id);

        $update = json_decode($rawUpdate, true);

        if (!$update || !isset($update['message'])) {
            $this->logger->add_log('error', 'Pembaruan masuk tidak valid atau tidak berisi pesan.', null, null, $this->bot_id);
            return;
        }

        $message = $update['message'];
        $chat = $message['chat'];
        $text = trim($message['text'] ?? '');

        // --- 1. Save or Update User ---
        $this->saveUser($chat);

        // Ekstrak info obrolan untuk logging
        $chatId = $chat['id'];
        $chatName = $this->getChatName($chat);

        $this->logger->add_log('incoming', $rawUpdate, $chatId, $chatName, $this->bot_id);

        // --- 2. Handle Commands ---
        if (strpos($text, '/') === 0) {
            $this->handleCommand($chatId, $text);
            return;
        }

        // --- 3. Handle Keyword Replies ---
        if (!empty($text)) {
            $reply = $this->keywordModel->getReply($text, $this->bot_id);
            if ($reply) {
                $this->api->sendMessage($chatId, $reply);
                return;
            }
        }

        // --- 4. Fallback to Echo (if text is not empty) ---
        if (!empty($text)) {
            $responseText = 'Anda mengirim: ' . $text;
            $this->api->sendMessage($chatId, $responseText);
        }
    }

    private function saveUser(array $chat): void
    {
        // Hanya simpan untuk obrolan pribadi, bukan grup
        if ($chat['type'] === 'private') {
            $userData = [
                'bot_id'     => $this->bot_id,
                'chat_id'    => $chat['id'],
                'first_name' => $chat['first_name'] ?? null,
                'last_name'  => $chat['last_name'] ?? null,
                'username'   => $chat['username'] ?? null
            ];
            $this->userModel->addUser($userData);
        }
    }

    private function getChatName(array $chat): string
    {
        if (isset($chat['title'])) {
            return $chat['title']; // Group name
        }
        return trim(($chat['first_name'] ?? '') . ' ' . ($chat['last_name'] ?? ''));
    }

    private function handleCommand(int $chatId, string $text): void
    {
        $parts = explode(' ', $text);
        $command = strtolower($parts[0]);

        switch ($command) {
            case '/start':
                $responseText = "Halo! Selamat datang di bot PHP modular.\n\nKetik /help untuk melihat daftar perintah.";
                break;

            case '/help':
                $responseText = "Bantuan Bot:\n\n/start - Memulai bot.\n/help - Menampilkan pesan bantuan ini.\n/subscribe - Mengaktifkan kembali langganan siaran.\n/unsubscribe - Berhenti berlangganan dari semua siaran.";
                break;

            case '/subscribe':
                $this->userModel->setUserStatus($chatId, 'active', $this->bot_id);
                $responseText = "Terima kasih! Anda telah kembali berlangganan dan akan menerima siaran berikutnya.";
                break;

            case '/unsubscribe':
                $this->userModel->setUserStatus($chatId, 'unsubscribed', $this->bot_id);
                $responseText = "Anda telah berhasil berhenti berlangganan dari pesan siaran. Anda tidak akan menerima siaran lagi kecuali Anda mengetik /subscribe.";
                break;

            default:
                $responseText = "Maaf, saya tidak mengerti perintah itu. Ketik /help untuk daftar perintah yang tersedia.";
                break;
        }
        $this->api->sendMessage($chatId, $responseText);
    }
}
