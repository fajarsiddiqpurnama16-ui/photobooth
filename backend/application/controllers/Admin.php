<?php
defined('BASEPATH') OR exit('No direct script access allowed');

class Admin extends CI_Controller {

    public function __construct() {
        parent::__construct();
        $this->load->model('Templates_model');
        $this->load->model('Orders_model');

        if (!$this->session->userdata('admin_logged_in')) {
            redirect('index.php/Login');
        }
    }

    public function templates() {
        $data['templates'] = $this->Templates_model->getAll();
        $data['page_title'] = 'Kelola Template Photobooth';
        $this->load->view('templates/_header', $data);
        $this->load->view('templates/index', $data);
        $this->load->view('templates/_footer');
    }

    private function _handleTemplateUpload($existingFilename = null) {
        $uploadDir = FCPATH . 'uploads/templates/';
        if (!is_dir($uploadDir)) {
            @mkdir($uploadDir, 0777, true);
        }

        // 1. Standard file upload
        if (!empty($_FILES['template_image']['name']) && $_FILES['template_image']['error'] === UPLOAD_ERR_OK) {
            $ext = strtolower(pathinfo($_FILES['template_image']['name'], PATHINFO_EXTENSION));
            $allowed = ['png', 'jpg', 'jpeg', 'webp', 'svg'];
            if (in_array($ext, $allowed)) {
                $newFilename = md5(uniqid(rand(), true)) . '.' . $ext;
                $targetPath = $uploadDir . $newFilename;
                if (move_uploaded_file($_FILES['template_image']['tmp_name'], $targetPath)) {
                    if ($existingFilename && $existingFilename !== $newFilename && file_exists($uploadDir . $existingFilename)) {
                        @unlink($uploadDir . $existingFilename);
                    }
                    return $newFilename;
                }
            }
        }

        // 2. Base64 fallback if image was selected via FileReader in browser
        $base64 = $this->input->post('template_image_base64');
        if (!empty($base64) && strpos($base64, 'data:image') === 0) {
            $parts = explode(',', $base64);
            if (count($parts) >= 2) {
                $mimePart = $parts[0];
                $dataPart = base64_decode($parts[1]);
                if ($dataPart !== false && strlen($dataPart) > 0) {
                    $ext = 'png';
                    if (strpos($mimePart, 'image/jpeg') !== false || strpos($mimePart, 'image/jpg') !== false) {
                        $ext = 'jpg';
                    } elseif (strpos($mimePart, 'image/webp') !== false) {
                        $ext = 'webp';
                    }
                    $newFilename = md5(uniqid(rand(), true)) . '.' . $ext;
                    $targetPath = $uploadDir . $newFilename;
                    if (file_put_contents($targetPath, $dataPart)) {
                        if ($existingFilename && $existingFilename !== $newFilename && file_exists($uploadDir . $existingFilename)) {
                            @unlink($uploadDir . $existingFilename);
                        }
                        return $newFilename;
                    }
                }
            }
        }

        return $existingFilename;
    }

    public function add() {
        $error = null;

        if ($this->input->method() === 'post') {
            $slug = $this->input->post('slug');

            if ($this->Templates_model->slugExists($slug)) {
                $error = "Slug '$slug' sudah dipakai template lain. Gunakan slug yang berbeda.";
            } else {
                $templateImage = $this->_handleTemplateUpload();

                $data = [
                    'slug'           => $slug,
                    'name'           => $this->input->post('name'),
                    'category'       => $this->input->post('category'),
                    'template_image' => $templateImage,
                    'width'          => $this->input->post('width'),
                    'height'         => $this->input->post('height'),
                    'photo_count'    => $this->input->post('photo_count'),
                    'background'     => $this->input->post('background'),
                    'photo_slots'    => $this->input->post('photo_slots'),
                    'decorations'    => $this->input->post('decorations') ?: null,
                    'text_config'    => $this->input->post('text_config') ?: null,
                    'border_config'  => $this->input->post('border_config') ?: null,
                    'photo_border'   => $this->input->post('photo_border') ?: null,
                    'accent'         => $this->input->post('accent') ?: null,
                    'is_active'      => $this->input->post('is_active') ? 1 : 0,
                ];

                $this->Templates_model->insert($data);
                redirect('Admin/templates');
                return;
            }
        }

        $data['error'] = $error;
        $this->load->view('templates/_header');
        $this->load->view('templates/form', $data);
        $this->load->view('templates/_footer');
    }

    public function edit($id) {
        $error = null;
        $existing = $this->Templates_model->getById($id);

        if (!$existing) {
            redirect('Admin/templates');
            return;
        }

        if ($this->input->method() === 'post') {
            $slug = $this->input->post('slug');

            if ($this->Templates_model->slugExists($slug, $id)) {
                $error = "Slug '$slug' sudah dipakai template lain. Gunakan slug yang berbeda.";
            } else {
                $templateImage = $this->_handleTemplateUpload($existing->template_image);

                $data = [
                    'slug'           => $slug,
                    'name'           => $this->input->post('name'),
                    'category'       => $this->input->post('category'),
                    'template_image' => $templateImage,
                    'width'          => $this->input->post('width'),
                    'height'         => $this->input->post('height'),
                    'photo_count'    => $this->input->post('photo_count'),
                    'background'     => $this->input->post('background'),
                    'photo_slots'    => $this->input->post('photo_slots'),
                    'decorations'    => $this->input->post('decorations') ?: null,
                    'text_config'    => $this->input->post('text_config') ?: null,
                    'border_config'  => $this->input->post('border_config') ?: null,
                    'photo_border'   => $this->input->post('photo_border') ?: null,
                    'accent'         => $this->input->post('accent') ?: null,
                    'is_active'      => $this->input->post('is_active') ? 1 : 0,
                ];

                $this->Templates_model->update($id, $data);
                redirect('Admin/templates');
                return;
            }
        }

        $data['t'] = $existing;
        $data['error'] = $error;
        $this->load->view('templates/_header');
        $this->load->view('templates/form', $data);
        $this->load->view('templates/_footer');
    }

    public function delete($id) {
        $existing = $this->Templates_model->getById($id);
        if ($existing && !empty($existing->template_image)) {
            $filePath = FCPATH . 'uploads/templates/' . $existing->template_image;
            if (file_exists($filePath)) {
                @unlink($filePath);
            }
        }
        $this->Templates_model->delete($id);
        redirect('Admin/templates');
    }

    public function delete_order($id) {
    $this->Orders_model->deleteOrder($id);
    redirect('Admin/orders');   // balik ke daftar orderan
}

    public function toggle_active($id) {
        $existing = $this->Templates_model->getById($id);
        if ($existing) {
            $newStatus = $existing->is_active ? 0 : 1;
            $this->Templates_model->update($id, ['is_active' => $newStatus]);
        }
        redirect('Admin/templates');
    }

    public function dashboard() {
        $all = $this->Templates_model->getAll();
        $data['total'] = count($all);
        $data['active'] = count(array_filter($all, fn($t) => $t->is_active));
        $data['inactive'] = $data['total'] - $data['active'];

        // Orders & revenue analytics
        $data['summary'] = $this->Orders_model->getSummary();
        $data['trend_7d'] = $this->Orders_model->getDailyTrend(7);
        $data['trend_14d'] = $this->Orders_model->getDailyTrend(14);
        $data['top_templates'] = $this->Orders_model->getTopTemplates(5);
        $data['payment_methods'] = $this->Orders_model->getPaymentMethodBreakdown();
        $data['recent_orders'] = $this->Orders_model->getRecentOrders(8);
        $data['page_title'] = 'Dashboard Analisis & Pendapatan';

        
        $this->load->view('dashboard', $data);
        
    }

    public function orders() {
        $data['orders'] = $this->Orders_model->getAllOrders(100);
        $data['summary'] = $this->Orders_model->getSummary();
        $data['page_title'] = 'Daftar Orderan Masuk';

        $this->load->view('templates/_header', $data);
        $this->load->view('orders/index', $data);
        $this->load->view('templates/_footer');
    }

    public function update_order_status($id) {
        $status = $this->input->post('order_status') ?: $this->input->get('status');
        $payment = $this->input->post('payment_status') ?: ($status === 'completed' ? 'paid' : null);
        if ($status) {
            $this->Orders_model->updateOrderStatus($id, $status, $payment);
        }
        
        $redirect = $this->input->get('redirect') ?: 'Admin/dashboard';
        redirect($redirect);
    }

    public function add_mock_order() {
        $templates = $this->Templates_model->getAll();
        $randomTemplate = !empty($templates) ? $templates[array_rand($templates)] : null;
        
        $names = ['Siti Rahma', 'Bintang Pratama', 'Jessica Putri', 'Rifqi Alamsyah', 'Anindya Kusuma', 'Rian Hidayat', 'Natasha Wilona'];
        $packages = [
            ['name' => 'Classic Strip (2 Cetak)', 'price' => 45000],
            ['name' => 'Classic 4 Strip + Softfile', 'price' => 55000],
            ['name' => 'Vintage Retro Package', 'price' => 75000],
            ['name' => 'Collage 4 Grid Strip', 'price' => 50000],
            ['name' => 'Polaroid Single Print', 'price' => 40000]
        ];
        $methods = ['QRIS', 'Transfer BCA', 'GoPay', 'ShopeePay', 'Dana', 'Tunai'];
        
        $chosenPkg = $packages[array_rand($packages)];
        $data = [
            'order_code'     => 'PB-' . date('Ymd') . '-' . rand(100, 999),
            'customer_name'  => $names[array_rand($names)],
            'customer_phone' => '08' . rand(1111111111, 9999999999),
            'id_template'    => $randomTemplate ? $randomTemplate->id_template : 1,
            'package_name'   => $chosenPkg['name'],
            'copies'         => rand(2, 4),
            'total_amount'   => $chosenPkg['price'],
            'payment_method' => $methods[array_rand($methods)],
            'payment_status' => 'paid',
            'order_status'   => 'completed',
            'created_at'     => date('Y-m-d H:i:s')
        ];
        
        $this->Orders_model->insertOrder($data);
        redirect('index.php/Admin/dashboard');
    }
}