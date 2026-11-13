<?php
defined('BASEPATH') OR exit('No direct script access allowed');

class Templates_model extends CI_Model {

    public function getAllActive_get() {
        $rows = $this->db->where('is_active', 1)
                          ->order_by('id_template', 'DESC')
                          ->get('templates')
                          ->result();

        $result = [];
        foreach ($rows as $row) {
            $result[] = $this->_mapRow($row);
        }
        return $result;
    }

    public function getAll() {
        return $this->db->order_by('id_template', 'DESC')->get('templates')->result();
    }

    public function getById($id) {
        return $this->db->where('id_template', $id)->get('templates')->row();
    }

    public function insert($data) {
        return $this->db->insert('templates', $data);
    }

    public function update($id, $data) {
        $this->db->where('id_template', $id);
        return $this->db->update('templates', $data);
    }

    public function delete($id) {
        return $this->db->where('id_template', $id)->delete('templates');
    }

        public function slugExists($slug, $excludeId = null) {
        $this->db->where('slug', $slug);
        if ($excludeId) {
            $this->db->where('id_template !=', $excludeId);
        }
        return $this->db->get('templates')->num_rows() > 0;
    }

    private function _mapRow($row) {
        $data = [
            'id'         => $row->slug,
            'name'       => $row->name,
            'category'      => $row->category,
            'templateImage' => !empty($row->template_image) ? (preg_match('/^(https?:\/\/|data:|blob:|\/)/', $row->template_image) ? $row->template_image : base_url('uploads/templates/' . $row->template_image)) : null,
            'width'         => (int) $row->width,
            'height'        => (int) $row->height,
            'photoCount'    => (int) $row->photo_count,
            'background'    => json_decode($row->background),
            'photoSlots'    => json_decode($row->photo_slots),
        ];

        if (!empty($row->decorations))   $data['decorations'] = json_decode($row->decorations);
        if (!empty($row->text_config))   $data['text'] = json_decode($row->text_config);
        if (!empty($row->border_config)) $data['border'] = json_decode($row->border_config);
        if (!empty($row->photo_border))  $data['photoBorder'] = json_decode($row->photo_border);
        if (!empty($row->accent))        $data['accent'] = $row->accent;

        return $data;
    }
}