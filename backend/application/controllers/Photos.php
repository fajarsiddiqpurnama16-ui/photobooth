<?php
defined('BASEPATH') OR exit('No direct script access allowed');

class Photos extends CI_Controller {

    public $drive_debug = null;
    private $drive_folder_url = null;

    public function __construct() {
        parent::__construct();
        $this->load->database();
    }

       public function save() {
    set_time_limit(300);

    header('Access-Control-Allow-Origin: *');
    header('Access-Control-Allow-Methods: POST, OPTIONS');
    header('Access-Control-Allow-Headers: Content-Type');

    if ($this->input->method() === 'options') {
        return;
    }

    $raw = json_decode(file_get_contents('php://input'), true);

    if (empty($raw['image']) || empty($raw['template_slug'])) {
        echo json_encode(['status' => false, 'message' => 'Data tidak lengkap']);
        return;
    }

    $base64 = explode(',', $raw['image'])[1] ?? $raw['image'];
    $imageData = base64_decode($base64);

    $filename = $raw['template_slug'] . '-' . time() . '-' . uniqid() . '.png';
    $path = FCPATH . 'uploads/photos/' . $filename;

    if (!is_dir(FCPATH . 'uploads/photos/')) {
        mkdir(FCPATH . 'uploads/photos/', 0777, true);
    }

    file_put_contents($path, $imageData);

    $this->db->insert('photos', [
        'filename'      => $filename,
        'template_slug' => $raw['template_slug'],
    ]);

    $nama = strtolower(trim($raw['customer_name'] ?? ''));
    $nama = trim(preg_replace('/[^a-z0-9]+/', '-', $nama), '-');
    $nama = substr($nama, 0, 20) ?: 'tamu';

    $stamp      = pathinfo($filename, PATHINFO_FILENAME);
    $folderName = $nama . '-' . date('Ymd-His') . '-' . substr(uniqid(), -4);

    $driveUrl  = $this->upload_to_drive($path, $nama . '-hasil.png', $folderName);
    $folderUrl = $this->drive_folder_url;
    $mainDebug = $this->drive_debug;

    $rawCount = 0;
    if (!empty($raw['raw_photos']) && is_array($raw['raw_photos'])) {
        $rawDir = FCPATH . 'uploads/photos_raw/';
        if (!is_dir($rawDir)) {
            mkdir($rawDir, 0777, true);
        }
        foreach (array_slice($raw['raw_photos'], 0, 12) as $i => $p) {
            if (!is_string($p) || strpos($p, 'data:image/') !== 0) continue;
            $bin = base64_decode(explode(',', $p)[1] ?? '');
            if (!$bin) continue;

            $rawName = $stamp . '-mentah-' . ($i + 1) . '.jpg';
            file_put_contents($rawDir . $rawName, $bin);
           if ($this->upload_to_drive($rawDir . $rawName, $nama . '-mentah-' . ($i + 1) . '.jpg', $folderName)) {
                $rawCount++;
            }
        }
    }

    if ($driveUrl && preg_match('~/d/([^/]+)~', $driveUrl, $m)) {
        $driveUrl = 'https://drive.google.com/uc?export=download&id=' . $m[1];
    }

    echo json_encode([
        'status'       => true,
        'filename'     => $filename,
        'drive_url'    => $driveUrl,
        'folder_url'   => $folderUrl,
        'raw_uploaded' => $rawCount,
        'drive_debug'  => $mainDebug,
    ]);
}
    public function gallery() {
        $data['photos'] = $this->db->order_by('created_at', 'DESC')->get('photos')->result();
        $this->load->view('templates/_header');
        $this->load->view('gallery', $data);
        $this->load->view('templates/_footer');
    }
    
    public function delete($id) {
        $photo = $this->db->where('id', $id)->get('photos')->row();
        if ($photo) {
            $path = FCPATH . 'uploads/photos/' . $photo->filename;
            if (file_exists($path)) unlink($path);
            $this->db->where('id', $id)->delete('photos');
        }
        redirect('index.php/Photos/');
    }

        private function make_drive_jpeg($path)
    {
        if (!function_exists('imagecreatefromstring')) return null;

        $img = @imagecreatefromstring(file_get_contents($path));
        if (!$img) return null;

        // Kecilkan kalau sisi terpanjang lebih dari 2000 px
        $w = imagesx($img);
        $h = imagesy($img);
        $max = 2000;
        if (max($w, $h) > $max) {
            $ratio  = $max / max($w, $h);
            $scaled = imagescale($img, (int)round($w * $ratio), (int)round($h * $ratio));
            imagedestroy($img);
            $img = $scaled;
            $w = imagesx($img);
            $h = imagesy($img);
        }

        // Latar putih supaya bagian transparan tidak jadi hitam di JPEG
        $bg = imagecreatetruecolor($w, $h);
        imagefill($bg, 0, 0, imagecolorallocate($bg, 255, 255, 255));
        imagecopy($bg, $img, 0, 0, 0, 0, $w, $h);
        imagedestroy($img);

        ob_start();
        imagejpeg($bg, null, 88);
        $data = ob_get_clean();
        imagedestroy($bg);

        return $data;
    }

        private function upload_to_drive($path, $filename, $folder = null)
        {
            $url = $this->config->item('gdrive_url');
            if (!$url || !file_exists($path)) return null;

            $this->drive_folder_url = null;

            $isJpg = (bool) preg_match('/\.jpe?g$/i', $filename);
            $jpeg  = $isJpg ? null : $this->make_drive_jpeg($path);
            if ($jpeg !== null) {
                $binary = $jpeg;
                $name   = preg_replace('/\.png$/i', '.jpg', $filename);
                $mime   = 'image/jpeg';
            } else {
                $binary = file_get_contents($path);
                $name   = $filename;
                $mime   = $isJpg ? 'image/jpeg' : 'image/png';
            }

            $body = json_encode([
                'secret'   => $this->config->item('gdrive_secret'),
                'filename' => $name,
                'mime'     => $mime,
                'folder'   => $folder,
                'image'    => base64_encode($binary),
            ]);

            $driveUrl = null;
            for ($try = 0; $try < 2; $try++) {
                $ch = curl_init($url);
                curl_setopt_array($ch, [
                    CURLOPT_POST           => true,
                    CURLOPT_POSTFIELDS     => $body,
                    CURLOPT_HTTPHEADER     => ['Content-Type: text/plain'],
                    CURLOPT_RETURNTRANSFER => true,
                    CURLOPT_FOLLOWLOCATION => true,
                    CURLOPT_CONNECTTIMEOUT => 10,
                    CURLOPT_TIMEOUT        => 25,
                ]);
                $res = curl_exec($ch);

                $this->drive_debug = [
                    'try'        => $try + 1,
                    'curl_error' => curl_error($ch),
                    'http'       => curl_getinfo($ch, CURLINFO_HTTP_CODE),
                    'body'       => substr((string)$res, 0, 300),
                ];

                if ($res === false) {
                    log_message('error', 'Drive upload: ' . curl_error($ch));
                }
                curl_close($ch);

                $json = $res ? json_decode($res, true) : null;
                if (!empty($json['url'])) {
                    $driveUrl = $json['url'];
                    if (!empty($json['folder_url'])) {
                        $this->drive_folder_url = $json['folder_url'];
                    }
                    break;
                }
            }

            return $driveUrl;
        }

}