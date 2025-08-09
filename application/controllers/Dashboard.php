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
        $this->load->library('pagination');
        $this->load->helper('url');
        $this->load->helper('form');
    }

    public function index()
    {
        // Tambahkan bot_id yang dipilih ke filter
        $filters = [
            'bot_id'    => $this->selected_bot_id,
            'log_type'  => $this->input->get('log_type'),
            'chat_id'   => $this->input->get('chat_id'),
            'chat_name' => $this->input->get('chat_name'),
            'keyword'   => $this->input->get('keyword')
        ];
        $filters = array_filter($filters, function($value) { return $value !== null && $value !== ''; });

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

        $data['logs'] = $this->Log_model->get_logs($filters, $config['per_page'], $page);
        $data['stats'] = $this->Log_model->get_stats($this->selected_bot_id);
        $data['pagination_links'] = $this->pagination->create_links();
        $data['filters'] = $filters;
        $data['log_types'] = ['incoming', 'outgoing', 'error'];

        $chart_days = $this->input->get('days') ? (int)$this->input->get('days') : 14;
        $chart_days = in_array($chart_days, [7, 14, 30]) ? $chart_days : 14;
        $chart_data = $this->Log_model->get_daily_log_counts($this->selected_bot_id, $chart_days);
        $data['chart_labels'] = json_encode(array_keys($chart_data));
        $data['chart_values'] = json_encode(array_values($chart_data));
        $data['chart_days'] = $chart_days;

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

        $data['user_stats'] = $this->UserModel->getUserStats($this->selected_bot_id);
        $data['health_stats'] = [
            'last_cron_run' => $this->Settings_model->get_setting('last_cron_run', $this->selected_bot_id),
            'last_incoming_message' => $this->Settings_model->get_setting('last_incoming_message', $this->selected_bot_id)
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
        $data['keywords'] = $this->KeywordModel->getKeywords($this->selected_bot_id);
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
            $this->KeywordModel->addKeyword($data, $this->selected_bot_id);
            redirect('dashboard/keywords');
        }
    }

    public function delete_keyword($id)
    {
        if ($id) $this->KeywordModel->deleteKeyword($id, $this->selected_bot_id);
        redirect('dashboard/keywords');
    }

    public function broadcast()
    {
        $filters = ['bot_id' => $this->selected_bot_id, 'status' => $this->input->get('status')];
        $filters = array_filter($filters, function($value) { return $value !== null && $value !== ''; });

        $config['base_url'] = site_url('dashboard/broadcast');
        $config['total_rows'] = $this->BroadcastModel->count_all_broadcasts($filters);
        $config['per_page'] = 15;
        // ... (sisa konfigurasi paginasi)
        $this->pagination->initialize($config);
        $page = $this->input->get('page') ? (int)$this->input->get('page') : 0;

        $data['broadcasts'] = $this->BroadcastModel->get_all_broadcasts($filters, $config['per_page'], $page);
        $data['pagination_links'] = $this->pagination->create_links();
        $data['user_stats'] = $this->UserModel->getUserStats($this->selected_bot_id);
        $data['tags'] = $this->UserModel->getAllTags($this->selected_bot_id);
        $data['filters'] = $filters;
        $data['cron_secret_key'] = $this->Settings_model->get_setting('cron_secret_key', $this->selected_bot_id);

        $this->load->view('broadcast_view', $data);
    }

    public function send_broadcast()
    {
        $this->form_validation->set_rules('message', 'Message', 'required');
        if ($this->form_validation->run() == FALSE) return $this->broadcast();

        $is_test_send = !empty($this->input->post('test_send'));
        $target_tag = $this->input->post('target_tag');
        if (empty($target_tag) || $target_tag === 'all') $target_tag = NULL;

        $recipient_count = $this->UserModel->countActiveUsers($this->selected_bot_id, $target_tag, $is_test_send);
        if ($recipient_count > 0) {
            $broadcast_data = [
                'message' => $this->input->post('message'),
                'total_recipients' => $recipient_count,
                'target_tag' => $target_tag,
                'is_test_broadcast' => $is_test_send ? 1 : 0,
            ];
            $this->BroadcastModel->create_broadcast($broadcast_data, $this->selected_bot_id);
        }
        redirect('dashboard/broadcast');
    }

    public function delete_broadcast($id)
    {
        if ($id) $this->BroadcastModel->delete_broadcast($id, $this->selected_bot_id);
        redirect('dashboard/broadcast');
    }

    public function export_csv()
    {
        $this->load->helper('download');
        $filters = ['bot_id' => $this->selected_bot_id, /* ... filter lain ... */ ];
        $logs = $this->Log_model->get_logs($filters, 0, 0);
        // ... (sisa logika CSV)
    }

    public function reset_cron_key()
    {
        $new_key = bin2hex(random_bytes(16));
        $this->Settings_model->save_setting('cron_secret_key', $new_key, $this->selected_bot_id);
        redirect('dashboard/broadcast');
    }

    public function switch_bot($bot_id)
    {
        $this->session->set_userdata('selected_bot_id', (int)$bot_id);
        redirect('dashboard');
    }
}
