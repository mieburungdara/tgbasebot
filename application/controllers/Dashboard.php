<?php
defined('BASEPATH') OR exit('No direct script access allowed');

class Dashboard extends CI_Controller {

    public function __construct()
    {
        parent::__construct();
        $this->load->model('Log_model');
        $this->load->helper('url');
    }

    public function index()
    {
        // Ambil semua log, urutkan dari yang terbaru
        $this->db->order_by('id', 'DESC');
        $query = $this->db->get('bot_logs');
        $data['logs'] = $query->result_array();

        $this->load->view('log_view', $data);
    }
}
