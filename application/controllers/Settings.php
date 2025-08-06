<?php
defined('BASEPATH') OR exit('No direct script access allowed');

class Settings extends CI_Controller {

    public function __construct()
    {
        parent::__construct();
        $this->load->model('Settings_model');
        $this->load->helper('url');
        $this->load->helper('form');
        $this->load->library('session'); // Untuk menampilkan pesan feedback
    }

    public function index()
    {
        $data['bot_token'] = $this->Settings_model->get_setting('bot_token');
        $data['success_message'] = $this->session->flashdata('success_message');
        $data['error_message'] = $this->session->flashdata('error_message');

        // Cek jika form disubmit
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

    public function set_webhook()
    {
        $token = $this->Settings_model->get_setting('bot_token');

        if (empty($token)) {
            $this->session->set_flashdata('error_message', 'Token bot belum diatur. Silakan simpan token terlebih dahulu.');
            redirect('settings');
            return;
        }

        $webhookUrl = site_url('bot/index.php', 'https'); // Gunakan https untuk webhook

        try {
            $client = new \GuzzleHttp\Client();
            $response = $client->post('https://api.telegram.org/bot' . $token . '/setWebhook', [
                'form_params' => [
                    'url' => $webhookUrl
                ]
            ]);

            $body = json_decode((string) $response->getBody(), true);

            if ($response->getStatusCode() == 200 && $body['ok'] === true) {
                $this->session->set_flashdata('success_message', 'Webhook berhasil diatur ke: ' . $webhookUrl);
            } else {
                $this->session->set_flashdata('error_message', 'Gagal mengatur webhook. Respon Telegram: ' . ($body['description'] ?? 'Format tidak dikenal.'));
            }
        } catch (\GuzzleHttp\Exception\GuzzleException $e) {
            $this->session->set_flashdata('error_message', 'Terjadi error saat menghubungi API Telegram: ' . $e->getMessage());
        }

        redirect('settings');
    }
}
