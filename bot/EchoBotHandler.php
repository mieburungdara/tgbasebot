<?php

require_once __DIR__ . '/HandlerInterface.php';

class EchoBotHandler implements HandlerInterface
{
    protected ApiClient $api;
    protected Log_model $logger;
    protected int $bot_id;

    // We keep the constructor signature compatible for simplicity,
    // even though this handler doesn't use all the models.
    public function __construct(ApiClient $api, Log_model $logger, UserModel $userModel, KeywordModel $keywordModel, Settings_model $settingsModel, int $bot_id)
    {
        $this->api = $api;
        $this->logger = $logger;
        $this->bot_id = $bot_id;
    }

    public function handle(string $rawUpdate): void
    {
        $update = json_decode($rawUpdate, true);

        if (!isset($update['message'])) {
            return; // Not a message, ignore
        }

        $message = $update['message'];
        $chatId = $message['chat']['id'];
        $text = $message['text'] ?? '';

        // Log the incoming message
        $chatName = $message['chat']['first_name'] ?? 'Unknown';
        $this->logger->add_log('incoming', $rawUpdate, $chatId, $chatName, $this->bot_id);

        if (!empty($text)) {
            // Echo the exact same text back to the user
            $this->api->sendMessage($chatId, $text);
        } else {
            // Handle cases where the message is not text (e.g., a photo or sticker)
            $this->api->sendMessage($chatId, "Saya hanya bisa menggemakan pesan teks.");
        }
    }
}
