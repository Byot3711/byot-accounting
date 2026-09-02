<?php
if (!defined('ABSPATH')) {
    exit;
}

class BYOT_Acc_Ajax_Handler {
    public static function init() {
        add_action('wp_ajax_byot_get_chart_data', array(__CLASS__, 'get_chart_data'));
    }

    public static function get_chart_data() {
        check_ajax_referer('byot_nonce', 'nonce');
        if (!current_user_can('manage_woocommerce')) {
            wp_send_json_error('Unauthorized');
        }

        $months = array();
        $sales_data = array();
        $expense_data = array();
        $purchase_data = array();

        for ($i = 11; $i >= 0; $i--) {
            $dt = date('Y-m-01', strtotime("-{$i} months"));
            $months[] = date_i18n('M Y', strtotime($dt));
            $start = date('Y-m-01', strtotime("-{$i} months"));
            $end = date('Y-m-t', strtotime("-{$i} months"));

            $sales_data[] = self::get_wc_sales_between($start, $end);
            $expense_data[] = self::get_expenses_between($start, $end);
            $purchase_data[] = self::get_purchases_between($start, $end);
        }

        wp_send_json_success(array(
            'labels' => $months,
            'sales' => $sales_data,
            'expenses' => $expense_data,
            'purchases' => $purchase_data,
        ));
    }

    private static function get_wc_sales_between($start, $end) {
        $orders = wc_get_orders(array(
            'status' => array('completed', 'processing'),
            'limit' => -1,
            'date_created' => $start . '...' . $end,
            'return' => 'ids',
        ));
        $total = 0;
        foreach ($orders as $order_id) {
            $order = wc_get_order($order_id);
            if ($order) {
                $total += (float) $order->get_total();
            }
        }
        return $total;
    }

    private static function get_expenses_between($start, $end) {
        global $wpdb;
        $table = $wpdb->prefix . 'byot_expenses';
        $sql = $wpdb->prepare("SELECT SUM(amount) FROM {$table} WHERE expense_date BETWEEN %s AND %s", $start, $end);
        return (float) $wpdb->get_var($sql);
    }

    private static function get_purchases_between($start, $end) {
        global $wpdb;
        $table = $wpdb->prefix . 'byot_purchases';
        $sql = $wpdb->prepare("SELECT SUM(total_amount) FROM {$table} WHERE purchase_date BETWEEN %s AND %s", $start, $end);
        return (float) $wpdb->get_var($sql);
    }
}