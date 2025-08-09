<?php
defined('BASEPATH') OR exit('No direct script access allowed');

class UserManagement extends MY_Controller {

    protected $selected_bot_id;

    public function __construct() {
        parent::__construct();
        $this->load->model('UserModel');
        $this->load->library('pagination');
        $this->load->helper('url');
        $this->load->helper('form');
        $this->load->library('form_validation');
    }

    public function index() {
        if (!$this->selected_bot_id) {
            // Tampilkan pesan jika tidak ada bot yang dipilih
            $this->load->view('user_management_view', ['users' => []]);
            return;
        }

        $config['base_url'] = site_url('user_management/index');
        $config['total_rows'] = $this->UserModel->countAllUsers($this->selected_bot_id);
        $config['per_page'] = 25;
        // ... (sisa konfigurasi paginasi)

        $this->pagination->initialize($config);

        $page = $this->input->get('page') ? (int)$this->input->get('page') : 0;
        $data['users'] = $this->UserModel->getAllUsersWithPagination($this->selected_bot_id, $config['per_page'], $page);
        $data['pagination_links'] = $this->pagination->create_links();

        $this->load->view('user_management_view', $data);
    }

    public function edit($id) {
        $data['user'] = $this->UserModel->getUserById($id, $this->selected_bot_id);
        if (empty($data['user'])) {
            show_404();
        }
        $this->load->view('user_edit_view', $data);
    }

    public function update($id) {
        $user = $this->UserModel->getUserById($id, $this->selected_bot_id);
        if (empty($user)) {
            show_404();
        }

        $this->form_validation->set_rules('tags', 'Tags', 'trim');
        $this->form_validation->set_rules('is_test_user', 'Test User', 'trim');

        if ($this->form_validation->run() == FALSE) {
            $this->edit($id);
        } else {
            $update_data = [
                'tags' => $this->input->post('tags'),
                'is_test_user' => $this->input->post('is_test_user') ? 1 : 0
            ];

            $this->UserModel->updateUser($id, $update_data, $this->selected_bot_id);
            redirect('user_management');
        }
    }
}
