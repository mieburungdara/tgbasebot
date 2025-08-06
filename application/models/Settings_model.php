<?php
defined('BASEPATH') OR exit('No direct script access allowed');

class Settings_model extends CI_Model {

    public function __construct()
    {
        parent::__construct();
        $this->load->database();
    }

    /**
     * Mengambil nilai pengaturan berdasarkan key.
     * @param string $key Kunci pengaturan yang dicari.
     * @return string|null Mengembalikan nilai pengaturan atau null jika tidak ditemukan.
     */
    public function get_setting($key)
    {
        $query = $this->db->get_where('settings', array('key' => $key), 1);
        if ($query->num_rows() > 0)
        {
            return $query->row()->value;
        }
        return null;
    }

    /**
     * Menyimpan atau memperbarui nilai pengaturan.
     * @param string $key Kunci pengaturan.
     * @param string $value Nilai pengaturan yang akan disimpan.
     * @return bool
     */
    public function save_setting($key, $value)
    {
        $data = array('key' => $key, 'value' => $value);

        // Periksa apakah key sudah ada
        $this->db->where('key', $key);
        $query = $this->db->get('settings');

        if ($query->num_rows() > 0)
        {
            // Jika ada, perbarui
            $this->db->where('key', $key);
            return $this->db->update('settings', array('value' => $value));
        }
        else
        {
            // Jika tidak ada, sisipkan
            return $this->db->insert('settings', $data);
        }
    }
}
