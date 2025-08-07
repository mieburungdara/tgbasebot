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
     */
    public function getAllUsers() {
        $query = $this->db->get('users');
        return $query->result_array();
    }
}
