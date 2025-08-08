<?php
defined('BASEPATH') OR exit('No direct script access allowed');

class Settings extends MY_Controller {

    public function __construct()
    {
        parent::__construct();
        $this->load->model('Settings_model');
        $this->load->model('Log_model');
        $this->load->helper('url');
        $this->load->helper('form');
        require_once FCPATH . 'bot/ApiClient.php';
    }

    public function index()
    {
        if (!$this->selected_bot) {
            $this->load->view('settings_view', ['error_message' => 'Silakan pilih bot terlebih dahulu.']);
            return;
        }

        $data['bot_token'] = $this->Settings_model->get_setting('bot_token', $this->selected_bot_id);
        // Kita bisa menyimpan token di tabel bots atau di settings. Untuk sekarang, kita asumsikan di settings.
        // Sebaiknya dipindahkan ke tabel bots.
        $data['bot_token'] = $this->selected_bot['token'];

        $data['success_message'] = $this->session->flashdata('success_message');
        $data['error_message'] = $this->session->flashdata('error_message');
        $data['webhook_info'] = $this->session->flashdata('webhook_info');

        if ($this->input->post('save_settings'))
        {
            // Logika ini perlu diperbarui untuk menyimpan ke tabel bots
            // $this->Settings_model->save_setting('bot_token', $this->input->post('bot_token'), $this->selected_bot_id);
            $this->session->set_flashdata('success_message', 'Pengaturan disimpan (logika update perlu diimplementasikan).');
            redirect('settings');
        }

        $this->load->view('settings_view', $data);
    }

    private function _get_api_client(): ?ApiClient
    {
        if (!$this->selected_bot) return null;

        $token = $this->selected_bot['token'];
        if (empty($token)) {
            $this->session->set_flashdata('error_message', 'Token untuk bot yang dipilih tidak ditemukan.');
            return null;
        }
        return new ApiClient($token, $this->Log_model, $this->selected_bot_id);
    }

    public function set_webhook()
    {
        if (!$this->selected_bot || empty($this->selected_bot['webhook_token'])) {
            $this->session->set_flashdata('error_message', 'Bot belum dipilih atau tidak memiliki token webhook.');
            redirect('settings');
            return;
        }

        $webhookUrl = site_url('bot/webhook/' . $this->selected_bot['webhook_token']);

        if ($api = $this->_get_api_client()) {
            $result = $api->setWebhook($webhookUrl);
            if ($result && ($result['ok'] ?? false)) {
                $this->session->set_flashdata('success_message', 'Webhook berhasil diatur ke: ' . $webhookUrl);
            } else {
                $this->session->set_flashdata('error_message', 'Gagal mengatur webhook. Respon: ' . ($result['description'] ?? 'Error tidak diketahui.'));
            }
        }
        redirect('settings');
    }

    public function get_webhook_info()
    {
        if ($api = $this->_get_api_client()) {
            $info = $api->getWebhookInfo();
            if ($info && ($info['ok'] ?? false)) {
                $this->session->set_flashdata('webhook_info', print_r($info['result'], true));
                $this->session->set_flashdata('success_message', 'Informasi webhook berhasil diambil.');
            } else {
                $this->session->set_flashdata('error_message', 'Gagal mengambil info webhook. Respon: ' . ($info['description'] ?? 'Error tidak diketahui.'));
            }
        }
        redirect('settings');
    }

    public function delete_webhook()
    {
        if ($api = $this->_get_api_client()) {
            $result = $api->deleteWebhook();
            if ($result && ($result['ok'] ?? false)) {
                $this->session->set_flashdata('success_message', 'Webhook berhasil dihapus.');
            } else {
                $this->session->set_flashdata('error_message', 'Gagal menghapus webhook. Respon: ' . ($result['description'] ?? 'Error tidak diketahui.'));
            }
        }
        redirect('settings');
    }
}
