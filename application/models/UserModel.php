<?php
defined('BASEPATH') OR exit('No direct script access allowed');

class UserModel extends CI_Model {

    public function __construct() {
        parent::__construct();
        $this->load->database();
    }

    /**
     * Add a new user if they don't exist, or update if they do.
     * Checks based on chat_id.
     * @param array $userData
     * @return int The user's ID.
     */
    public function addUser($userData) {
        $this->db->where('chat_id', $userData['chat_id']);
        $query = $this->db->get('users');

        if ($query->num_rows() == 0) {
            $this->db->insert('users', $userData);
            return $this->db->insert_id();
        } else {
            // Update user info in case their name or username changes
            $this->db->where('chat_id', $userData['chat_id']);
            $this->db->update('users', $userData);
            return $query->row()->id;
        }
    }

    /**
     * Get all users for broadcasting.
     * @return array
     * @deprecated Use getActiveUsersBatch for new broadcast system
     */
    public function getAllUsers() {
        $this->db->where('status', 'active');
        $query = $this->db->get('users');
        return $query->result_array();
    }

    /**
     * Get a batch of active users for processing.
     */
    public function getActiveUsersBatch($limit, $offset) {
        $this->db->where('status', 'active');
        $this->db->order_by('id', 'ASC');
        $this->db->limit($limit, $offset);
        $query = $this->db->get('users');
        return $query->result_array();
    }

    /**
     * Mark a user as banned.
     */
    public function markUserAsBanned($chat_id) {
        $this->db->where('chat_id', $chat_id);
        return $this->db->update('users', ['status' => 'banned']);
    }

    /**
     * Get statistics about users (active vs banned).
     */
    public function getUserStats() {
        $this->db->select("COUNT(id) as total_users");
        $this->db->select("COUNT(CASE WHEN status = 'active' THEN 1 END) as active_users");
        $this->db->select("COUNT(CASE WHEN status = 'banned' THEN 1 END) as banned_users");
        $query = $this->db->get('users');
        return $query->row_array();
    }
}
