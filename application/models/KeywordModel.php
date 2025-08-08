<?php
defined('BASEPATH') OR exit('No direct script access allowed');

class KeywordModel extends CI_Model {

    public function __construct() {
        parent::__construct();
        $this->load->database();
    }

    /**
     * Mengambil semua balasan kata kunci untuk bot tertentu.
     */
    public function getKeywords($bot_id) {
        $this->db->where('bot_id', $bot_id);
        $this->db->order_by('keyword', 'ASC');
        $query = $this->db->get('keyword_replies');
        return $query->result_array();
    }

    /**
     * Mengambil balasan untuk kata kunci tertentu untuk bot tertentu.
     */
    public function getReply($keyword, $bot_id) {
        $this->db->where('bot_id', $bot_id);
        $this->db->where('LOWER(keyword)', strtolower($keyword));
        $query = $this->db->get('keyword_replies');
        if ($query->num_rows() > 0) {
            return $query->row()->reply;
        }
        return null;
    }

    /**
     * Menambahkan balasan kata kunci baru untuk bot tertentu.
     */
    public function addKeyword($data, $bot_id) {
        $data['bot_id'] = $bot_id;
        return $this->db->insert('keyword_replies', $data);
    }

    /**
     * Menghapus balasan kata kunci berdasarkan ID untuk bot tertentu.
     */
    public function deleteKeyword($id, $bot_id) {
        $this->db->where('id', $id);
        $this->db->where('bot_id', $bot_id);
        return $this->db->delete('keyword_replies');
    }
}
