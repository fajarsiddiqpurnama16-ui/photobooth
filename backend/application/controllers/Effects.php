<?php
defined('BASEPATH') OR exit('No direct script access allowed');

/**
 * API publik untuk photobooth (tanpa login):
 *   GET  index.php/Effects             -> daftar efek aktif (JSON)
 *   GET  index.php/Effects/img/xxx.png -> gambar lapisan (dengan header CORS)
 */
class Effects extends CI_Controller {

    public function __construct() {
        parent::__construct();
        $this->load->model('Effects_model');
        header('Access-Control-Allow-Origin: *');
    }

    public function index() {
        $out = [];
        foreach ($this->Effects_model->getAll(true) as $e) {
            $layers = json_decode($e->layers, true);
            if (!is_array($layers)) {
                continue;
            }
            foreach ($layers as &$l) {
                $l['image_url'] = site_url('Effects/img/' . rawurlencode(isset($l['image']) ? $l['image'] : ''));
            }
            unset($l);
            $out[] = [
                'id'     => (int) $e->id_effect,
                'name'   => $e->name,
                'icon'   => $e->icon,
                'layers' => $layers,
            ];
        }
        $this->output
            ->set_content_type('application/json')
            ->set_output(json_encode(['effects' => $out]));
    }

    public function img($file = '') {
        $file = basename(urldecode($file));
        if (!preg_match('/^[a-f0-9]{32}\.(png|webp|gif)$/', $file)) {
            show_404();
        }
        $path = FCPATH . 'uploads/effects/' . $file;
        if (!is_file($path)) {
            show_404();
        }
        $mimes = ['png' => 'image/png', 'webp' => 'image/webp', 'gif' => 'image/gif'];
        header('Content-Type: ' . $mimes[pathinfo($file, PATHINFO_EXTENSION)]);
        header('Content-Length: ' . filesize($path));
        header('Cache-Control: public, max-age=86400');
        readfile($path);
        exit;
    }
}