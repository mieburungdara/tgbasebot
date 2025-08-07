<?php
defined('BASEPATH') OR exit('No direct script access allowed');

class BroadcastModel extends CI_Model {

    public function __construct() {
        parent::__construct();
        $this->load->database();
    }

    /**
     * Membuat entri siaran baru dengan opsi penargetan.
     * @param array $data Data siaran, termasuk 'message', 'total_recipients', dan opsional 'target_tag', 'is_test_broadcast'
     */
    public function create_broadcast($data) {
        $defaults = [
            'status' => 'pending',
            'created_at' => date('Y-m-d H:i:s'),
            'is_test_broadcast' => 0,
            'target_tag' => NULL
        ];
        $data = array_merge($defaults, $data);

        $this->db->insert('broadcasts', $data);
        return $this->db->insert_id();
    }

    /**
     * Mengambil pekerjaan siaran berikutnya untuk diproses.
     * Memprioritaskan yang sudah 'processing', lalu yang 'pending'.
     */
    public function get_job_to_process() {
        $this->db->where_in('status', ['processing', 'pending']);
        $this->db->order_by('status', 'DESC'); // 'processing' > 'pending'
        $this->db->order_by('created_at', 'ASC');
        $this->db->limit(1);
        $query = $this->db->get('broadcasts');
        return $query->row_array();
    }

    /**
     * Menandai siaran sebagai 'processing'.
     */
    public function mark_as_processing($id) {
        $this->db->where('id', $id);
        return $this->db->update('broadcasts', [
            'status' => 'processing',
            'processing_started_at' => date('Y-m-d H:i:s')
        ]);
    }

    /**
     * Menandai siaran sebagai 'completed'.
     */
    public function mark_as_completed($id) {
        $this->db->where('id', $id);
        return $this->db->update('broadcasts', [
            'status' => 'completed',
            'completed_at' => date('Y-m-d H:i:s')
        ]);
    }

    /**
     * Memperbarui statistik terkirim dan gagal untuk sebuah siaran.
     */
    public function update_stats($id, $sent_increment, $failed_increment) {
        $this->db->where('id', $id);
        $this->db->set('sent_count', 'sent_count + ' . (int)$sent_increment, FALSE);
        $this->db->set('failed_count', 'failed_count + ' . (int)$failed_increment, FALSE);
        return $this->db->update('broadcasts');
    }

    /**
     * Mengambil detail siaran tunggal.
     */
    public function get_broadcast($id) {
        $this->db->where('id', $id);
        $query = $this->db->get('broadcasts');
        return $query->row_array();
    }

    /**
     * Menerapkan filter pada query siaran.
     */
    private function _apply_broadcast_filters($filters = [])
    {
        if (!empty($filters['status'])) {
            $this->db->where('status', $filters['status']);
        }
    }

    /**
     * Mengambil semua siaran untuk ditampilkan di dasbor, dengan filter dan paginasi.
     */
    public function get_all_broadcasts($filters = [], $limit = 25, $offset = 0) {
        $this->_apply_broadcast_filters($filters);
        $this->db->order_by('created_at', 'DESC');
        $this->db->limit($limit, $offset);
        $query = $this->db->get('broadcasts');
        return $query->result_array();
    }

    /**
     * Menghitung total siaran, dengan filter.
     */
    public function count_all_broadcasts($filters = []) {
        $this->_apply_broadcast_filters($filters);
        return $this->db->count_all_results('broadcasts');
    }

    /**
     * Memperbarui pesan error terakhir untuk sebuah siaran.
     */
    public function update_error_message($id, $message) {
        $this->db->where('id', $id);
        return $this->db->update('broadcasts', ['last_error_message' => $message]);
    }

    /**
     * Menghapus catatan siaran.
     */
    public function delete_broadcast($id) {
        $this->db->where('id', $id);
        return $this->db->delete('broadcasts');
    }
}
