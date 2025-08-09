<?php
defined('BASEPATH') OR exit('No direct script access allowed');

class KeywordModel extends CI_Model {

    public function __construct() {
        parent::__construct();
        $this->load->database();
    }

    /**
     * Mengambil semua balasan kata kunci (global).
     */
    public function getKeywords() {
        $this->db->order_by('keyword', 'ASC');
        $query = $this->db->get('keyword_replies');
        return $query->result_array();
    }

    /**
     * Mengambil balasan untuk kata kunci tertentu (global).
     */
    public function getReply($keyword) {
        $this->db->where('LOWER(keyword)', strtolower($keyword));
        $query = $this->db->get('keyword_replies');
        if ($query->num_rows() > 0) {
            return $query->row()->reply;
        }
        return null;
    }

    /**
     * Menambahkan balasan kata kunci baru (global).
     */
    public function addKeyword($data) {
        return $this->db->insert('keyword_replies', $data);
    }

    /**
     * Menghapus balasan kata kunci berdasarkan ID (global).
     */
    public function deleteKeyword($id) {
        $this->db->where('id', $id);
        return $this->db->delete('keyword_replies');
    }
}
