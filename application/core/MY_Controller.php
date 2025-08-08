<?php
defined('BASEPATH') OR exit('No direct script access allowed');

/**
 * MY_Controller Class
 *
 * Extends the CI_Controller class to add custom logic for all dashboard controllers.
 * It handles bot selection logic via sessions.
 */
class MY_Controller extends CI_Controller {

    protected $selected_bot_id;
    protected $selected_bot;
    protected $all_bots;

    public function __construct() {
        parent::__construct();

        $this->load->library('session');
        $this->load->model('BotModel');

        // Ambil semua bot untuk switcher
        $this->all_bots = $this->BotModel->getAllBots();

        // Jika tidak ada bot sama sekali, paksa redirect ke halaman manajemen bot
        // kecuali jika kita sudah berada di sana.
        if (empty($this->all_bots) && strtolower($this->uri->segment(1)) !== 'botmanagement') {
            redirect('bot_management');
        }

        // Tentukan bot yang dipilih dari sesi
        $this->selected_bot_id = $this->session->userdata('selected_bot_id');

        // Jika tidak ada bot yang dipilih di sesi (atau ID tidak valid), default ke bot pertama
        $bot_id_is_valid = false;
        if ($this->selected_bot_id) {
            foreach ($this->all_bots as $bot) {
                if ($bot['id'] == $this->selected_bot_id) {
                    $bot_id_is_valid = true;
                    break;
                }
            }
        }

        if (!$bot_id_is_valid && !empty($this->all_bots)) {
            $this->selected_bot_id = $this->all_bots[0]['id'];
            $this->session->set_userdata('selected_bot_id', $this->selected_bot_id);
        }

        // Dapatkan data lengkap untuk bot yang dipilih
        $this->selected_bot = null;
        if ($this->selected_bot_id) {
            foreach ($this->all_bots as $bot) {
                if ($bot['id'] == $this->selected_bot_id) {
                    $this->selected_bot = $bot;
                    break;
                }
            }
        }

        // Jadikan data bot tersedia untuk semua view yang dimuat oleh controller turunan
        $this->load->vars([
            'all_bots' => $this->all_bots,
            'selected_bot_id' => $this->selected_bot_id,
            'selected_bot' => $this->selected_bot
        ]);
    }
}
