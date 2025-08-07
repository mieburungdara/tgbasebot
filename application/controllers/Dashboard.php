<?php
defined('BASEPATH') OR exit('No direct script access allowed');

class Dashboard extends CI_Controller {

    public function __construct()
    {
        parent::__construct();
        $this->load->model('Log_model');
        $this->load->model('KeywordModel');
        $this->load->model('UserModel');
        $this->load->library('pagination');
        $this->load->helper('url');
        $this->load->helper('form');
    }

    public function index()
    {
        // Ambil filter dari input GET
        $filters = [
            'log_type'  => $this->input->get('log_type'),
            'chat_id'   => $this->input->get('chat_id'),
            'chat_name' => $this->input->get('chat_name'),
            'keyword'   => $this->input->get('keyword')
        ];
        // Hapus filter kosong untuk URL yang lebih bersih
        $filters = array_filter($filters);

        // Konfigurasi Paginasi
        $config['base_url'] = site_url('dashboard/index');
        $config['total_rows'] = $this->Log_model->count_logs($filters);
        $config['per_page'] = 25;
        $config['page_query_string'] = TRUE;
        $config['query_string_segment'] = 'page';

        // Tambahkan filter ke URL paginasi
        if (!empty($filters)) {
            $config['suffix'] = '&' . http_build_query($filters, '', "&");
            $config['first_url'] = $config['base_url'] . '?' . http_build_query($filters, '', "&");
        }

        $this->pagination->initialize($config);

        // Tentukan offset
        $page = $this->input->get('page') ? (int)$this->input->get('page') : 0;
        $offset = $page;

        // Ambil data untuk tampilan
        $data['logs'] = $this->Log_model->get_logs($filters, $config['per_page'], $offset);
        $data['stats'] = $this->Log_model->get_stats();
        $data['pagination_links'] = $this->pagination->create_links();
        $data['filters'] = $filters;
        $data['log_types'] = ['incoming', 'outgoing', 'error']; // Untuk dropdown filter

        // Ambil data untuk grafik
        $chart_data = $this->Log_model->get_daily_log_counts(14); // Ambil data 14 hari
        $data['chart_labels'] = json_encode(array_keys($chart_data));
        $data['chart_values'] = json_encode(array_values($chart_data));

        $this->load->view('log_view', $data);
    }

    /**
     * Menghapus satu entri log.
     *
     * @param int $id ID log.
     */
    public function delete($id)
    {
        if ($id) {
            $this->Log_model->delete_log($id);
        }
        redirect('dashboard');
    }

    /**
     * Menghapus semua entri log.
     */
    public function clear_logs()
    {
        $this->Log_model->clear_all_logs();
        redirect('dashboard');
    }

    /**
     * Menampilkan halaman manajemen kata kunci.
     */
    public function keywords()
    {
        $data['keywords'] = $this->KeywordModel->getKeywords();
        // Nanti kita akan membuat view ini
        $this->load->view('keywords_view', $data);
    }

    /**
     * Menambahkan kata kunci baru.
     */
    public function add_keyword()
    {
        $this->load->library('form_validation');
        $this->form_validation->set_rules('keyword', 'Keyword', 'required|trim');
        $this->form_validation->set_rules('reply', 'Reply', 'required|trim');

        if ($this->form_validation->run() == FALSE) {
            // Gagal validasi, muat ulang tampilan dengan error
            $this->keywords();
        } else {
            $data = [
                'keyword' => $this->input->post('keyword'),
                'reply' => $this->input->post('reply')
            ];
            $this->KeywordModel->addKeyword($data);
            redirect('dashboard/keywords');
        }
    }

    /**
     * Menghapus kata kunci.
     * @param int $id ID kata kunci.
     */
    public function delete_keyword($id)
    {
        if ($id) {
            $this->KeywordModel->deleteKeyword($id);
        }
        redirect('dashboard/keywords');
    }

    /**
     * Menampilkan halaman siaran.
     */
    public function broadcast()
    {
        // Nanti kita akan membuat view ini
        $this->load->view('broadcast_view');
    }

    /**
     * Mengirim pesan siaran.
     */
    public function send_broadcast()
    {
        $message = $this->input->post('message');

        if (empty($message)) {
            // TODO: Tambahkan pesan error flashdata
            redirect('dashboard/broadcast');
            return;
        }

        // Muat komponen bot
        require_once FCPATH . 'bot/ApiClient.php';
        $this->load->model('Settings_model');
        $botToken = $this->Settings_model->get_setting('bot_token');

        if (empty($botToken)) {
            // TODO: Tambahkan pesan error flashdata
            redirect('dashboard/broadcast');
            return;
        }

        $apiClient = new ApiClient($botToken, $this->Log_model);
        $users = $this->UserModel->getAllUsers();

        foreach ($users as $user) {
            try {
                $apiClient->sendMessage($user['chat_id'], $message);
                // Tambahkan jeda kecil untuk menghindari pembatasan laju (rate-limiting)
                usleep(100000); // 100ms
            } catch (Exception $e) {
                $this->Log_model->add_log('error', "Siaran gagal untuk chat_id: {$user['chat_id']}. Error: " . $e->getMessage());
            }
        }

        // TODO: Tambahkan pesan sukses flashdata
        redirect('dashboard/broadcast');
    }
}
