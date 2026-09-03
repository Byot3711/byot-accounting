<?php
/**
 * Plugin activation routine.
 *
 * @package BYOT_Accounting
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Runs on plugin activation.
 */
class BYOT_Acc_Activator {

	/**
	 * Creates the custom database tables and flushes rewrite rules.
	 */
	public static function activate() {
		require_once BYOT_ACC_PLUGIN_DIR . 'includes/class-database.php';
		BYOT_Acc_Database::create_tables();
		flush_rewrite_rules();
	}
}
