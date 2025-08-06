<?php
defined('BASEPATH') OR exit('No direct script access allowed');

class Log_model extends CI_Model {

    public function __construct()
    {
        parent::__construct();
        $this->load->database();
    }

    /**
     * Menambahkan entri log baru ke database.
     *
     * @param string $type Jenis log (misalnya, 'incoming', 'outgoing', 'error').
     * @param string $message Pesan atau data log.
     * @param int|null $chat_id ID obrolan opsional.
     * @param string|null $chat_name Nama obrolan opsional.
     * @return bool
     */
    public function add_log($type, $message, $chat_id = null, $chat_name = null)
    {
        $data = array(
            'log_type'    => $type,
            'log_message' => $message,
            'chat_id'     => $chat_id,
            'chat_name'   => $chat_name,
            'created_at'  => date('Y-m-d H:i:s')
        );
        return $this->db->insert('bot_logs', $data);
    }

    /**
     * Menghitung jumlah log berdasarkan filter.
     *
     * @param array $filters Filter untuk diterapkan.
     * @return int
     */
    public function count_logs($filters = [])
    {
        $this->db->from('bot_logs');
        $this->_apply_filters($filters);
        return $this->db->count_all_results();
    }

    /**
     * Mengambil log dari database dengan filter dan paginasi.
     *
     * @param array $filters Filter untuk diterapkan.
     * @param int $limit Jumlah log yang akan diambil.
     * @param int $offset Offset untuk paginasi.
     * @return array
     */
    public function get_logs($filters = [], $limit = 25, $offset = 0)
    {
        $this->db->from('bot_logs');
        $this->_apply_filters($filters);
        $this->db->order_by('id', 'DESC');
        $this->db->limit($limit, $offset);
        $query = $this->db->get();
        return $query->result_array();
    }

    /**
     * Menerapkan filter ke query database secara privat.
     *
     * @param array $filters Filter untuk diterapkan.
     */
    private function _apply_filters($filters = [])
    {
        if (!empty($filters['log_type'])) {
            $this->db->where('log_type', $filters['log_type']);
        }
        if (!empty($filters['chat_id'])) {
            $this->db->where('chat_id', $filters['chat_id']);
        }
        if (!empty($filters['chat_name'])) {
            $this->db->like('chat_name', $filters['chat_name']);
        }
        if (!empty($filters['keyword'])) {
            $this->db->like('log_message', $filters['keyword']);
        }
    }

    /**
     * Mengambil statistik log.
     *
     * @return array
     */
    public function get_stats()
    {
        $stats = [];
        $stats['total_logs'] = $this->db->count_all('bot_logs');

        $this->db->select('log_type, COUNT(*) as count');
        $this->db->group_by('log_type');
        $query = $this->db->get('bot_logs');
        $stats['logs_by_type'] = $query->result_array();

        $this->db->where('created_at >=', date('Y-m-d H:i:s', strtotime('-24 hours')));
        $stats['logs_today'] = $this->db->count_all_results('bot_logs');

        return $stats;
    }

    /**
     * Menghapus entri log tunggal.
     *
     * @param int $id ID log yang akan dihapus.
     * @return bool
     */
    public function delete_log($id)
    {
        return $this->db->delete('bot_logs', ['id' => $id]);
    }

    /**
     * Menghapus semua entri log dari database.
     *
     * @return bool
     */
    public function clear_all_logs()
    {
        return $this->db->truncate('bot_logs');
    }
}
