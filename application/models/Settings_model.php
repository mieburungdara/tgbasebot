<?php
defined('BASEPATH') OR exit('No direct script access allowed');

class Settings_model extends CI_Model {

    public function __construct()
    {
        parent::__construct();
        $this->load->database();
    }

    /**
     * Mengambil nilai pengaturan berdasarkan key untuk bot tertentu.
     */
    public function get_setting($key, $bot_id)
    {
        $this->db->where('bot_id', $bot_id);
        $query = $this->db->get_where('settings', array('key' => $key), 1);
        if ($query->num_rows() > 0)
        {
            return $query->row()->value;
        }
        return null;
    }

    /**
     * Menyimpan atau memperbarui nilai pengaturan untuk bot tertentu.
     */
    public function save_setting($key, $value, $bot_id)
    {
        $data = array('bot_id' => $bot_id, 'key' => $key, 'value' => $value);

        $this->db->where('bot_id', $bot_id);
        $this->db->where('key', $key);
        $query = $this->db->get('settings');

        if ($query->num_rows() > 0)
        {
            $this->db->where('bot_id', $bot_id);
            $this->db->where('key', $key);
            return $this->db->update('settings', array('value' => $value));
        }
        else
        {
            return $this->db->insert('settings', $data);
        }
    }
}
