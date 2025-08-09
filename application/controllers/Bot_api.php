<?php
defined('BASEPATH') OR exit('No direct script access allowed');

class Bot_api extends MY_Controller {

    public function __construct() {
        parent::__construct();
        $this->load->model('BotModel');
        $this->load->helper('url');
    }

    private function _send_telegram_request($token, $method, $params = []) {
        try {
            $client = new \GuzzleHttp\Client([
                'base_uri' => "https://api.telegram.org/bot{$token}/",
                'timeout'  => 10.0,
                'verify' => false, // Bypass SSL verification on some servers
            ]);

            $response = $client->request('GET', $method, [
                'query' => $params
            ]);

            return json_decode($response->getBody()->getContents(), true);

        } catch (\GuzzleHttp\Exception\RequestException $e) {
            if ($e->hasResponse()) {
                return json_decode($e->getResponse()->getBody()->getContents(), true);
            }
            return ['ok' => false, 'error_code' => $e->getCode(), 'description' => $e->getMessage()];
        }
    }

    public function info($bot_id) {
        $bot = $this->BotModel->getBotById($bot_id);
        if (!$bot) {
            $this->output->set_status_header(404)->set_content_type('application/json')->set_output(json_encode(['ok' => false, 'error' => 'Bot not found']));
            return;
        }

        $token = $bot['token'];

        $getMe_result = $this->_send_telegram_request($token, 'getMe');
        $getWebhookInfo_result = $this->_send_telegram_request($token, 'getWebhookInfo');

        $response_data = [
            'getMe' => $getMe_result,
            'getWebhookInfo' => $getWebhookInfo_result,
        ];

        $this->output->set_content_type('application/json')->set_output(json_encode($response_data));
    }

    public function set_webhook($bot_id) {
        $bot = $this->BotModel->getBotById($bot_id);
        if (!$bot) {
            $this->output->set_status_header(404)->set_content_type('application/json')->set_output(json_encode(['ok' => false, 'error' => 'Bot not found']));
            return;
        }

        $token = $bot['token'];
        $webhook_url = site_url('bot_webhook/handle/' . $bot['id']);

        $result = $this->_send_telegram_request($token, 'setWebhook', ['url' => $webhook_url]);

        $this->output->set_content_type('application/json')->set_output(json_encode($result));
    }

    public function delete_webhook($bot_id) {
        $bot = $this->BotModel->getBotById($bot_id);
        if (!$bot) {
            $this->output->set_status_header(404)->set_content_type('application/json')->set_output(json_encode(['ok' => false, 'error' => 'Bot not found']));
            return;
        }

        $token = $bot['token'];
        $result = $this->_send_telegram_request($token, 'deleteWebhook');

        $this->output->set_content_type('application/json')->set_output(json_encode($result));
    }
}
