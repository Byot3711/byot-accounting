<?php
if (!defined('ABSPATH')) {
    exit;
}

class BYOT_Acc_Dashboard {
    public static function render() {
        if (!current_user_can('manage_woocommerce')) {
            wp_die(esc_html__('Nu ai permisiunea.', 'byot-accounting'));
        }
        ?>
        <div class="wrap byot-wrap">
            <h1><?php echo esc_html(get_admin_page_title()); ?></h1>

            <div class="byot-cards">
                <div class="byot-card">
                    <h3><?php esc_html_e('Vanzari (An Curent)', 'byot-accounting'); ?></h3>
                    <p class="byot-big"><?php echo wp_kses_post(wc_price(self::get_current_year_sales())); ?></p>
                </div>
                <div class="byot-card">
                    <h3><?php esc_html_e('Cheltuieli (An Curent)', 'byot-accounting'); ?></h3>
                    <p class="byot-big"><?php echo wp_kses_post(wc_price(self::get_current_year_expenses())); ?></p>
                </div>
                <div class="byot-card">
                    <h3><?php esc_html_e('Achizitii (An Curent)', 'byot-accounting'); ?></h3>
                    <p class="byot-big"><?php echo wp_kses_post(wc_price(self::get_current_year_purchases())); ?></p>
                </div>
                <div class="byot-card">
                    <h3><?php esc_html_e('Profit Estimat', 'byot-accounting'); ?></h3>
                    <p class="byot-big"><?php echo wp_kses_post(wc_price(self::get_current_year_profit())); ?></p>
                </div>
            </div>

            <div class="byot-chart-box">
                <h2><?php esc_html_e('Evolutie Financiara', 'byot-accounting'); ?></h2>
                <canvas id="byotMainChart" height="120"></canvas>
            </div>

            <div class="byot-recent">
                <div class="byot-half">
                    <h2><?php esc_html_e('Ultimele Cheltuieli', 'byot-accounting'); ?></h2>
                    <?php self::render_recent_expenses(); ?>
                </div>
                <div class="byot-half">
                    <h2><?php esc_html_e('Ultimele Achizitii', 'byot-accounting'); ?></h2>
                    <?php self::render_recent_purchases(); ?>
                </div>
            </div>
        </div>
        <?php
    }

    private static function get_current_year_sales() {
        $start = date('Y-01-01');
        $end = date('Y-12-31');
        $orders = wc_get_orders(array(
            'status' => array('completed', 'processing'),
            'limit' => -1,
            'date_created' => $start . '...' . $end,
            'return' => 'ids',
        ));
        $total = 0;
        foreach ($orders as $oid) {
            $order = wc_get_order($oid);
            if ($order) {
                $total += (float) $order->get_total();
            }
        }
        return $total;
    }

    private static function get_current_year_expenses() {
        global $wpdb;
        $table = $wpdb->prefix . 'byot_expenses';
        return (float) $wpdb->get_var($wpdb->prepare("SELECT SUM(amount) FROM {$table} WHERE expense_date >= %s", date('Y-01-01')));
    }

    private static function get_current_year_purchases() {
        global $wpdb;
        $table = $wpdb->prefix . 'byot_purchases';
        return (float) $wpdb->get_var($wpdb->prepare("SELECT SUM(total_amount) FROM {$table} WHERE purchase_date >= %s", date('Y-01-01')));
    }

    private static function get_current_year_profit() {
        return self::get_current_year_sales() - self::get_current_year_expenses();
    }

    private static function render_recent_expenses() {
        global $wpdb;
        $table = $wpdb->prefix . 'byot_expenses';
        $rows = $wpdb->get_results("SELECT * FROM {$table} ORDER BY created_at DESC LIMIT 5");
        if (empty($rows)) {
            echo '<p>' . esc_html__('Nu exista inregistrari.', 'byot-accounting') . '</p>';
            return;
        }
        echo '<table class="widefat"><thead><tr><th>' . esc_html__('Data', 'byot-accounting') . '</th><th>' . esc_html__('Categorie', 'byot-accounting') . '</th><th>' . esc_html__('Suma', 'byot-accounting') . '</th></tr></thead><tbody>';
        foreach ($rows as $row) {
            echo '<tr>';
            echo '<td>' . esc_html(date_i18n(get_option('date_format'), strtotime($row->expense_date))) . '</td>';
            echo '<td>' . esc_html($row->category) . '</td>';
            echo '<td>' . wp_kses_post(wc_price($row->amount)) . '</td>';
            echo '</tr>';
        }
        echo '</tbody></table>';
    }

    private static function render_recent_purchases() {
        global $wpdb;
        $table = $wpdb->prefix . 'byot_purchases';
        $rows = $wpdb->get_results("SELECT * FROM {$table} ORDER BY created_at DESC LIMIT 5");
        if (empty($rows)) {
            echo '<p>' . esc_html__('Nu exista inregistrari.', 'byot-accounting') . '</p>';
            return;
        }
        echo '<table class="widefat"><thead><tr><th>' . esc_html__('Data', 'byot-accounting') . '</th><th>' . esc_html__('Produs', 'byot-accounting') . '</th><th>' . esc_html__('Total', 'byot-accounting') . '</th></tr></thead><tbody>';
        foreach ($rows as $row) {
            echo '<tr>';
            echo '<td>' . esc_html(date_i18n(get_option('date_format'), strtotime($row->purchase_date))) . '</td>';
            echo '<td>' . esc_html($row->product_name) . '</td>';
            echo '<td>' . wp_kses_post(wc_price($row->total_amount)) . '</td>';
            echo '</tr>';
        }
        echo '</tbody></table>';
    }
}