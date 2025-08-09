<?php
defined('BASEPATH') OR exit('No direct script access allowed');

class BotManagement extends MY_Controller {

    public function __construct() {
        parent::__construct();
        $this->load->helper('url');
        $this->load->helper('form');
        $this->load->library('form_validation');
        $this->load->model('Settings_model');
        $this->load->model('Log_model');
    }

    public function index() {
        $data['bots'] = $this->BotModel->getAllBots();
        $this->load->view('bot_management_view', $data);
    }

    public function add() {
        $this->form_validation->set_rules('name', 'Nama Bot', 'required|trim');
        $this->form_validation->set_rules('token', 'Token API Telegram', 'required|trim');

        if ($this->form_validation->run() == FALSE) {
            // Jika validasi gagal, tampilkan kembali form dengan error
            $this->index();
        } else {
            $bot_data = [
                'name' => $this->input->post('name'),
                'token' => $this->input->post('token'),
            ];
            $this->BotModel->createBot($bot_data);
            redirect('bot_management');
        }
    }

    public function edit($id) {
        $data['bot'] = $this->BotModel->getBotById($id);
        if (empty($data['bot'])) {
            show_404();
        }
        $this->load->view('bot_edit_view', $data); // We need to create this view
    }

    public function update($id) {
        $this->form_validation->set_rules('name', 'Nama Bot', 'required|trim');
        $this->form_validation->set_rules('token', 'Token API Telegram', 'required|trim');

        if ($this->form_validation->run() == FALSE) {
            $this->edit($id);
        } else {
            $bot_data = [
                'name' => $this->input->post('name'),
                'token' => $this->input->post('token'),
            ];
            $this->db->where('id', $id)->update('bots', $bot_data);
            redirect('bot_management');
        }
    }

    public function reset_cron_key($bot_id) {
        if (empty($bot_id)) {
            show_404();
        }

        // Pastikan bot ada
        $bot = $this->BotModel->getBotById($bot_id);
        if (!$bot) {
            show_404();
        }

        // Buat kunci baru dan simpan
        $new_key = bin2hex(random_bytes(16));
        $this->Settings_model->save_setting('cron_secret_key', $new_key, $bot_id);

        $this->session->set_flashdata('success', 'Cron secret key untuk bot ' . html_escape($bot['name']) . ' berhasil direset.');
        redirect('bot_management');
    }

    public function switch_bot($bot_id) {
        // Cek apakah bot dengan ID tersebut ada
        $bot = $this->BotModel->getBotById($bot_id);
        if ($bot) {
            $this->session->set_userdata('selected_bot_id', $bot_id);
        }
        // Redirect kembali ke halaman dashboard atau halaman sebelumnya
        redirect($this->input->server('HTTP_REFERER') ?: 'dashboard');
    }

    // --- API Methods for Bot Info Modal ---

    private function _get_api_client($bot_id) {
        $bot = $this->BotModel->getBotById($bot_id);
        if (!$bot) {
            return null;
        }
        return new ApiClient($bot['token'], $this->Log_model, $bot['id']);
    }

    public function info($bot_id) {
        $api = $this->_get_api_client($bot_id);
        if (!$api) {
            $this->output->set_status_header(404)->set_content_type('application/json')->set_output(json_encode(['ok' => false, 'error' => 'Bot not found']));
            return;
        }

        try {
            $getMe_result = $api->getMe();
            $getWebhookInfo_result = $api->getWebhookInfo();
            $response_data = [
                'getMe' => $getMe_result,
                'getWebhookInfo' => $getWebhookInfo_result,
            ];
            $this->output->set_content_type('application/json')->set_output(json_encode($response_data));
        } catch (Exception $e) {
            $this->output->set_status_header(500)->set_content_type('application/json')->set_output(json_encode(['ok' => false, 'error' => $e->getMessage()]));
        }
    }

    public function set_webhook($bot_id) {
        $api = $this->_get_api_client($bot_id);
        if (!$api) {
            $this->output->set_status_header(404)->set_content_type('application/json')->set_output(json_encode(['ok' => false, 'error' => 'Bot not found']));
            return;
        }

        try {
            $webhook_url = site_url('bot_webhook/handle/' . $bot_id);
            $result = $api->setWebhook($webhook_url);
            $this->output->set_content_type('application/json')->set_output(json_encode($result));
        } catch (Exception $e) {
            $this->output->set_status_header(500)->set_content_type('application/json')->set_output(json_encode(['ok' => false, 'error' => $e->getMessage()]));
        }
    }

    public function delete_webhook($bot_id) {
        $api = $this->_get_api_client($bot_id);
        if (!$api) {
            $this->output->set_status_header(404)->set_content_type('application/json')->set_output(json_encode(['ok' => false, 'error' => 'Bot not found']));
            return;
        }

        try {
            $result = $api->deleteWebhook();
            $this->output->set_content_type('application/json')->set_output(json_encode($result));
        } catch (Exception $e) {
            $this->output->set_status_header(500)->set_content_type('application/json')->set_output(json_encode(['ok' => false, 'error' => $e->getMessage()]));
        }
    }
}
