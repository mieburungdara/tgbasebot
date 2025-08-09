<?php
defined('BASEPATH') OR exit('No direct script access allowed');

class Dashboard extends MY_Controller {

    public function __construct()
    {
        parent::__construct();
        $this->load->model('Log_model');
        $this->load->model('KeywordModel');
        $this->load->model('UserModel');
        $this->load->model('BroadcastModel');
        $this->load->model('Settings_model');
        $this->load->model('BotModel'); // Load BotModel
        $this->load->library('pagination');
        $this->load->library('form_validation');
        $this->load->helper('url');
        $this->load->helper('form');
    }

    public function index()
    {
        // Gunakan bot yang dipilih dari sesi sebagai default
        $bot_id_to_use = $this->input->get('bot_id') ?: $this->selected_bot_id;

        // Ambil filter dari URL
        $filters = [
            'bot_id'    => $bot_id_to_use, // Gunakan bot_id yang sudah ditentukan
            'log_type'  => $this->input->get('log_type'),
            'chat_id'   => $this->input->get('chat_id'),
            'chat_name' => $this->input->get('chat_name'),
            'keyword'   => $this->input->get('keyword')
        ];
        $filters = array_filter($filters, function($value) { return $value !== null && $value !== ''; });

        // Ambil detail bot yang dipilih untuk ditampilkan di header
        $data['selected_bot'] = $this->BotModel->getBotById($bot_id_to_use);


        // Konfigurasi Paginasi
        $config['base_url'] = site_url('dashboard/index');
        $config['total_rows'] = $this->Log_model->count_logs($filters);
        $config['per_page'] = 25;
        $config['page_query_string'] = TRUE;
        $config['query_string_segment'] = 'page';
        if (!empty($filters)) {
            $config['suffix'] = '&' . http_build_query($filters, '', "&");
            $config['first_url'] = $config['base_url'] . '?' . http_build_query($filters, '', "&");
        }
        $this->pagination->initialize($config);
        $page = $this->input->get('page') ? (int)$this->input->get('page') : 0;

        // Ambil data untuk tampilan utama
        $data['logs'] = $this->Log_model->get_logs($filters, $config['per_page'], $page);
        $data['stats'] = $this->Log_model->get_stats($filters['bot_id'] ?? null);
        $data['pagination_links'] = $this->pagination->create_links();
        $data['filters'] = $filters;
        $data['log_types'] = ['incoming', 'outgoing', 'error'];

        // Ambil data untuk grafik
        $chart_days = $this->input->get('days') ? (int)$this->input->get('days') : 14;
        $chart_days = in_array($chart_days, [7, 14, 30]) ? $chart_days : 14;
        $chart_data = $this->Log_model->get_daily_log_counts($filters['bot_id'] ?? null, $chart_days);
        $data['chart_labels'] = json_encode(array_keys($chart_data));
        $data['chart_values'] = json_encode(array_values($chart_data));
        $data['chart_days'] = $chart_days;

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
        $data['user_stats'] = $this->UserModel->getUserStats($filters['bot_id'] ?? null);

        // Ambil data kesehatan bot (hanya tampilkan jika satu bot difilter)
        $data['health_stats'] = [
            'last_cron_run' => !empty($filters['bot_id']) ? $this->Settings_model->get_setting('last_cron_run', $filters['bot_id']) : null,
            'last_incoming_message' => !empty($filters['bot_id']) ? $this->Settings_model->get_setting('last_incoming_message', $filters['bot_id']) : null
        ];

        $this->load->view('log_view', $data);
    }

    public function delete($id)
    {
        if ($id) $this->Log_model->delete_log($id, $this->selected_bot_id);
        redirect('dashboard');
    }

    public function clear_logs()
    {
        $this->Log_model->clear_all_logs($this->selected_bot_id);
        redirect('dashboard');
    }

    public function keywords()
    {
        $data['keywords'] = $this->KeywordModel->getKeywords();
        $this->load->view('keywords_view', $data);
    }

    public function add_keyword()
    {
        $this->form_validation->set_rules('keyword', 'Keyword', 'required|trim');
        $this->form_validation->set_rules('reply', 'Reply', 'required|trim');
        if ($this->form_validation->run() == FALSE) {
            $this->keywords();
        } else {
            $data = ['keyword' => $this->input->post('keyword'), 'reply' => $this->input->post('reply')];
            $this->KeywordModel->addKeyword($data);
            redirect('dashboard/keywords');
        }
    }

    public function delete_keyword($id)
    {
        if ($id) $this->KeywordModel->deleteKeyword($id);
        redirect('dashboard/keywords');
    }

    public function send_broadcast()
    {
        $this->form_validation->set_rules('message', 'Message', 'required');
        $this->form_validation->set_rules('sender_bot_id', 'Sender Bot', 'required|integer');

        if ($this->form_validation->run() == FALSE) return $this->broadcast();

        $sender_bot_id = $this->input->post('sender_bot_id');
        $is_test_send = !empty($this->input->post('test_send'));
        $target_tag = $this->input->post('target_tag');
        if (empty($target_tag) || $target_tag === 'all') $target_tag = NULL;

        // Hitung penerima berdasarkan bot PENGIRIM
        $recipient_count = $this->UserModel->countActiveUsers($sender_bot_id, $target_tag, $is_test_send);

        if ($recipient_count > 0) {
            $broadcast_data = [
                'message' => $this->input->post('message'),
                'total_recipients' => $recipient_count,
                'target_tag' => $target_tag,
                'is_test_broadcast' => $is_test_send ? 1 : 0,
            ];
            // Simpan siaran dengan ID bot pengirim
            $this->BroadcastModel->create_broadcast($broadcast_data, $sender_bot_id);
        }
        redirect('dashboard/broadcast');
    }

    public function broadcast()
    {
        // Filter sekarang bersifat opsional, tidak lagi terikat pada sesi
        $filters = [
            'bot_id' => $this->input->get('bot_id'),
            'status' => $this->input->get('status')
        ];
        $filters = array_filter($filters, function($value) { return $value !== null && $value !== ''; });

        // Konfigurasi Paginasi
        $config['base_url'] = site_url('dashboard/broadcast');
        $config['total_rows'] = $this->BroadcastModel->count_all_broadcasts($filters);
        $config['per_page'] = 15;
        // ... (sisa konfigurasi paginasi)

        $this->pagination->initialize($config);
        $page = $this->input->get('page') ? (int)$this->input->get('page') : 0;

        $data['broadcasts'] = $this->BroadcastModel->get_all_broadcasts($filters, $config['per_page'], $page);
        $data['pagination_links'] = $this->pagination->create_links();
        $data['user_stats'] = $this->UserModel->getUserStats($filters['bot_id'] ?? null); // Stats untuk bot yang difilter
        $data['tags'] = $this->UserModel->getAllTags($filters['bot_id'] ?? null); // Tags untuk bot yang difilter
        $data['filters'] = $filters;

        // Ambil semua bot untuk dropdown pengirim
        $data['all_bots'] = $this->BotModel->getAllBots();

        $this->load->view('broadcast_view', $data);
    }

    public function delete_broadcast($id)
    {
        if ($id) {
            // Hapus broadcast berdasarkan ID saja, tanpa bot_id
            $this->BroadcastModel->delete_broadcast($id);
        }
        redirect('dashboard/broadcast');
    }

    public function export_csv()
    {
        $this->load->helper('download');
        // Filter diambil dari query string, bukan dari sesi
        $filters = [
            'bot_id'    => $this->input->get('bot_id'),
            'log_type'  => $this->input->get('log_type'),
            'chat_id'   => $this->input->get('chat_id'),
            'chat_name' => $this->input->get('chat_name'),
            'keyword'   => $this->input->get('keyword')
        ];
        $filters = array_filter($filters, function($value) { return $value !== null && $value !== ''; });

        $logs = $this->Log_model->get_logs($filters, 0, 0); // No limit for export

        if (empty($logs)) {
            // Tampilkan pesan atau redirect jika tidak ada data untuk diekspor
            $this->session->set_flashdata('error', 'No logs to export for the selected filters.');
            redirect($_SERVER['HTTP_REFERER'] ?? 'dashboard');
            return;
        }

        $filename = "logs_export_" . date('YmdHis') . ".csv";
        header('Content-Type: text/csv');
        header('Content-Disposition: attachment; filename="' . $filename . '"');

        $output = fopen('php://output', 'w');
        // Header CSV
        fputcsv($output, ['ID', 'Bot ID', 'Timestamp', 'Log Type', 'Chat ID', 'Chat Name', 'Username', 'Text']);

        // Data baris
        foreach ($logs as $log) {
            fputcsv($output, [
                $log['id'],
                $log['bot_id'],
                $log['timestamp'],
                $log['log_type'],
                $log['chat_id'],
                $log['chat_name'],
                $log['username'],
                $log['text']
            ]);
        }
        fclose($output);
        exit;
    }

}
