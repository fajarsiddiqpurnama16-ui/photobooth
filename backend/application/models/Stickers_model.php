<?php
defined('BASEPATH') OR exit('No direct script access allowed');

class Stickers_model extends CI_Model {

    public function getAll($onlyActive = false) {
        if ($onlyActive) $this->db->where('is_active', 1);
        return $this->db->order_by('id', 'DESC')->get('stickers')->result();
    }

    public function find($id) {
        return $this->db->where('id', $id)->get('stickers')->row();
    }

    public function insert($name, $filename) {
        $this->db->insert('stickers', ['name' => $name, 'filename' => $filename]);
        return $this->db->insert_id();
    }

    public function toggle($id) {
        $s = $this->find($id);
        if (!$s) return;
        $this->db->where('id', $id)->update('stickers', ['is_active' => $s->is_active ? 0 : 1]);
    }

    public function delete($id) {
        $this->db->where('id', $id)->delete('stickers');
    }
}