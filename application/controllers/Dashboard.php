<?php
defined('BASEPATH') OR exit('No direct script access allowed');

class Dashboard extends CI_Controller {

    public function __construct()
    {
        parent::__construct();
        $this->load->model('Log_model');
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
}
