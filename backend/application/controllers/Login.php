<?php
defined('BASEPATH') OR exit('No direct script access allowed');

class Login extends CI_Controller {

    public function __construct() {
        parent::__construct();
        $this->load->database();
    }

    public function index() {
        if ($this->input->method() === 'post') {
            $username = $this->input->post('username');
            $password = $this->input->post('password');

            $user = $this->db->where('username', $username)->get('admin_users')->row();

            if ($user && password_verify($password, $user->password)) {
                $this->session->set_userdata('admin_logged_in', TRUE);
                $this->session->set_userdata('admin_username', $user->username);
                redirect('Admin/templates');
            } else {
                $data['error'] = 'Username atau password salah.';
                $this->load->view('login', $data);
                return;
            }
        }

        $this->load->view('login');
    }

    public function logout() {
        $this->session->sess_destroy();
        redirect('Login');
    }

    // Method sementara buat bikin akun admin pertama - HAPUS setelah dipakai sekali
    public function seed() {
        $data = [
            'username' => 'admin',
            'password' => password_hash('admin123', PASSWORD_DEFAULT),
        ];
        $this->db->insert('admin_users', $data);
        echo 'Admin created: username=admin, password=admin123';
    }
}