<?php
defined('BASEPATH') OR exit('No direct script access allowed');

class KeywordModel extends CI_Model {

    public function __construct() {
        parent::__construct();
        $this->load->database();
    }

    /**
     * Get all keyword replies, ordered by keyword.
     * @return array
     */
    public function getKeywords() {
        $this->db->order_by('keyword', 'ASC');
        $query = $this->db->get('keyword_replies');
        return $query->result_array();
    }

    /**
     * Get a reply for a specific keyword.
     * @param string $keyword
     * @return string|null
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
     * Add a new keyword reply.
     * @param array $data
     * @return bool
     */
    public function addKeyword($data) {
        return $this->db->insert('keyword_replies', $data);
    }

    /**
     * Delete a keyword reply by its ID.
     * @param int $id
     * @return bool
     */
    public function deleteKeyword($id) {
        $this->db->where('id', $id);
        return $this->db->delete('keyword_replies');
    }
}
