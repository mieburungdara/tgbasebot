<?php
defined('BASEPATH') OR exit('No direct script access allowed');

class UserManagement extends CI_Controller {

    public function __construct() {
        parent::__construct();
        $this->load->model('UserModel');
        $this->load->library('pagination');
        $this->load->helper('url');
        $this->load->helper('form');
    }

    public function index() {
        $config['base_url'] = site_url('user_management/index');
        $config['total_rows'] = $this->UserModel->countAllUsers();
        $config['per_page'] = 25;
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

        $this->pagination->initialize($config);

        $page = $this->input->get('page') ? (int)$this->input->get('page') : 0;
        $data['users'] = $this->UserModel->getAllUsersWithPagination($config['per_page'], $page);
        $data['pagination_links'] = $this->pagination->create_links();

        $this->load->view('user_management_view', $data);
    }

    public function edit($id) {
        $data['user'] = $this->UserModel->getUserById($id);
        if (empty($data['user'])) {
            show_404();
        }
        $this->load->view('user_edit_view', $data);
    }

    public function update($id) {
        $user = $this->UserModel->getUserById($id);
        if (empty($user)) {
            show_404();
        }

        $this->load->library('form_validation');
        $this->form_validation->set_rules('tags', 'Tags', 'trim');
        $this->form_validation->set_rules('is_test_user', 'Test User', 'trim');

        if ($this->form_validation->run() == FALSE) {
            $this->edit($id);
        } else {
            $update_data = [
                'tags' => $this->input->post('tags'),
                'is_test_user' => $this->input->post('is_test_user') ? 1 : 0
            ];

            $this->UserModel->updateUser($id, $update_data);
            redirect('user_management');
        }
    }
}
