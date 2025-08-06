<?php
defined('BASEPATH') OR exit('No direct script access allowed');

class Settings extends CI_Controller {

    public function __construct()
    {
        parent::__construct();
        $this->load->model('Settings_model');
        $this->load->model('Log_model'); // Load Log_model
        $this->load->helper('url');
        $this->load->helper('form');
        $this->load->library('session');
        require_once dirname(APPPATH) . '/bot/ApiClient.php'; // Correct path to ApiClient
    }

    public function index()
    {
        $data['bot_token'] = $this->Settings_model->get_setting('bot_token');
        $data['success_message'] = $this->session->flashdata('success_message');
        $data['error_message'] = $this->session->flashdata('error_message');
        $data['webhook_info'] = $this->session->flashdata('webhook_info'); // Get webhook info from flashdata

        if ($this->input->post('save_token'))
        {
            $token = $this->input->post('bot_token');
            if ($this->Settings_model->save_setting('bot_token', $token))
            {
                $this->session->set_flashdata('success_message', 'Token bot berhasil disimpan.');
            }
            else
            {
                $this->session->set_flashdata('error_message', 'Gagal menyimpan token bot.');
            }
            redirect('settings');
        }

        $this->load->view('settings_view', $data);
    }

    private function _get_api_client(): ?ApiClient
    {
        $token = $this->Settings_model->get_setting('bot_token');
        if (empty($token)) {
            $this->session->set_flashdata('error_message', 'Token bot belum diatur. Silakan simpan token terlebih dahulu.');
            return null;
        }
        return new ApiClient($token, $this->Log_model);
    }

    public function set_webhook()
    {
        $token = $this->Settings_model->get_setting('bot_token');
        if (empty($token)) {
            $this->session->set_flashdata('error_message', 'Token bot belum diatur. Silakan simpan token terlebih dahulu.');
            redirect('settings');
            return;
        }

        // Gunakan base_url untuk mendapatkan path yang benar tanpa index.php tambahan
        $webhookUrl = base_url('bot/index.php');

        // Pastikan URL menggunakan HTTPS
        if (strpos($webhookUrl, 'http://') === 0) {
            $webhookUrl = 'https' . substr($webhookUrl, 4);
        }

        if ($api = $this->_get_api_client()) {
            $result = $api->setWebhook($webhookUrl);
            if ($result && ($result['ok'] ?? false)) {
                $this->session->set_flashdata('success_message', 'Webhook berhasil diatur ke: ' . $webhookUrl);
            } else {
                $this->session->set_flashdata('error_message', 'Gagal mengatur webhook. Respon: ' . ($result['description'] ?? 'Tidak ada atau error.'));
            }
        }

        redirect('settings');
    }

    public function get_webhook_info()
    {
        if ($api = $this->_get_api_client()) {
            $info = $api->getWebhookInfo();
            if ($info && ($info['ok'] ?? false)) {
                $this->session->set_flashdata('webhook_info', $info['result']);
                $this->session->set_flashdata('success_message', 'Informasi webhook berhasil diambil.');
            } else {
                $this->session->set_flashdata('error_message', 'Gagal mengambil info webhook. Respon: ' . ($info['description'] ?? 'Tidak ada atau error.'));
            }
        }
        redirect('settings');
    }

    public function delete_webhook()
    {
        if ($api = $this->_get_api_client()) {
            $result = $api->deleteWebhook();
            if ($result && ($result['ok'] ?? false)) {
                $this->session->set_flashdata('success_message', 'Webhook berhasil dihapus. ' . ($result['description'] ?? ''));
            } else {
                $this->session->set_flashdata('error_message', 'Gagal menghapus webhook. Respon: ' . ($result['description'] ?? 'Tidak ada atau error.'));
            }
        }
        redirect('settings');
    }
}
