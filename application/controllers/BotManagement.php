<?php
defined('BASEPATH') OR exit('No direct script access allowed');

class BotManagement extends CI_Controller {

    public function __construct() {
        parent::__construct();
        $this->load->model('BotModel');
        $this->load->helper('url');
        $this->load->helper('form');
        $this->load->library('form_validation');
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
}
