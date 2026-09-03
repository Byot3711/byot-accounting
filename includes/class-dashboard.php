<?php
/**
 * Dashboard admin screen: summary cards, chart, and recent activity.
 *
 * @package BYOT_Accounting
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Renders the BYOT Accounting dashboard (summary cards, chart, recent activity).
 */
class BYOT_Acc_Dashboard {

	/**
	 * Renders the dashboard page.
	 */
	public static function render() {
		if ( ! current_user_can( 'manage_woocommerce' ) ) {
			wp_die( esc_html__( 'Nu ai permisiunea.', 'byot-accounting' ) );
		}
		?>
		<div class="wrap byot-wrap">
			<h1><?php echo esc_html( get_admin_page_title() ); ?></h1>

			<div class="byot-cards">
				<div class="byot-card">
					<h3><?php esc_html_e( 'Vanzari (An Curent)', 'byot-accounting' ); ?></h3>
					<p class="byot-big"><?php echo wp_kses_post( wc_price( self::get_current_year_sales() ) ); ?></p>
				</div>
				<div class="byot-card">
					<h3><?php esc_html_e( 'Cheltuieli (An Curent)', 'byot-accounting' ); ?></h3>
					<p class="byot-big"><?php echo wp_kses_post( wc_price( self::get_current_year_expenses() ) ); ?></p>
				</div>
				<div class="byot-card">
					<h3><?php esc_html_e( 'Achizitii (An Curent)', 'byot-accounting' ); ?></h3>
					<p class="byot-big"><?php echo wp_kses_post( wc_price( self::get_current_year_purchases() ) ); ?></p>
				</div>
				<div class="byot-card">
					<h3><?php esc_html_e( 'Profit Estimat', 'byot-accounting' ); ?></h3>
					<p class="byot-big"><?php echo wp_kses_post( wc_price( self::get_current_year_profit() ) ); ?></p>
				</div>
			</div>

			<div class="byot-chart-box">
				<h2><?php esc_html_e( 'Evolutie Financiara', 'byot-accounting' ); ?></h2>
				<canvas id="byotMainChart" height="120"></canvas>
			</div>

			<div class="byot-recent">
				<div class="byot-half">
					<h2><?php esc_html_e( 'Ultimele Cheltuieli', 'byot-accounting' ); ?></h2>
					<?php self::render_recent_expenses(); ?>
				</div>
				<div class="byot-half">
					<h2><?php esc_html_e( 'Ultimele Achizitii', 'byot-accounting' ); ?></h2>
					<?php self::render_recent_purchases(); ?>
				</div>
			</div>
		</div>
		<?php
	}

	/**
	 * Sums the total of completed/processing WooCommerce orders for the current year.
	 *
	 * @return float
	 */
	private static function get_current_year_sales() {
		$start  = gmdate( 'Y-01-01' );
		$end    = gmdate( 'Y-12-31' );
		$orders = wc_get_orders(
			array(
				'status'       => array( 'completed', 'processing' ),
				'limit'        => -1,
				'date_created' => $start . '...' . $end,
				'return'       => 'ids',
			)
		);
		$total  = 0;
		foreach ( $orders as $oid ) {
			$order = wc_get_order( $oid );
			if ( $order ) {
				$total += (float) $order->get_total();
			}
		}
		return $total;
	}

	/**
	 * Sums expenses recorded so far this year.
	 *
	 * @return float
	 */
	private static function get_current_year_expenses() {
		global $wpdb;
		$table = $wpdb->prefix . 'byot_expenses';
		return (float) $wpdb->get_var( $wpdb->prepare( "SELECT SUM(amount) FROM {$table} WHERE expense_date >= %s", gmdate( 'Y-01-01' ) ) ); // phpcs:ignore WordPress.DB.DirectDatabaseQuery.DirectQuery, WordPress.DB.DirectDatabaseQuery.NoCaching, WordPress.DB.PreparedSQL.InterpolatedNotPrepared -- custom plugin table (name from $wpdb->prefix, not user input), no core object-cache API applies.
	}

	/**
	 * Sums purchases recorded so far this year.
	 *
	 * @return float
	 */
	private static function get_current_year_purchases() {
		global $wpdb;
		$table = $wpdb->prefix . 'byot_purchases';
		return (float) $wpdb->get_var( $wpdb->prepare( "SELECT SUM(total_amount) FROM {$table} WHERE purchase_date >= %s", gmdate( 'Y-01-01' ) ) ); // phpcs:ignore WordPress.DB.DirectDatabaseQuery.DirectQuery, WordPress.DB.DirectDatabaseQuery.NoCaching, WordPress.DB.PreparedSQL.InterpolatedNotPrepared -- custom plugin table (name from $wpdb->prefix, not user input), no core object-cache API applies.
	}

	/**
	 * Estimated profit: current-year sales minus current-year expenses.
	 *
	 * @return float
	 */
	private static function get_current_year_profit() {
		return self::get_current_year_sales() - self::get_current_year_expenses();
	}

	/**
	 * Echoes a table of the 5 most recent expense rows.
	 */
	private static function render_recent_expenses() {
		global $wpdb;
		$table = $wpdb->prefix . 'byot_expenses';
		$rows  = $wpdb->get_results( "SELECT * FROM {$table} ORDER BY created_at DESC LIMIT 5" ); // phpcs:ignore WordPress.DB.DirectDatabaseQuery.DirectQuery, WordPress.DB.DirectDatabaseQuery.NoCaching, WordPress.DB.PreparedSQL.InterpolatedNotPrepared -- custom plugin table (name from $wpdb->prefix, not user input), no core object-cache API applies.
		if ( empty( $rows ) ) {
			echo '<p>' . esc_html__( 'Nu exista inregistrari.', 'byot-accounting' ) . '</p>';
			return;
		}
		echo '<table class="widefat"><thead><tr><th>' . esc_html__( 'Data', 'byot-accounting' ) . '</th><th>' . esc_html__( 'Categorie', 'byot-accounting' ) . '</th><th>' . esc_html__( 'Suma', 'byot-accounting' ) . '</th></tr></thead><tbody>';
		foreach ( $rows as $row ) {
			echo '<tr>';
			echo '<td>' . esc_html( date_i18n( get_option( 'date_format' ), strtotime( $row->expense_date ) ) ) . '</td>';
			echo '<td>' . esc_html( $row->category ) . '</td>';
			echo '<td>' . wp_kses_post( wc_price( $row->amount ) ) . '</td>';
			echo '</tr>';
		}
		echo '</tbody></table>';
	}

	/**
	 * Echoes a table of the 5 most recent purchase rows.
	 */
	private static function render_recent_purchases() {
		global $wpdb;
		$table = $wpdb->prefix . 'byot_purchases';
		$rows  = $wpdb->get_results( "SELECT * FROM {$table} ORDER BY created_at DESC LIMIT 5" ); // phpcs:ignore WordPress.DB.DirectDatabaseQuery.DirectQuery, WordPress.DB.DirectDatabaseQuery.NoCaching, WordPress.DB.PreparedSQL.InterpolatedNotPrepared -- custom plugin table (name from $wpdb->prefix, not user input), no core object-cache API applies.
		if ( empty( $rows ) ) {
			echo '<p>' . esc_html__( 'Nu exista inregistrari.', 'byot-accounting' ) . '</p>';
			return;
		}
		echo '<table class="widefat"><thead><tr><th>' . esc_html__( 'Data', 'byot-accounting' ) . '</th><th>' . esc_html__( 'Produs', 'byot-accounting' ) . '</th><th>' . esc_html__( 'Total', 'byot-accounting' ) . '</th></tr></thead><tbody>';
		foreach ( $rows as $row ) {
			echo '<tr>';
			echo '<td>' . esc_html( date_i18n( get_option( 'date_format' ), strtotime( $row->purchase_date ) ) ) . '</td>';
			echo '<td>' . esc_html( $row->product_name ) . '</td>';
			echo '<td>' . wp_kses_post( wc_price( $row->total_amount ) ) . '</td>';
			echo '</tr>';
		}
		echo '</tbody></table>';
	}
}