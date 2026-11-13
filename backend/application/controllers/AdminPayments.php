<?php
defined('BASEPATH') OR exit('No direct script access allowed');

class AdminPayments extends CI_Controller {

    public function __construct() {
        parent::__construct();
        $this->load->database();
        $this->load->model('Orders_model');
        if (!$this->session->userdata('admin_logged_in')) {
            redirect('Login');
        }
    }

    public function index() {
        $data['requests'] = $this->db->order_by('created_at', 'DESC')->get('payment_requests')->result();
        $this->load->view('templates/_header');
        $this->load->view('payments', $data);
        $this->load->view('templates/_footer');
    }

    public function approve($id) {
        $r = $this->db->where('id', $id)->get('payment_requests')->row();

        // Hanya request yang masih menunggu yang bisa disetujui,
        // jadi klik dua kali / refresh nggak bikin order dobel.
        if ($r && $r->status === 'pending') {
            $now = date('Y-m-d H:i:s');

            $this->db->where('id', $id)->update('payment_requests', [
                'status'      => 'approved',
                'approved_at' => $now,
            ]);

            $this->_create_order_from_payment($r, $now);
        }

        redirect('AdminPayments');
    }

    public function delete($id) {
        $r = $this->db->where('id', $id)->get('payment_requests')->row();
 
        // Hanya request yang sudah diproses (disetujui / ditolak) yang boleh dihapus.
        // Order di Orderan Masuk tetap ada, yang dihapus cuma baris di daftar pembayaran.
        if ($r && $r->status !== 'pending') {
            $this->db->where('id', $id)->delete('payment_requests');
        }
 
        redirect('AdminPayments');
    }


   public function reject($id)
{
    $pay = $this->db->get_where('payment_requests', ['id' => $id])->row();
    if (!$pay || $pay->status !== 'pending') { redirect('adminpayments'); return; }

    $this->db->where('id', $id)->update('payment_requests', ['status' => 'rejected']);

    $order_code = 'PB-' . $pay->code;
    $exists = $this->db->get_where('orders', ['order_code' => $order_code])->row();  // anti dobel
    if (!$exists) {
        $price = $this->db->get_where('settings', ['id' => 1])->row()->price;
        $this->db->insert('orders', [
            'order_code'     => $order_code,
            'customer_name'  => 'Pengunjung',
            'id_template'    => NULL,
            'package_name'   => 'Sesi Photobooth (Ditolak)',
            'copies'         => 1,
            'total_amount'   => $price,
            'payment_method' => 'QRIS',
            'payment_status' => 'cancelled',
            'order_status'   => 'cancelled',
        ]);
    }
    redirect('adminpayments');
}

    public function settings() {
        if ($this->input->method() === 'post') {
            $price = $this->input->post('price');
            $minutes = $this->input->post('session_minutes');
            $copies = (int)$this->input->post('print_copies');

            $qrisFile = null;
            if (!empty($_FILES['qris_image']['name'])) {
                $filename = 'qris-' . time() . '.png';
                move_uploaded_file($_FILES['qris_image']['tmp_name'], FCPATH . 'uploads/qris/' . $filename);
                $qrisFile = $filename;
            }

            $update = ['price' => $price, 'session_minutes' => $minutes, 'print_copies' => $copies];
            if ($qrisFile) $update['qris_image'] = $qrisFile;

            $this->db->where('id', 1)->update('settings', $update);
            redirect('AdminPayments/settings');
        }

        $data['s'] = $this->db->get('settings')->row();
        $this->load->view('templates/_header');
        $this->load->view('payment_settings', $data);
        $this->load->view('templates/_footer');
    }

    public function cards() {
    $data['cards'] = $this->db->order_by('sort_order', 'ASC')->get('landing_cards')->result();
    $editId = (int)$this->input->get('edit');
    $data['edit'] = $editId ? $this->db->get_where('landing_cards', ['id' => $editId])->row() : null;
    $this->load->view('templates/_header');
    $this->load->view('payment_cards', $data);
    $this->load->view('templates/_footer');
}

public function card_save() {
    if ($this->input->method() !== 'post') { redirect('AdminPayments/cards'); return; }
    $id = (int)$this->input->post('id');
    $row = [
        'icon'        => $this->input->post('icon', TRUE),
        'title'       => $this->input->post('title', TRUE),
        'description' => $this->input->post('description', TRUE),
        'sort_order'  => (int)$this->input->post('sort_order'),
        'is_active'   => $this->input->post('is_active') ? 1 : 0,
    ];

    // hapus gambar lama kalau dicentang
    if ($this->input->post('remove_image')) $row['image'] = NULL;

    // upload gambar baru
    if (!empty($_FILES['image']['name'])) {
        $ext = strtolower(pathinfo($_FILES['image']['name'], PATHINFO_EXTENSION));
        if (in_array($ext, ['jpg', 'jpeg', 'png', 'webp', 'gif'])) {
            $dir = FCPATH . 'uploads/landing/';
            if (!is_dir($dir)) mkdir($dir, 0777, true);
            $filename = 'card-' . time() . '.' . $ext;
            if (move_uploaded_file($_FILES['image']['tmp_name'], $dir . $filename)) {
                $row['image'] = $filename;
            }
        }
    }

    if ($id) $this->db->where('id', $id)->update('landing_cards', $row);
    else     $this->db->insert('landing_cards', $row);
    redirect('AdminPayments/cards');
}

public function card_delete($id) {
    $this->db->where('id', (int)$id)->delete('landing_cards');
    redirect('AdminPayments/cards');
}

    /**
     * Bikin satu order lunas dari pembayaran yang baru disetujui,
     * supaya muncul di Orderan Masuk dan dihitung di Dashboard.
     * Kode order = 'PB-' + kode pembayaran, jadi gampang dilacak balik.
     */
    private function _create_order_from_payment($r, $paidAt) {
        $orderCode = 'PB-' . $r->code;

        // Pengaman: jangan bikin order kalau kodenya sudah ada
        if ($this->db->where('order_code', $orderCode)->count_all_results('orders') > 0) {
            return;
        }

        $s = $this->db->get('settings')->row();
        $minutes = $s ? (int)$s->session_minutes : 0;

        $this->Orders_model->insertOrder([
            'order_code'     => $orderCode,
            'customer_name'  => 'Pengunjung',
            'customer_phone' => '',
            'id_template'    => null,
            'package_name'   => 'Sesi Photobooth' . ($minutes ? " ({$minutes} menit)" : ''),
            'copies'         => 1,
            'total_amount'   => $s ? (int)$s->price : 0,
            'payment_method' => 'QRIS',
            'payment_status' => 'paid',
            'order_status' => 'completed',
            'created_at'     => $paidAt,
            'print_copies' => 1,
        ]);
    }
}