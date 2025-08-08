<?php
defined('BASEPATH') OR exit('No direct script access allowed');

class BotModel extends CI_Model {

    public function __construct() {
        parent::__construct();
        $this->load->database();
    }

    /**
     * Mendapatkan detail bot berdasarkan token webhook uniknya.
     */
    public function getBotByWebhookToken($token) {
        if (empty($token)) {
            return null;
        }
        $this->db->where('webhook_token', $token);
        $query = $this->db->get('bots');
        return $query->row_array();
    }

    /**
     * Membuat bot baru dan menghasilkan token webhook unik.
     */
    public function createBot($data) {
        // Pastikan token unik
        do {
            $webhook_token = bin2hex(random_bytes(24));
            $this->db->where('webhook_token', $webhook_token);
            $query = $this->db->get('bots');
        } while ($query->num_rows() > 0);

        $data['webhook_token'] = $webhook_token;

        return $this->db->insert('bots', $data);
    }

    /**
     * Mengambil semua bot yang terdaftar.
     */
    public function getAllBots() {
        $this->db->order_by('name', 'ASC');
        $query = $this->db->get('bots');
        return $query->result_array();
    }

    /**
     * Mengambil detail bot berdasarkan ID.
     */
    public function getBotById($id) {
        $this->db->where('id', $id);
        $query = $this->db->get('bots');
        return $query->row_array();
    }
}
