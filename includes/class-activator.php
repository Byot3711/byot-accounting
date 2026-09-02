<?php
if (!defined('ABSPATH')) {
    exit;
}

class BYOT_Acc_Activator {
    public static function activate() {
        require_once BYOT_ACC_PLUGIN_DIR . 'includes/class-database.php';
        BYOT_Acc_Database::create_tables();
        flush_rewrite_rules();
    }
}