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
     * @return bool
     */
    public function add_log($type, $message)
    {
        $data = array(
            'log_type' => $type,
            'log_message' => $message,
            'created_at' => date('Y-m-d H:i:s') // Set timestamp secara manual
        );
        return $this->db->insert('bot_logs', $data);
    }
}
