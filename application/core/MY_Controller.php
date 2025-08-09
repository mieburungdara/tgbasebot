<?php
defined('BASEPATH') OR exit('No direct script access allowed');

/**
 * MY_Controller Class
 *
 * Extends the CI_Controller class to add custom logic for all dashboard controllers.
 */
class MY_Controller extends CI_Controller {

    protected $all_bots;

    public function __construct() {
        parent::__construct();

        $this->load->helper('url');
        $this->load->library('session'); // Tetap berguna untuk flash messages
        $this->load->model('BotModel');

        // Muat semua bot agar tersedia untuk filter/dropdown di view
        $this->all_bots = $this->BotModel->getAllBots();

        // Redirect untuk menambah bot jika belum ada
        if (empty($this->all_bots) && strtolower($this->uri->segment(1)) !== 'botmanagement' && strtolower($this->uri->segment(1)) !== 'bot_management') {
            redirect('bot_management');
        }

        // Jadikan daftar bot tersedia secara global untuk semua view
        $this->load->vars([
            'all_bots' => $this->all_bots,
        ]);
    }
}
