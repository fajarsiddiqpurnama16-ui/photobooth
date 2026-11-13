<?php
defined('BASEPATH') OR exit('No direct script access allowed');

class Effects_model extends CI_Model {

    private $table = 'effects';

    public function __construct() {
        parent::__construct();
        $this->load->database();
    }

    public function getAll($onlyActive = false) {
        if ($onlyActive) {
            $this->db->where('is_active', 1);
        }
        return $this->db->order_by('sort_order', 'ASC')->order_by('id_effect', 'ASC')
                        ->get($this->table)->result();
    }

    public function getById($id) {
        return $this->db->where('id_effect', (int) $id)->get($this->table)->row();
    }

    public function insert($data) {
        $this->db->insert($this->table, $data);
        return $this->db->insert_id();
    }

    public function update($id, $data) {
        $this->db->where('id_effect', (int) $id)->update($this->table, $data);
    }

    public function delete($id) {
        $this->db->where('id_effect', (int) $id)->delete($this->table);
    }
}