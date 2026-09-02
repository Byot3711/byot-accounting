<?php
if (!defined('ABSPATH')) {
    exit;
}

class BYOT_Acc_Admin {
    public static function init() {
        add_action('admin_menu', array(__CLASS__, 'register_menus'));
        add_action('admin_enqueue_scripts', array(__CLASS__, 'enqueue_assets'));
    }

    public static function register_menus() {
        add_menu_page(
            __('BYOT Accounting', 'byot-accounting'),
            __('BYOT Conta', 'byot-accounting'),
            'manage_woocommerce',
            'byot-accounting',
            array('BYOT_Acc_Dashboard', 'render'),
            'dashicons-chart-area',
            58
        );

        add_submenu_page(
            'byot-accounting',
            __('Dashboard', 'byot-accounting'),
            __('Panou Principal', 'byot-accounting'),
            'manage_woocommerce',
            'byot-accounting',
            array('BYOT_Acc_Dashboard', 'render')
        );

        add_submenu_page(
            'byot-accounting',
            __('Sales', 'byot-accounting'),
            __('Vanzari', 'byot-accounting'),
            'manage_woocommerce',
            'byot-sales',
            array('BYOT_Acc_Sales', 'render')
        );

        add_submenu_page(
            'byot-accounting',
            __('Expenses', 'byot-accounting'),
            __('Cheltuieli', 'byot-accounting'),
            'manage_woocommerce',
            'byot-expenses',
            array('BYOT_Acc_Expenses', 'render')
        );

        add_submenu_page(
            'byot-accounting',
            __('Purchases', 'byot-accounting'),
            __('Achizitii', 'byot-accounting'),
            'manage_woocommerce',
            'byot-purchases',
            array('BYOT_Acc_Purchases', 'render')
        );
    }

    public static function enqueue_assets($hook) {
        if (strpos($hook, 'byot') === false) {
            return;
        }

        wp_enqueue_style('byot-admin-css', BYOT_ACC_PLUGIN_URL . 'assets/css/admin.css', array(), BYOT_ACC_VERSION);
        wp_enqueue_script('chart-js', 'https://cdn.jsdelivr.net/npm/chart.js@4.4.1/dist/chart.umd.min.js', array(), '4.4.1', true);
        wp_enqueue_script('byot-admin-js', BYOT_ACC_PLUGIN_URL . 'assets/js/admin.js', array('jquery', 'chart-js'), BYOT_ACC_VERSION, true);

        wp_localize_script('byot-admin-js', 'byotAjax', array(
            'ajaxUrl' => admin_url('admin-ajax.php'),
            'nonce'   => wp_create_nonce('byot_nonce')
        ));
    }
}