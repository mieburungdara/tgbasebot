<?php
defined('BASEPATH') OR exit('No direct script access allowed');

class UserModel extends CI_Model {

    public function __construct() {
        parent::__construct();
        $this->load->database();
    }

    /**
     * Menambahkan pengguna baru jika belum ada, atau memperbarui jika sudah ada.
     */
    public function addUser($userData) {
        $this->db->where('chat_id', $userData['chat_id']);
        $query = $this->db->get('users');

        if ($query->num_rows() == 0) {
            $this->db->insert('users', $userData);
            return $this->db->insert_id();
        } else {
            // Hanya perbarui nama dan username, jangan sentuh status, tag, dll.
            $update_data = [
                'username' => $userData['username'],
                'first_name' => $userData['first_name'],
                'last_name' => $userData['last_name'],
            ];
            $this->db->where('chat_id', $userData['chat_id']);
            $this->db->update('users', $update_data);
            return $query->row()->id;
        }
    }

    /**
     * Mengambil batch pengguna aktif untuk diproses siaran.
     * Dapat difilter berdasarkan tag atau status tes.
     */
    public function getActiveUsersBatch($limit, $offset, $tag = NULL, $is_test = FALSE) {
        $this->db->where('status', 'active');
        if ($is_test) {
            $this->db->where('is_test_user', 1);
        }
        if ($tag) {
            $this->db->like('tags', $tag, 'both');
        }
        $this->db->order_by('id', 'ASC');
        $this->db->limit($limit, $offset);
        $query = $this->db->get('users');
        return $query->result_array();
    }

    /**
     * Menghitung pengguna aktif, opsional difilter berdasarkan tag atau status tes.
     */
    public function countActiveUsers($tag = NULL, $is_test = FALSE) {
        $this->db->where('status', 'active');
        if ($is_test) {
            $this->db->where('is_test_user', 1);
        }
        if ($tag) {
            $this->db->like('tags', $tag, 'both');
        }
        return $this->db->count_all_results('users');
    }

    /**
     * Menandai pengguna sebagai diblokir.
     */
    public function markUserAsBanned($chat_id) {
        $this->db->where('chat_id', $chat_id);
        return $this->db->update('users', ['status' => 'banned']);
    }

    /**
     * Mendapatkan statistik tentang pengguna (aktif vs diblokir).
     */
    public function getUserStats() {
        $this->db->select("COUNT(id) as total_users");
        $this->db->select("COUNT(CASE WHEN status = 'active' THEN 1 END) as active_users");
        $this->db->select("COUNT(CASE WHEN status = 'banned' THEN 1 END) as banned_users");
        $query = $this->db->get('users');
        return $query->row_array();
    }

    /**
     * Mengambil semua pengguna dengan paginasi untuk halaman manajemen.
     */
    public function getAllUsersWithPagination($limit, $offset) {
        $this->db->order_by('id', 'DESC');
        $this->db->limit($limit, $offset);
        $query = $this->db->get('users');
        return $query->result_array();
    }

    /**
     * Menghitung semua pengguna untuk paginasi.
     */
    public function countAllUsers() {
        return $this->db->count_all('users');
    }

    /**
     * Mengambil detail pengguna tunggal berdasarkan ID.
     */
    public function getUserById($id) {
        $this->db->where('id', $id);
        $query = $this->db->get('users');
        return $query->row_array();
    }

    /**
     * Memperbarui detail pengguna.
     */
    public function updateUser($id, $data) {
        $this->db->where('id', $id);
        return $this->db->update('users', $data);
    }

    /**
     * Mendapatkan daftar semua tag unik yang digunakan.
     */
    public function getAllTags() {
        $this->db->select('tags');
        $this->db->where('tags IS NOT NULL');
        $this->db->where('tags !=', '');
        $query = $this->db->get('users');

        $all_tags = [];
        foreach ($query->result_array() as $row) {
            $tags = explode(',', $row['tags']);
            foreach ($tags as $tag) {
                $trimmed_tag = trim($tag);
                if (!empty($trimmed_tag)) {
                    $all_tags[$trimmed_tag] = $trimmed_tag;
                }
            }
        }
        sort($all_tags);
        return array_values($all_tags);
    }
}
