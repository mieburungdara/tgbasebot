<?php
defined('BASEPATH') OR exit('No direct script access allowed');

class BotManagement extends MY_Controller {

    public function __construct() {
        parent::__construct();
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
}
