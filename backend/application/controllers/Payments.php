<?php
defined('BASEPATH') OR exit('No direct script access allowed');

class Payments extends CI_Controller {

    public function __construct() {
        parent::__construct();
        $this->load->database();
        header('Access-Control-Allow-Origin: *');
        header('Access-Control-Allow-Methods: GET, POST, OPTIONS');
        header('Access-Control-Allow-Headers: Content-Type');
        if ($this->input->method() === 'options') { exit; }
    }

    // Ambil setting QRIS + harga (dipanggil React)
    public function settings() {
        $s = $this->db->get('settings')->row();
        echo json_encode([
            'status' => true,
            'qris_image' => $s->qris_image ? base_url('uploads/qris/' . $s->qris_image) : null,
            'price' => (int)$s->price,
            'session_minutes' => (int)$s->session_minutes,
        ]);
    }

    // Bikin request bayar baru (dipanggil React saat klik "Saya Sudah Transfer")
    public function create() {
        $code = strtoupper(substr(uniqid(), -8));
        $this->db->insert('payment_requests', ['code' => $code, 'status' => 'pending']);
        echo json_encode(['status' => true, 'code' => $code]);
    }

        public function info()
        {
            header('Access-Control-Allow-Origin: *');
            $s = $this->db->get_where('settings', ['id' => 1])->row();
            $cards = $this->db->where('is_active', 1)->order_by('sort_order', 'ASC')->get('landing_cards')->result();

            foreach ($cards as $c) {
                $c->image = !empty($c->image) ? base_url('uploads/landing/' . $c->image) : null;
            }

            $this->output->set_content_type('application/json')->set_output(json_encode([
                'status' => true,
                'data' => [
                    'price'           => (int)$s->price,
                    'session_minutes' => (int)$s->session_minutes,
                    'print_copies'    => (int)$s->print_copies,
                    'cards'           => $cards,
                ],
            ]));
        }

    // Cek status (dipanggil React berkala / polling)
    public function status($code) {
        $r = $this->db->where('code', $code)->get('payment_requests')->row();
        if (!$r) {
            echo json_encode(['status' => false, 'message' => 'Not found']);
            return;
        }
        $settings = $this->db->get('settings')->row();
        echo json_encode([
            'status' => true,
            'payment_status' => $r->status,
            'approved_at' => $r->approved_at,
            'session_minutes' => (int)$settings->session_minutes,
        ]);
    }
}