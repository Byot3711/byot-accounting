<?php
/**
 * Plugin Name: BYOT Accounting
 * Description: Professional WooCommerce accounting: sales, expenses, purchases, and charts.
 * Version: 1.0.1
 * Author: byot
 * Text Domain: byot-accounting
 * Domain Path: /languages
 * Requires at least: 5.8
 * Requires PHP: 7.4
 * Requires Plugins: woocommerce
 * WC requires at least: 5.0
 */

if (!defined('ABSPATH')) {
    exit;
}

define('BYOT_ACC_VERSION', '1.0.1');
define('BYOT_ACC_PLUGIN_DIR', plugin_dir_path(__FILE__));
define('BYOT_ACC_PLUGIN_URL', plugin_dir_url(__FILE__));

add_action('plugins_loaded', function () {
    load_plugin_textdomain('byot-accounting', false, dirname(plugin_basename(__FILE__)) . '/languages/');
});

spl_autoload_register(function ($class) {
    $prefix = 'BYOT_Acc_';
    if (strpos($class, $prefix) !== 0) {
        return;
    }
    $relative_class = str_replace($prefix, '', $class);
    $file = BYOT_ACC_PLUGIN_DIR . 'includes/class-' . str_replace('_', '-', strtolower($relative_class)) . '.php';
    if (file_exists($file)) {
        require $file;
    }
});

register_activation_hook(__FILE__, array('BYOT_Acc_Activator', 'activate'));
register_deactivation_hook(__FILE__, array('BYOT_Acc_Deactivator', 'deactivate'));

add_action('plugins_loaded', function () {
    if (!class_exists('WooCommerce')) {
        add_action('admin_notices', function () {
            echo '<div class="error"><p>' . esc_html__('BYOT Accounting necesita WooCommerce activ.', 'byot-accounting') . '</p></div>';
        });
        return;
    }
    BYOT_Acc_Admin::init();
    BYOT_Acc_Ajax_Handler::init();
});