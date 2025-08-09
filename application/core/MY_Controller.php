<?php
defined('BASEPATH') OR exit('No direct script access allowed');

/**
 * MY_Controller Class
 *
 * Extends the CI_Controller class to add custom logic for all dashboard controllers.
 */
class MY_Controller extends CI_Controller {

    protected $all_bots;
    protected $selected_bot_id;
    protected $selected_bot;

    public function __construct() {
        parent::__construct();

        $this->load->helper('url');
        $this->load->library('session');
        $this->load->model('BotModel');

        // Muat semua bot agar tersedia untuk filter/dropdown di view
        $this->all_bots = $this->BotModel->getAllBots();

        // Redirect untuk menambah bot jika belum ada
        if (empty($this->all_bots) && strtolower($this->uri->segment(1)) !== 'botmanagement' && strtolower($this->uri->segment(1)) !== 'bot_management') {
            redirect('bot_management');
        }

        // Logika untuk menentukan bot yang dipilih
        $this->selected_bot_id = $this->session->userdata('selected_bot_id');

        // Jika tidak ada bot yang dipilih di session DAN ada bot tersedia, gunakan bot pertama sebagai default
        if (!$this->selected_bot_id && !empty($this->all_bots)) {
            $this->selected_bot_id = $this->all_bots[0]['id'];
            $this->session->set_userdata('selected_bot_id', $this->selected_bot_id);
        }

        // Muat data bot yang dipilih
        if ($this->selected_bot_id) {
            $this->selected_bot = $this->BotModel->getBotById($this->selected_bot_id);
        } else {
            $this->selected_bot = null;
        }

        // Jadikan variabel global untuk semua view
        $this->load->vars([
            'all_bots' => $this->all_bots,
            'selected_bot_id' => $this->selected_bot_id,
            'selected_bot' => $this->selected_bot
        ]);
    }
}
