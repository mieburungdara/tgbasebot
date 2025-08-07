<?php
defined('BASEPATH') OR exit('No direct script access allowed');

class Dashboard extends CI_Controller {

    public function __construct()
    {
        parent::__construct();
        $this->load->model('Log_model');
        $this->load->model('KeywordModel');
        $this->load->model('UserModel');
        $this->load->model('BroadcastModel');
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

        // Ambil data statistik pengguna
        $data['user_stats'] = $this->UserModel->getUserStats();

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
     * Menampilkan halaman siaran dengan riwayat dan paginasi.
     */
    public function broadcast()
    {
        $filters = ['status' => $this->input->get('status')];
        $filters = array_filter($filters);

        // Konfigurasi Paginasi untuk riwayat siaran
        $config['base_url'] = site_url('dashboard/broadcast');
        $config['total_rows'] = $this->BroadcastModel->count_all_broadcasts($filters);
        $config['per_page'] = 15;
        $config['page_query_string'] = TRUE;
        $config['query_string_segment'] = 'page';
        $config['reuse_query_string'] = TRUE;
        $config['full_tag_open'] = '<nav><ul class="pagination justify-content-center">';
        $config['full_tag_close'] = '</ul></nav>';
        $config['first_link'] = 'Awal';
        $config['last_link'] = 'Akhir';
        $config['next_link'] = '&raquo;';
        $config['prev_link'] = '&laquo;';
        $config['num_tag_open'] = '<li class="page-item"><span class="page-link">';
        $config['num_tag_close'] = '</span></li>';
        $config['cur_tag_open'] = '<li class="page-item active"><span class="page-link">';
        $config['cur_tag_close'] = '</span></li>';
        $config['next_tag_open'] = '<li class="page-item"><span class="page-link">';
        $config['next_tag_close'] = '</span></li>';
        $config['prev_tag_open'] = '<li class="page-item"><span class="page-link">';
        $config['prev_tag_close'] = '</span></li>';

        if (!empty($filters)) {
            $config['suffix'] = '&' . http_build_query($filters, '', "&");
            $config['first_url'] = $config['base_url'] . '?' . http_build_query($filters, '', "&");
        }

        $this->pagination->initialize($config);

        $page = $this->input->get('page') ? (int)$this->input->get('page') : 0;
        $offset = $page;

        $data['broadcasts'] = $this->BroadcastModel->get_all_broadcasts($filters, $config['per_page'], $offset);
        $data['pagination_links'] = $this->pagination->create_links();
        $data['user_stats'] = $this->UserModel->getUserStats();
        $data['tags'] = $this->UserModel->getAllTags();
        $data['filters'] = $filters;

        $this->load->view('broadcast_view', $data);
    }

    /**
     * Menambahkan siaran baru ke dalam antrean dengan opsi penargetan.
     */
    public function send_broadcast()
    {
        $this->load->library('form_validation');
        $this->form_validation->set_rules('message', 'Message', 'required');

        if ($this->form_validation->run() == FALSE) {
            return $this->broadcast();
        }

        $is_test_send = !empty($this->input->post('test_send'));
        $target_tag = $this->input->post('target_tag');
        if (empty($target_tag) || $target_tag === 'all') {
            $target_tag = NULL;
        }

        // Hitung jumlah penerima berdasarkan target
        $recipient_count = $this->UserModel->countActiveUsers($target_tag, $is_test_send);

        if ($recipient_count > 0) {
            $broadcast_data = [
                'message' => $this->input->post('message'),
                'total_recipients' => $recipient_count,
                'target_tag' => $target_tag,
                'is_test_broadcast' => $is_test_send ? 1 : 0,
            ];
            $this->BroadcastModel->create_broadcast($broadcast_data);
        }

        redirect('dashboard/broadcast');
    }

    /**
     * Menghapus catatan siaran.
     * @param int $id ID siaran.
     */
    public function delete_broadcast($id)
    {
        if ($id) {
            $this->BroadcastModel->delete_broadcast($id);
        }
        redirect('dashboard/broadcast');
    }
}
