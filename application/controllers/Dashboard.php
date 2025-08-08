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
        $this->load->model('Settings_model');
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
        $chart_days = $this->input->get('days') ? (int)$this->input->get('days') : 14;
        $chart_days = in_array($chart_days, [7, 14, 30]) ? $chart_days : 14; // Validasi
        $chart_data = $this->Log_model->get_daily_log_counts($chart_days);
        $data['chart_labels'] = json_encode(array_keys($chart_data));
        $data['chart_values'] = json_encode(array_values($chart_data));
        $data['chart_days'] = $chart_days; // Kirim ke view untuk menandai filter aktif

        // Siapkan data untuk Donut Chart
        $donut_labels = [];
        $donut_values = [];
        if (!empty($data['stats']['logs_by_type'])) {
            foreach($data['stats']['logs_by_type'] as $type_stat) {
                $donut_labels[] = ucfirst($type_stat['log_type']);
                $donut_values[] = $type_stat['count'];
            }
        }
        $data['donut_labels'] = json_encode($donut_labels);
        $data['donut_values'] = json_encode($donut_values);

        // Ambil data statistik pengguna
        $data['user_stats'] = $this->UserModel->getUserStats();

        // Ambil data kesehatan bot
        $data['health_stats'] = [
            'last_cron_run' => $this->Settings_model->get_setting('last_cron_run'),
            'last_incoming_message' => $this->Settings_model->get_setting('last_incoming_message')
        ];

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
        $data['cron_secret_key'] = $this->Settings_model->get_setting('cron_secret_key');

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

    /**
     * Mengekspor log yang difilter sebagai file CSV.
     */
    public function export_csv()
    {
        $this->load->helper('download');

        // Ambil filter yang sama dengan halaman utama
        $filters = [
            'log_type'  => $this->input->get('log_type'),
            'chat_id'   => $this->input->get('chat_id'),
            'chat_name' => $this->input->get('chat_name'),
            'keyword'   => $this->input->get('keyword')
        ];
        $filters = array_filter($filters);

        // Ambil SEMUA log yang cocok, bukan hanya halaman saat ini
        $logs = $this->Log_model->get_logs($filters, 0, 0); // Limit 0 untuk mengambil semua

        if (empty($logs)) {
            // Redirect kembali jika tidak ada log untuk diekspor
            redirect('dashboard');
            return;
        }

        $filename = 'bot_logs_' . date('Ymd_His') . '.csv';
        $delimiter = ",";
        $newline = "\r\n";
        $data = '';

        // Header CSV
        $data .= '"ID","Timestamp","Log Type","Chat ID","Chat Name","Message"'.$newline;

        // Baris data
        foreach ($logs as $log) {
            $line = '"' . $log['id'] . '"' . $delimiter;
            $line .= '"' . $log['created_at'] . '"' . $delimiter;
            $line .= '"' . $log['log_type'] . '"' . $delimiter;
            $line .= '"' . $log['chat_id'] . '"' . $delimiter;
            $line .= '"' . str_replace('"', '""', $log['chat_name']) . '"' . $delimiter;

            $message = preg_replace('/\s+/', ' ', $log['log_message']);
            $message = str_replace('"', '""', $message);

            $line .= '"' . $message . '"' . $newline;
            $data .= $line;
        }

        force_download($filename, $data);
    }

    /**
     * Menghasilkan CRON_SECRET_KEY baru dan menyimpannya di database.
     */
    public function reset_cron_key()
    {
        $new_key = bin2hex(random_bytes(16)); // 32 karakter hex
        $this->Settings_model->save_setting('cron_secret_key', $new_key);
        // TODO: Tambahkan flash message sukses
        redirect('dashboard/broadcast');
    }
}
