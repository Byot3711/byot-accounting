<?php
/**
 * AJAX endpoint that feeds the dashboard chart.
 *
 * @package BYOT_Accounting
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Handles the wp_ajax_byot_get_chart_data endpoint used by the dashboard chart.
 */
class BYOT_Acc_Ajax_Handler {

	/**
	 * Registers the AJAX action.
	 */
	public static function init() {
		add_action( 'wp_ajax_byot_get_chart_data', array( __CLASS__, 'get_chart_data' ) );
	}

	/**
	 * Returns 12 months of sales/expenses/purchases totals for the chart.
	 */
	public static function get_chart_data() {
		check_ajax_referer( 'byot_nonce', 'nonce' );
		if ( ! current_user_can( 'manage_woocommerce' ) ) {
			wp_send_json_error( 'Unauthorized' );
		}

		$months        = array();
		$sales_data    = array();
		$expense_data  = array();
		$purchase_data = array();

		for ( $i = 11; $i >= 0; $i-- ) {
			$dt       = gmdate( 'Y-m-01', strtotime( "-{$i} months" ) );
			$months[] = date_i18n( 'M Y', strtotime( $dt ) );
			$start    = gmdate( 'Y-m-01', strtotime( "-{$i} months" ) );
			$end      = gmdate( 'Y-m-t', strtotime( "-{$i} months" ) );

			$sales_data[]    = self::get_wc_sales_between( $start, $end );
			$expense_data[]  = self::get_expenses_between( $start, $end );
			$purchase_data[] = self::get_purchases_between( $start, $end );
		}

		wp_send_json_success(
			array(
				'labels'    => $months,
				'sales'     => $sales_data,
				'expenses'  => $expense_data,
				'purchases' => $purchase_data,
			)
		);
	}

	/**
	 * Sums completed/processing WooCommerce order totals within a date range.
	 *
	 * @param string $start Range start date (Y-m-d).
	 * @param string $end   Range end date (Y-m-d).
	 * @return float
	 */
	private static function get_wc_sales_between( $start, $end ) {
		$orders = wc_get_orders(
			array(
				'status'       => array( 'completed', 'processing' ),
				'limit'        => -1,
				'date_created' => $start . '...' . $end,
				'return'       => 'ids',
			)
		);
		$total  = 0;
		foreach ( $orders as $order_id ) {
			$order = wc_get_order( $order_id );
			if ( $order ) {
				$total += (float) $order->get_total();
			}
		}
		return $total;
	}

	/**
	 * Sums expenses within a date range.
	 *
	 * @param string $start Range start date (Y-m-d).
	 * @param string $end   Range end date (Y-m-d).
	 * @return float
	 */
	private static function get_expenses_between( $start, $end ) {
		global $wpdb;
		$table = $wpdb->prefix . 'byot_expenses';
		$sql   = $wpdb->prepare( "SELECT SUM(amount) FROM {$table} WHERE expense_date BETWEEN %s AND %s", $start, $end ); // phpcs:ignore WordPress.DB.PreparedSQL.InterpolatedNotPrepared -- table name from $wpdb->prefix, not user input.
		return (float) $wpdb->get_var( $sql ); // phpcs:ignore WordPress.DB.DirectDatabaseQuery.DirectQuery, WordPress.DB.DirectDatabaseQuery.NoCaching, WordPress.DB.PreparedSQL.NotPrepared -- $sql is built via $wpdb->prepare() above; custom plugin table, no core object-cache API applies.
	}

	/**
	 * Sums purchases within a date range.
	 *
	 * @param string $start Range start date (Y-m-d).
	 * @param string $end   Range end date (Y-m-d).
	 * @return float
	 */
	private static function get_purchases_between( $start, $end ) {
		global $wpdb;
		$table = $wpdb->prefix . 'byot_purchases';
		$sql   = $wpdb->prepare( "SELECT SUM(total_amount) FROM {$table} WHERE purchase_date BETWEEN %s AND %s", $start, $end ); // phpcs:ignore WordPress.DB.PreparedSQL.InterpolatedNotPrepared -- table name from $wpdb->prefix, not user input.
		return (float) $wpdb->get_var( $sql ); // phpcs:ignore WordPress.DB.DirectDatabaseQuery.DirectQuery, WordPress.DB.DirectDatabaseQuery.NoCaching, WordPress.DB.PreparedSQL.NotPrepared -- $sql is built via $wpdb->prepare() above; custom plugin table, no core object-cache API applies.
	}
}
