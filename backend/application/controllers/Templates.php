<?php
defined('BASEPATH') OR exit('No direct script access allowed');

use Restserver\Libraries\REST_Controller;

require APPPATH . 'libraries/Format.php';
require APPPATH . 'libraries/REST_Controller.php';

class Templates extends REST_Controller {

    public function __construct() {
    parent::__construct();
    header('Access-Control-Allow-Origin: *');
    header('Access-Control-Allow-Methods: GET, POST, PUT, DELETE, OPTIONS');
    header('Access-Control-Allow-Headers: Content-Type, Authorization');
    $this->load->model('Templates_model');
}
    public function index_get() {
        $data = $this->Templates_model->getAllActive_get();

        $this->response([
            'status'  => TRUE,
            'message' => 'Success',
            'data'    => $data
        ], 200);
    }

        public function admin_list() {
        $data['templates'] = $this->Templates_model->getAll();
        $this->load->view('templates/index', $data);
    }
    
    public function showcase()
{
    // Ambil semua template aktif (is_active=1) — tampilkan di landing tanpa filter show_on_landing
    // supaya setiap template baru yang ditambah admin langsung muncul di landing.
    $data = $this->Templates_model->getAllActive_get();

    // Bungkus dalam format standar agar TemplateShowcase.jsx bisa baca lewat json.data
    $this->output
         ->set_content_type('application/json')
         ->set_output(json_encode(['status' => true, 'message' => 'Success', 'data' => $data]));
}

}