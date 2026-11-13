<?php
defined('BASEPATH') OR exit('No direct script access allowed');

class Stickers extends CI_Controller {

    public function __construct() {
        parent::__construct();
        $this->load->model('Stickers_model');
    }

    // GET Stickers/all  -> daftar stiker aktif (JSON)
    public function all() {
        header('Access-Control-Allow-Origin: *');
        header('Content-Type: application/json');

        $out = [];
        foreach ($this->Stickers_model->getAll(true) as $s) {
            $out[] = [
                'id'   => (int) $s->id,
                'name' => $s->name,
                'url'  => site_url('Stickers/file/' . $s->id),
            ];
        }
        echo json_encode($out);
    }

    // GET Stickers/file/5 -> kirim gambar + header CORS
    public function file($id = 0) {
        $s    = $this->Stickers_model->find((int) $id);
        $path = $s ? FCPATH . 'uploads/stickers/' . $s->filename : '';
        if (!$s || !is_file($path)) show_404();

        header('Access-Control-Allow-Origin: *');
        header('Content-Type: ' . mime_content_type($path));
        header('Cache-Control: public, max-age=86400');
        readfile($path);
    }
}