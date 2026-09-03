<?php
/**
 * Custom database table schema for expenses and purchases.
 *
 * @package BYOT_Accounting
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Creates the plugin's custom database tables.
 */
class BYOT_Acc_Database {

	/**
	 * Creates (or upgrades) the byot_expenses and byot_purchases tables via dbDelta().
	 */
	public static function create_tables() {
		global $wpdb;
		$charset_collate = $wpdb->get_charset_collate();
		$expenses        = $wpdb->prefix . 'byot_expenses';
		$purchases       = $wpdb->prefix . 'byot_purchases';

		require_once ABSPATH . 'wp-admin/includes/upgrade.php';

		$sql_expenses = "CREATE TABLE IF NOT EXISTS {$expenses} (
            id bigint(20) unsigned NOT NULL AUTO_INCREMENT,
            expense_date date NOT NULL,
            category varchar(100) NOT NULL DEFAULT '',
            description text NOT NULL,
            amount decimal(15,4) NOT NULL DEFAULT 0.0000,
            payment_method varchar(50) NOT NULL DEFAULT '',
            supplier varchar(150) NOT NULL DEFAULT '',
            receipt_url varchar(255) NOT NULL DEFAULT '',
            created_at datetime NOT NULL DEFAULT CURRENT_TIMESTAMP,
            PRIMARY KEY (id),
            KEY expense_date (expense_date),
            KEY category (category)
        ) {$charset_collate};";

		$sql_purchases = "CREATE TABLE IF NOT EXISTS {$purchases} (
            id bigint(20) unsigned NOT NULL AUTO_INCREMENT,
            purchase_date date NOT NULL,
            product_name varchar(255) NOT NULL DEFAULT '',
            wc_product_id bigint(20) unsigned NOT NULL DEFAULT 0,
            quantity decimal(12,4) NOT NULL DEFAULT 0.0000,
            unit_price decimal(15,4) NOT NULL DEFAULT 0.0000,
            total_amount decimal(15,4) NOT NULL DEFAULT 0.0000,
            supplier varchar(150) NOT NULL DEFAULT '',
            notes text NOT NULL,
            created_at datetime NOT NULL DEFAULT CURRENT_TIMESTAMP,
            PRIMARY KEY (id),
            KEY purchase_date (purchase_date),
            KEY wc_product_id (wc_product_id)
        ) {$charset_collate};";

		dbDelta( $sql_expenses );
		dbDelta( $sql_purchases );
	}
}
