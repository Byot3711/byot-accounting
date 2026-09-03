<?php
/**
 * Sales list screen, backed by WooCommerce orders.
 *
 * @package BYOT_Accounting
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Renders the read-only Sales admin page (sourced from WooCommerce orders).
 */
class BYOT_Acc_Sales {

	/**
	 * Renders the sales list, including the status/date filter form.
	 */
	public static function render() {
		if ( ! current_user_can( 'manage_woocommerce' ) ) {
			wp_die( esc_html__( 'Acces interzis.', 'byot-accounting' ) );
		}

		$per_page     = 20;
		$current_page = isset( $_GET['paged'] ) ? max( 1, intval( $_GET['paged'] ) ) : 1; // phpcs:ignore WordPress.Security.NonceVerification.Recommended -- read-only pagination parameter, no state change.
		$offset       = ( $current_page - 1 ) * $per_page;

		// phpcs:ignore WordPress.Security.NonceVerification.Recommended -- read-only list filters, no state change.
		$status_filter = isset( $_GET['status'] ) ? sanitize_text_field( wp_unslash( $_GET['status'] ) ) : '';
		// phpcs:ignore WordPress.Security.NonceVerification.Recommended -- read-only list filters, no state change.
		$date_from = isset( $_GET['date_from'] ) ? sanitize_text_field( wp_unslash( $_GET['date_from'] ) ) : '';
		// phpcs:ignore WordPress.Security.NonceVerification.Recommended -- read-only list filters, no state change.
		$date_to = isset( $_GET['date_to'] ) ? sanitize_text_field( wp_unslash( $_GET['date_to'] ) ) : '';

		$args = array(
			'status'   => ! empty( $status_filter ) ? array( $status_filter ) : array( 'completed', 'processing' ),
			'limit'    => $per_page,
			'offset'   => $offset,
			'paginate' => true,
		);

		if ( ! empty( $date_from ) && ! empty( $date_to ) ) {
			$args['date_created'] = $date_from . '...' . $date_to;
		}

		$result = wc_get_orders( $args );
		$orders = $result->orders;
		$total  = $result->total;
		$pages  = ceil( $total / $per_page );
		?>
		<div class="wrap">
			<h1><?php echo esc_html( get_admin_page_title() ); ?></h1>

			<form method="get">
				<input type="hidden" name="page" value="byot-sales">
				<div class="tablenav top">
					<div class="alignleft actions">
						<select name="status">
							<option value=""><?php esc_html_e( 'Toate statusurile', 'byot-accounting' ); ?></option>
							<option value="completed" <?php selected( $status_filter, 'completed' ); ?>><?php esc_html_e( 'Finalizate', 'byot-accounting' ); ?></option>
							<option value="processing" <?php selected( $status_filter, 'processing' ); ?>><?php esc_html_e( 'In procesare', 'byot-accounting' ); ?></option>
							<option value="on-hold" <?php selected( $status_filter, 'on-hold' ); ?>><?php esc_html_e( 'In asteptare', 'byot-accounting' ); ?></option>
						</select>
						<input type="date" name="date_from" value="<?php echo esc_attr( $date_from ); ?>">
						<input type="date" name="date_to" value="<?php echo esc_attr( $date_to ); ?>">
						<?php submit_button( __( 'Filtreaza', 'byot-accounting' ), 'button', 'filter', false ); ?>
					</div>
				</div>
			</form>

			<table class="wp-list-table widefat fixed striped">
				<thead>
					<tr>
						<th><?php esc_html_e( 'Comanda', 'byot-accounting' ); ?></th>
						<th><?php esc_html_e( 'Data', 'byot-accounting' ); ?></th>
						<th><?php esc_html_e( 'Client', 'byot-accounting' ); ?></th>
						<th><?php esc_html_e( 'Status', 'byot-accounting' ); ?></th>
						<th><?php esc_html_e( 'Total', 'byot-accounting' ); ?></th>
						<th><?php esc_html_e( 'Metoda plata', 'byot-accounting' ); ?></th>
					</tr>
				</thead>
				<tbody>
					<?php if ( ! empty( $orders ) ) : ?>
						<?php foreach ( $orders as $order ) : ?>
							<tr>
								<td><a href="<?php echo esc_url( $order->get_edit_order_url() ); ?>">#<?php echo esc_html( $order->get_order_number() ); ?></a></td>
								<td><?php echo esc_html( wc_format_datetime( $order->get_date_created() ) ); ?></td>
								<td><?php echo esc_html( $order->get_formatted_billing_full_name() ); ?></td>
								<td><?php echo esc_html( wc_get_order_status_name( $order->get_status() ) ); ?></td>
								<td><?php echo wp_kses_post( $order->get_formatted_order_total() ); ?></td>
								<td><?php echo esc_html( $order->get_payment_method_title() ); ?></td>
							</tr>
						<?php endforeach; ?>
					<?php else : ?>
						<tr><td colspan="6"><?php esc_html_e( 'Nu au fost gasite comenzi.', 'byot-accounting' ); ?></td></tr>
					<?php endif; ?>
				</tbody>
			</table>

			<?php if ( $pages > 1 ) : ?>
				<div class="tablenav">
					<div class="tablenav-pages">
						<?php
						echo paginate_links(
							array(
								'base'      => add_query_arg( 'paged', '%#%' ),
								'format'    => '',
								'prev_text' => __( '&laquo;', 'byot-accounting' ),
								'next_text' => __( '&raquo;', 'byot-accounting' ),
								'total'     => $pages,
								'current'   => $current_page,
							)
						);
						?>
					</div>
				</div>
			<?php endif; ?>
		</div>
		<?php
	}
}