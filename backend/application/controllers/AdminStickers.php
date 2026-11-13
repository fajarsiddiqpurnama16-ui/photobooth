<?php
defined('BASEPATH') OR exit('No direct script access allowed');

class AdminStickers extends CI_Controller {

    public function __construct() {
    parent::__construct();
    $this->load->database();
    $this->load->model('Stickers_model');
    if (!$this->session->userdata('admin_logged_in')) {
        redirect('Login');
    }
}

public function index() {
    $data['stickers'] = $this->Stickers_model->getAll();
    $this->load->view('templates/_header');
    $this->load->view('stickers', $data);
    $this->load->view('templates/_footer');
}
    public function add() {
        $name = trim($this->input->post('name'));
        $dir  = FCPATH . 'uploads/stickers/';
        if (!is_dir($dir)) mkdir($dir, 0777, true);

        $this->load->library('upload', [
            'upload_path'   => $dir,
            'allowed_types' => 'png|webp',
            'max_size'      => 2048,
            'encrypt_name'  => TRUE,
        ]);

        if ($name !== '' && $this->upload->do_upload('image')) {
            $this->Stickers_model->insert($name, $this->upload->data('file_name'));
        }
        redirect('AdminStickers');
    }

    public function toggle($id) {
        $this->Stickers_model->toggle((int) $id);
        redirect('AdminStickers');
    }

    public function delete($id) {
        $s = $this->Stickers_model->find((int) $id);
        if ($s) {
            @unlink(FCPATH . 'uploads/stickers/' . $s->filename);
            $this->Stickers_model->delete((int) $id);
        }
        redirect('AdminStickers');
    }
}