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
     */
    public function add_log($type, $message, $chat_id = null, $chat_name = null, $bot_id = null)
    {
        $data = array(
            'bot_id'      => $bot_id,
            'log_type'    => $type,
            'log_message' => $message,
            'chat_id'     => $chat_id,
            'chat_name'   => $chat_name,
            'created_at'  => date('Y-m-d H:i:s')
        );
        return $this->db->insert('bot_logs', $data);
    }

    /**
     * Menerapkan filter ke query database secara privat.
     */
    private function _apply_filters($filters = [])
    {
        if (!empty($filters['bot_id'])) {
            $this->db->where('bot_id', $filters['bot_id']);
        }
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
     * Menghitung jumlah log berdasarkan filter.
     */
    public function count_logs($filters = [])
    {
        $this->_apply_filters($filters);
        return $this->db->count_all_results('bot_logs');
    }

    /**
     * Mengambil log dari database dengan filter dan paginasi.
     */
    public function get_logs($filters = [], $limit = 25, $offset = 0)
    {
        $this->_apply_filters($filters);
        $this->db->order_by('id', 'DESC');
        if ($limit > 0) {
            $this->db->limit($limit, $offset);
        }
        $query = $this->db->get('bot_logs');
        return $query->result_array();
    }

    /**
     * Mengambil statistik log untuk bot tertentu.
     */
    public function get_stats($bot_id)
    {
        $stats = [];
        $this->db->where('bot_id', $bot_id);
        $stats['total_logs'] = $this->db->count_all_results('bot_logs');

        $this->db->select('log_type, COUNT(*) as count');
        $this->db->where('bot_id', $bot_id);
        $this->db->group_by('log_type');
        $query = $this->db->get('bot_logs');
        $stats['logs_by_type'] = $query->result_array();

        $this->db->where('bot_id', $bot_id);
        $this->db->where('created_at >=', date('Y-m-d H:i:s', strtotime('-24 hours')));
        $stats['logs_today'] = $this->db->count_all_results('bot_logs');

        return $stats;
    }

    /**
     * Menghapus entri log tunggal untuk bot tertentu.
     */
    public function delete_log($id, $bot_id)
    {
        $this->db->where('bot_id', $bot_id);
        return $this->db->delete('bot_logs', ['id' => $id]);
    }

    /**
     * Menghapus semua entri log dari database untuk bot tertentu.
     */
    public function clear_all_logs($bot_id)
    {
        $this->db->where('bot_id', $bot_id);
        return $this->db->delete('bot_logs');
    }

    /**
     * Mengambil jumlah log harian selama N hari terakhir untuk bot tertentu.
     */
    public function get_daily_log_counts($bot_id, $days = 7)
    {
        $this->db->select('DATE(created_at) as date, COUNT(id) as count');
        $this->db->where('bot_id', $bot_id);
        $this->db->where('created_at >=', date('Y-m-d H:i:s', strtotime("-$days days")));
        $this->db->group_by('DATE(created_at)');
        $this->db->order_by('date', 'ASC');
        $query = $this->db->get('bot_logs');
        $results = $query->result_array();

        $date_range = [];
        for ($i = $days - 1; $i >= 0; $i--) {
            $date = date('Y-m-d', strtotime("-$i days"));
            $date_range[$date] = 0;
        }

        foreach ($results as $row) {
            $date_range[$row['date']] = (int)$row['count'];
        }

        return $date_range;
    }
}
