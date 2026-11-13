<?php
defined('BASEPATH') OR exit('No direct script access allowed');

class Admin_effects extends CI_Controller {

    const DIR = 'uploads/effects/';

    public function __construct() {
        parent::__construct();
        $this->load->model('Effects_model');
        if (!$this->session->userdata('admin_logged_in')) {
            redirect('Login');
        }
    }

    public function index() {
        $data['effects'] = $this->Effects_model->getAll();
        $data['page_title'] = 'Kelola Efek Wajah';
        $data['nav_active'] = 'effects';
        $this->load->view('templates/_header', $data);
        $this->load->view('effects/index', $data);
        $this->load->view('templates/_footer');
    }

    public function add() {
        $this->_form(null);
    }

    public function edit($id = 0) {
        $fx = $this->Effects_model->getById($id);
        if (!$fx) {
            redirect('Admin_effects');
            return;
        }
        $this->_form($fx);
    }

    public function delete($id = 0) {
        $fx = $this->Effects_model->getById($id);
        if ($fx) {
            foreach ($this->_files($fx->layers) as $f) {
                @unlink(FCPATH . self::DIR . $f);
            }
            $this->Effects_model->delete($id);
        }
        redirect('Admin_effects');
    }

    public function toggle($id = 0) {
        $fx = $this->Effects_model->getById($id);
        if ($fx) {
            $this->Effects_model->update($id, ['is_active' => $fx->is_active ? 0 : 1]);
        }
        redirect('Admin_effects');
    }

    /** Upload satu gambar lapisan (AJAX). Mengembalikan JSON. */
    public function upload() {
        $this->output->set_content_type('application/json');
        $fail = function ($msg) {
            $this->output->set_status_header(400)->set_output(json_encode([
                'ok' => false, 'message' => $msg, 'csrf' => $this->security->get_csrf_hash(),
            ]));
        };

        if ($this->input->method() !== 'post' || empty($_FILES['image']['name'])) {
            return $fail('Pilih file gambar.');
        }
        $f = $_FILES['image'];
        if ($f['error'] !== UPLOAD_ERR_OK) {
            return $fail('Upload gagal (kode ' . $f['error'] . ').');
        }
        if ($f['size'] > 2 * 1024 * 1024) {
            return $fail('Ukuran maksimal 2 MB.');
        }
        $info = @getimagesize($f['tmp_name']);
        $types = [IMAGETYPE_PNG => 'png', IMAGETYPE_WEBP => 'webp', IMAGETYPE_GIF => 'gif'];
        if (!$info || !isset($types[$info[2]])) {
            return $fail('Format harus PNG, WebP, atau GIF (disarankan PNG transparan).');
        }

        $dir = FCPATH . self::DIR;
        if (!is_dir($dir)) {
            @mkdir($dir, 0777, true);
        }
        $name = md5(uniqid(mt_rand(), true)) . '.' . $types[$info[2]];
        if (!move_uploaded_file($f['tmp_name'], $dir . $name)) {
            return $fail('Gagal menyimpan file di server.');
        }

        $this->output->set_output(json_encode([
            'ok'   => true,
            'file' => $name,
            'url'  => site_url('Effects/img/' . $name),
            'csrf' => $this->security->get_csrf_hash(),
        ]));
    }

    /* ------------------------------------------------------------ */

    private function _form($fx) {
        $error = null;
        $layers = $fx ? (json_decode($fx->layers, true) ?: []) : [];

        if ($this->input->method() === 'post') {
            $row = $this->_collect();
            $layers = json_decode($row['layers'], true);
            if (empty($layers)) {
                $error = 'Tambahkan minimal satu lapisan gambar.';
            } else {
                if ($fx) {
                    $this->Effects_model->update($fx->id_effect, $row);
                    // hapus file lapisan yang sudah tidak dipakai
                    $unused = array_diff($this->_files($fx->layers), $this->_files($row['layers']));
                    foreach ($unused as $f) {
                        @unlink(FCPATH . self::DIR . $f);
                    }
                } else {
                    $this->Effects_model->insert($row);
                }
                redirect('Admin_effects');
                return;
            }
        }

        foreach ($layers as &$l) {
            $l['url'] = site_url('Effects/img/' . rawurlencode(isset($l['image']) ? $l['image'] : ''));
        }
        unset($l);

        $data = [
            'fx'          => $fx,
            'error'       => $error,
            'layers_json' => json_encode($layers, JSON_HEX_TAG | JSON_HEX_AMP | JSON_HEX_APOS | JSON_HEX_QUOT),
            'csrf'        => [
                'name' => $this->security->get_csrf_token_name(),
                'hash' => $this->security->get_csrf_hash(),
            ],
            'posted'      => $this->input->method() === 'post' ? [
                'name'       => $this->input->post('name'),
                'icon'       => $this->input->post('icon'),
                'sort_order' => $this->input->post('sort_order'),
                'is_active'  => $this->input->post('is_active'),
            ] : null,
            'page_title'  => $fx ? 'Edit Efek Wajah' : 'Efek Wajah Baru',
        ];
        $this->load->view('templates/_header', $data);
        $this->load->view('effects/form', $data);
        $this->load->view('templates/_footer');
    }

    /** Ambil & bersihkan data dari form. Semua angka dibatasi supaya aman. */
    private function _collect() {
        $raw = json_decode((string) $this->input->post('layers'), true);
        $clean = [];
        foreach ((is_array($raw) ? $raw : []) as $l) {
            $img = isset($l['image']) ? basename((string) $l['image']) : '';
            if (!preg_match('/^[a-f0-9]{32}\.(png|webp|gif)$/', $img)) {
                continue;
            }
            $anchor = isset($l['anchor']) ? (string) $l['anchor'] : '';
            $clean[] = [
                'image'   => $img,
                'anchor'  => preg_match('/^[a-z-]{3,20}$/', $anchor) ? $anchor : 'kepala-atas',
                'size'    => $this->_num($l, 'size', 0.4, 0.02, 3),
                'dx'      => $this->_num($l, 'dx', 0, -3, 3),
                'dy'      => $this->_num($l, 'dy', 0, -3, 3),
                'rot'     => $this->_num($l, 'rot', 0, -180, 180),
                'opacity' => $this->_num($l, 'opacity', 1, 0, 1),
                'flip'    => !empty($l['flip']),
                'tilt'    => !isset($l['tilt']) || !empty($l['tilt']),
            ];
        }

        $name = trim((string) $this->input->post('name'));
        $icon = trim((string) $this->input->post('icon'));
        return [
            'name'       => mb_substr($name !== '' ? $name : 'Efek Baru', 0, 60),
            'icon'       => mb_substr($icon !== '' ? $icon : '✨', 0, 4),
            'layers'     => json_encode($clean),
            'sort_order' => (int) $this->input->post('sort_order'),
            'is_active'  => $this->input->post('is_active') ? 1 : 0,
        ];
    }

    private function _num($arr, $key, $default, $min, $max) {
        $v = isset($arr[$key]) ? (float) $arr[$key] : $default;
        return round(max($min, min($max, $v)), 3);
    }

    /** Daftar nama file dari JSON lapisan. */
    private function _files($layersJson) {
        $out = [];
        foreach ((json_decode($layersJson, true) ?: []) as $l) {
            if (!empty($l['image'])) {
                $out[] = basename($l['image']);
            }
        }
        return $out;
    }
}