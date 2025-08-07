<?php
defined('BASEPATH') OR exit('No direct script access allowed');

class BroadcastModel extends CI_Model {

    public function __construct() {
        parent::__construct();
        $this->load->database();
    }

    /**
     * Membuat entri siaran baru.
     */
    public function create_broadcast($message, $total_recipients) {
        $data = [
            'message' => $message,
            'total_recipients' => $total_recipients,
            'status' => 'pending',
            'created_at' => date('Y-m-d H:i:s')
        ];
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
     * Mengambil semua siaran untuk ditampilkan di dasbor.
     */
    public function get_all_broadcasts($limit = 25, $offset = 0) {
        $this->db->order_by('created_at', 'DESC');
        $this->db->limit($limit, $offset);
        $query = $this->db->get('broadcasts');
        return $query->result_array();
    }

    /**
     * Menghitung total siaran.
     */
    public function count_all_broadcasts() {
        return $this->db->count_all('broadcasts');
    }
}
