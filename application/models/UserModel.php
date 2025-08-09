<?php
defined('BASEPATH') OR exit('No direct script access allowed');

class UserModel extends CI_Model {

    public function __construct() {
        parent::__construct();
        $this->load->database();
    }

    /**
     * Menambahkan pengguna baru jika belum ada, atau memperbarui jika sudah ada, untuk bot tertentu.
     */
    public function addUser($userData) {
        $this->db->where('bot_id', $userData['bot_id']);
        $this->db->where('chat_id', $userData['chat_id']);
        $query = $this->db->get('users');

        if ($query->num_rows() == 0) {
            $this->db->insert('users', $userData);
            return $this->db->insert_id();
        } else {
            $update_data = [
                'username' => $userData['username'],
                'first_name' => $userData['first_name'],
                'last_name' => $userData['last_name'],
            ];
            $this->db->where('id', $query->row()->id);
            $this->db->update('users', $update_data);
            return $query->row()->id;
        }
    }

    public function getActiveUsersBatch($bot_id, $limit, $offset, $tag = NULL, $is_test = FALSE) {
        $this->db->where('bot_id', $bot_id);
        $this->db->where('status', 'active');
        if ($is_test) $this->db->where('is_test_user', 1);
        if ($tag) $this->db->like('tags', $tag, 'both');
        $this->db->order_by('id', 'ASC');
        $this->db->limit($limit, $offset);
        $query = $this->db->get('users');
        return $query->result_array();
    }

    public function countActiveUsers($bot_id, $tag = NULL, $is_test = FALSE) {
        $this->db->where('bot_id', $bot_id);
        $this->db->where('status', 'active');
        if ($is_test) $this->db->where('is_test_user', 1);
        if ($tag) $this->db->like('tags', $tag, 'both');
        return $this->db->count_all_results('users');
    }

    public function setUserStatus($chat_id, $status, $bot_id) {
        $allowed_statuses = ['active', 'banned', 'unsubscribed'];
        if (!in_array($status, $allowed_statuses)) return FALSE;

        $this->db->where('bot_id', $bot_id);
        $this->db->where('chat_id', $chat_id);
        return $this->db->update('users', ['status' => $status]);
    }

    public function markUserAsBanned($chat_id, $bot_id) {
        return $this->setUserStatus($chat_id, 'banned', $bot_id);
    }

    public function getUserStats($bot_id = null) {
        if ($bot_id) {
            $this->db->where('bot_id', $bot_id);
        }
        $this->db->select("COUNT(id) as total_users");
        $this->db->select("COUNT(CASE WHEN status = 'active' THEN 1 END) as active_users");
        $this->db->select("COUNT(CASE WHEN status = 'banned' THEN 1 END) as banned_users");
        $this->db->select("COUNT(CASE WHEN status = 'unsubscribed' THEN 1 END) as unsubscribed_users");
        $query = $this->db->get('users');
        return $query->row_array();
    }

    public function getAllUsersWithPagination($bot_id, $limit, $offset) {
        $this->db->where('bot_id', $bot_id);
        $this->db->order_by('id', 'DESC');
        $this->db->limit($limit, $offset);
        $query = $this->db->get('users');
        return $query->result_array();
    }

    public function countAllUsers($bot_id) {
        $this->db->where('bot_id', $bot_id);
        return $this->db->count_all_results('users');
    }

    public function getUserById($id, $bot_id) {
        $this->db->where('id', $id);
        $this->db->where('bot_id', $bot_id);
        $query = $this->db->get('users');
        return $query->row_array();
    }

    public function updateUser($id, $data, $bot_id) {
        $this->db->where('id', $id);
        $this->db->where('bot_id', $bot_id);
        return $this->db->update('users', $data);
    }

    public function getAllTags($bot_id) {
        $this->db->select('tags');
        $this->db->where('bot_id', $bot_id);
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
