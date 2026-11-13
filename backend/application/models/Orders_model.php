<?php
defined('BASEPATH') OR exit('No direct script access allowed');

class Orders_model extends CI_Model {

    public function __construct() {
        parent::__construct();
        $this->load->database();
    }

    public function getSummary() {
        $today = date('Y-m-d');
        $thisMonth = date('Y-m');

        // Total orders & revenue
        $totalOrders = $this->db->count_all('orders');
        
        $revQuery = $this->db->select_sum('total_amount')
                             ->where('payment_status', 'paid')
                             ->get('orders')
                             ->row();
        $totalRevenue = $revQuery ? (int)$revQuery->total_amount : 0;

        // Today's orders & revenue
        $todayOrders = $this->db->where("DATE(created_at) = '{$today}'")->count_all_results('orders');
        
        $todayRevQuery = $this->db->select_sum('total_amount')
                                  ->where('payment_status', 'paid')
                                  ->where("DATE(created_at) = '{$today}'")
                                  ->get('orders')
                                  ->row();
        $todayRevenue = $todayRevQuery ? (int)$todayRevQuery->total_amount : 0;

        // This month's orders & revenue
        $monthOrders = $this->db->where("DATE_FORMAT(created_at, '%Y-%m') = '{$thisMonth}'")->count_all_results('orders');

        $monthRevQuery = $this->db->select_sum('total_amount')
                                  ->where('payment_status', 'paid')
                                  ->where("DATE_FORMAT(created_at, '%Y-%m') = '{$thisMonth}'")
                                  ->get('orders')
                                  ->row();
        $monthRevenue = $monthRevQuery ? (int)$monthRevQuery->total_amount : 0;

        // Order status breakdown
        $pending = $this->db->where('order_status', 'pending')->count_all_results('orders');
        $processing = $this->db->where('order_status', 'processing')->count_all_results('orders');
        $completed = $this->db->where('order_status', 'completed')->count_all_results('orders');

        // Average Order Value
        $paidCount = $this->db->where('payment_status', 'paid')->count_all_results('orders');
        $aov = $paidCount > 0 ? (int)($totalRevenue / $paidCount) : 0;

        return [
            'total_orders'       => $totalOrders,
            'total_revenue'      => $totalRevenue,
            'today_orders'       => $todayOrders,
            'today_revenue'      => $todayRevenue,
            'month_orders'       => $monthOrders,
            'month_revenue'      => $monthRevenue,
            'status_pending'     => $pending,
            'status_processing'  => $processing,
            'status_completed'   => $completed,
            'paid_orders_count'  => $paidCount,
            'average_order_val'  => $aov,
        ];
    }

    public function getDailyTrend($days = 7) {
        $labels = [];
        $revenues = [];
        $ordersCount = [];

        for ($i = $days - 1; $i >= 0; $i--) {
            $date = date('Y-m-d', strtotime("-{$i} days"));
            $displayDate = date('d M', strtotime($date));
            $labels[] = $displayDate;

            // revenue for this date
            $revRow = $this->db->select_sum('total_amount')
                               ->where('payment_status', 'paid')
                               ->where("DATE(created_at) = '{$date}'")
                               ->get('orders')
                               ->row();
            $revenues[] = $revRow && $revRow->total_amount ? (int)$revRow->total_amount : 0;

            // count for this date
            $cnt = $this->db->where("DATE(created_at) = '{$date}'")->count_all_results('orders');
            $ordersCount[] = (int)$cnt;
        }

        return [
            'labels'   => $labels,
            'revenues' => $revenues,
            'orders'   => $ordersCount
        ];
    }

    public function getTopTemplates($limit = 5) {
        $query = $this->db->select('t.id_template, t.name, t.category, t.slug, COUNT(o.id_order) as total_sold, SUM(o.total_amount) as total_generated')
                          ->from('templates t')
                          ->join('orders o', 'o.id_template = t.id_template', 'left')
                          ->where('o.payment_status', 'paid')
                          ->group_by('t.id_template')
                          ->order_by('total_sold', 'DESC')
                          ->limit($limit)
                          ->get();
        return $query->result();
    }

    public function getPaymentMethodBreakdown() {
        $query = $this->db->select('payment_method, COUNT(*) as count, SUM(total_amount) as revenue')
                          ->where('payment_status', 'paid')
                          ->group_by('payment_method')
                          ->order_by('count', 'DESC')
                          ->get('orders');
        return $query->result();
    }

    public function getRecentOrders($limit = 10) {
        $this->db->select('o.*, t.name as template_name, t.slug as template_slug')
                 ->from('orders o')
                 ->join('templates t', 't.id_template = o.id_template', 'left')
                 ->order_by('o.created_at', 'DESC')
                 ->limit($limit);
        return $this->db->get()->result();
    }

    public function getAllOrders($limit = 100, $offset = 0) {
        $this->db->select('o.*, t.name as template_name, t.slug as template_slug')
                 ->from('orders o')
                 ->join('templates t', 't.id_template = o.id_template', 'left')
                 ->order_by('o.created_at', 'DESC')
                 ->limit($limit, $offset);
        return $this->db->get()->result();
    }

    public function updateOrderStatus($id, $order_status, $payment_status = null) {
        $data = ['order_status' => $order_status];
        if ($payment_status !== null) {
            $data['payment_status'] = $payment_status;
        }
        $this->db->where('id_order', $id)->update('orders', $data);
        return $this->db->affected_rows() > 0;
    }

    public function insertOrder($data) {
        if (empty($data['order_code'])) {
            $data['order_code'] = 'PB-' . date('Ymd') . '-' . rand(100, 999);
        }
        $this->db->insert('orders', $data);
        return $this->db->insert_id();
    }


    public function deleteOrder($id) {
    $this->db->where('id_order', $id)->delete('orders');
    return $this->db->affected_rows() > 0;
}


}
