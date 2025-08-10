<?php
defined('BASEPATH') OR exit('No direct script access allowed');

class Bot_webhook extends CI_Controller {

    public function handle($webhook_token = '') {
        try {
            if (empty($webhook_token)) {
                throw new Exception('Webhook token tidak ada.');
            }

            $this->load->model('BotModel');
            $bot = $this->BotModel->getBotByWebhookToken($webhook_token);

            if (!$bot) {
                throw new Exception('Token webhook tidak valid.');
            }

            $bot_id = $bot['id'];
            $bot_api_token = $bot['token'];

            // Muat semua komponen yang diperlukan
            $this->load->model('Settings_model');
            $this->load->model('Log_model');
            $this->load->model('UserModel');
            $this->load->model('KeywordModel');

            // --- Dynamic Handler Loading ---
            $handler_class = $bot['handler_type'] ?? 'DefaultBotHandler';
            $handler_file = FCPATH . 'bot/' . $handler_class . '.php';

            if (!file_exists($handler_file)) {
                throw new Exception("Handler file not found: {$handler_file}");
            }
            require_once $handler_file;
            if (!class_exists($handler_class)) {
                throw new Exception("Handler class not found: {$handler_class}");
            }
            // --- End Dynamic Handler Loading ---

            require_once FCPATH . 'bot/ApiClient.php';

            // Inisialisasi komponen bot dengan bot_id
            $apiClient = new ApiClient($bot_api_token, $this->Log_model, $bot_id);
            $botHandler = new $handler_class(
                $apiClient,
                $this->Log_model,
                $this->UserModel,
                $this->KeywordModel,
                $this->Settings_model,
                $bot_id
            );

            $rawUpdate = file_get_contents('php://input');

            if (!empty($rawUpdate)) {
                $botHandler->handle($rawUpdate);
            }

        } catch (Throwable $e) {
            // Catat error apapun ke log utama CI
            log_message('error', 'Kegagalan Webhook: ' . $e->getMessage());
        } finally {
            // Selalu kembalikan 200 OK ke Telegram untuk mencegah pengiriman ulang
            http_response_code(200);
        }
    }
}
