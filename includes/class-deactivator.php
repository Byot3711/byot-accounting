<?php
/**
 * Plugin deactivation routine.
 *
 * @package BYOT_Accounting
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Runs on plugin deactivation.
 */
class BYOT_Acc_Deactivator {

	/**
	 * Flushes rewrite rules. Custom tables are intentionally left in place.
	 */
	public static function deactivate() {
		flush_rewrite_rules();
	}
}
