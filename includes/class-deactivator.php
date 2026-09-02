<?php
if (!defined('ABSPATH')) {
    exit;
}

class BYOT_Acc_Deactivator {
    public static function deactivate() {
        flush_rewrite_rules();
    }
}