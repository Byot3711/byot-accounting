<?php
/**
 * Expenses admin screen: list, add/edit form, save and delete handlers.
 *
 * @package BYOT_Accounting
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Manages the Expenses admin page and its custom database table.
 */
class BYOT_Acc_Expenses {

	/**
	 * Dispatches the Expenses page to the list, form, or delete handler.
	 */
	public static function render() {
		if ( ! current_user_can( 'manage_woocommerce' ) ) {
			wp_die( esc_html__( 'Acces interzis.', 'byot-accounting' ) );
		}

		$action = isset( $_GET['action'] ) ? sanitize_text_field( wp_unslash( $_GET['action'] ) ) : 'list'; // phpcs:ignore WordPress.Security.NonceVerification.Recommended -- read-only view parameter, no state change.
		if ( 'add' === $action || 'edit' === $action ) {
			self::render_form();
			return;
		}

		if ( isset( $_GET['delete'] ) ) { // phpcs:ignore WordPress.Security.NonceVerification.Recommended -- nonce is verified inside handle_delete() before any data is touched.
			self::handle_delete();
		}

		self::render_list();
	}

	/**
	 * Verifies the delete nonce and removes the requested expense row.
	 */
	private static function handle_delete() {
		if ( ! isset( $_GET['_wpnonce'] ) || ! wp_verify_nonce( sanitize_text_field( wp_unslash( $_GET['_wpnonce'] ) ), 'byot_delete_expense' ) ) {
			wp_die( esc_html__( 'Securitate verificata.', 'byot-accounting' ) );
		}
		global $wpdb;
		$id = intval( $_GET['delete'] );
		$wpdb->delete( $wpdb->prefix . 'byot_expenses', array( 'id' => $id ) ); // phpcs:ignore WordPress.DB.DirectDatabaseQuery.DirectQuery, WordPress.DB.DirectDatabaseQuery.NoCaching -- custom plugin table, no core object-cache API applies.
		wp_redirect( admin_url( 'admin.php?page=byot-expenses&deleted=1' ) );
		exit;
	}

	/**
	 * Verifies the save nonce and inserts or updates an expense row.
	 */
	private static function handle_save() {
		if ( ! isset( $_POST['byot_expense_nonce'] ) || ! wp_verify_nonce( sanitize_text_field( wp_unslash( $_POST['byot_expense_nonce'] ) ), 'byot_save_expense' ) ) {
			wp_die( esc_html__( 'Nonce invalid.', 'byot-accounting' ) );
		}

		global $wpdb;
		$table = $wpdb->prefix . 'byot_expenses';
		$data  = array(
			'expense_date'   => sanitize_text_field( wp_unslash( $_POST['expense_date'] ) ),
			'category'       => sanitize_text_field( wp_unslash( $_POST['category'] ) ),
			'description'    => sanitize_textarea_field( wp_unslash( $_POST['description'] ) ),
			'amount'         => floatval( sanitize_text_field( wp_unslash( $_POST['amount'] ) ) ),
			'payment_method' => sanitize_text_field( wp_unslash( $_POST['payment_method'] ) ),
			'supplier'       => sanitize_text_field( wp_unslash( $_POST['supplier'] ) ),
		);
		$id    = isset( $_POST['expense_id'] ) ? intval( $_POST['expense_id'] ) : 0;

		if ( $id > 0 ) {
			$wpdb->update( $table, $data, array( 'id' => $id ) ); // phpcs:ignore WordPress.DB.DirectDatabaseQuery.DirectQuery, WordPress.DB.DirectDatabaseQuery.NoCaching -- custom plugin table, no core object-cache API applies.
		} else {
			$wpdb->insert( $table, $data ); // phpcs:ignore WordPress.DB.DirectDatabaseQuery.DirectQuery -- custom plugin table, no core object-cache API applies.
		}

		wp_redirect( admin_url( 'admin.php?page=byot-expenses&saved=1' ) );
		exit;
	}

	/**
	 * Renders the add/edit form and processes a submitted save.
	 */
	private static function render_form() {
		if ( ! empty( $_POST['save_expense'] ) ) { // phpcs:ignore WordPress.Security.NonceVerification.Missing -- nonce is verified inside handle_save() before any data is touched.
			self::handle_save();
		}

		global $wpdb;
		$id   = isset( $_GET['id'] ) ? intval( $_GET['id'] ) : 0; // phpcs:ignore WordPress.Security.NonceVerification.Recommended -- read-only view parameter, no state change.
		$item = new stdClass();
		if ( $id > 0 ) {
			$item = $wpdb->get_row( $wpdb->prepare( "SELECT * FROM {$wpdb->prefix}byot_expenses WHERE id = %d", $id ) ); // phpcs:ignore WordPress.DB.DirectDatabaseQuery.DirectQuery, WordPress.DB.DirectDatabaseQuery.NoCaching -- custom plugin table, no core object-cache API applies.
			if ( ! $item ) {
				$item = new stdClass();
			}
		}
		?>
		<div class="wrap">
			<h1><?php echo $id ? esc_html__( 'Editeaza Cheltuiala', 'byot-accounting' ) : esc_html__( 'Adauga Cheltuiala', 'byot-accounting' ); ?></h1>
			<form method="post">
				<?php wp_nonce_field( 'byot_save_expense', 'byot_expense_nonce' ); ?>
				<input type="hidden" name="expense_id" value="<?php echo esc_attr( $id ); ?>">
				<table class="form-table">
					<tr>
						<th><label for="expense_date"><?php esc_html_e( 'Data', 'byot-accounting' ); ?></label></th>
						<td><input type="date" id="expense_date" name="expense_date" value="<?php echo esc_attr( $item->expense_date ?? gmdate( 'Y-m-d' ) ); ?>" required></td>
					</tr>
					<tr>
						<th><label for="category"><?php esc_html_e( 'Categorie', 'byot-accounting' ); ?></label></th>
						<td><input type="text" id="category" name="category" value="<?php echo esc_attr( $item->category ?? '' ); ?>" required></td>
					</tr>
					<tr>
						<th><label for="amount"><?php esc_html_e( 'Suma', 'byot-accounting' ); ?></label></th>
						<td><input type="number" step="0.01" id="amount" name="amount" value="<?php echo esc_attr( $item->amount ?? '' ); ?>" required></td>
					</tr>
					<tr>
						<th><label for="payment_method"><?php esc_html_e( 'Metoda plata', 'byot-accounting' ); ?></label></th>
						<td><input type="text" id="payment_method" name="payment_method" value="<?php echo esc_attr( $item->payment_method ?? '' ); ?>"></td>
					</tr>
					<tr>
						<th><label for="supplier"><?php esc_html_e( 'Furnizor', 'byot-accounting' ); ?></label></th>
						<td><input type="text" id="supplier" name="supplier" value="<?php echo esc_attr( $item->supplier ?? '' ); ?>"></td>
					</tr>
					<tr>
						<th><label for="description"><?php esc_html_e( 'Descriere', 'byot-accounting' ); ?></label></th>
						<td><textarea id="description" name="description" rows="4" cols="50"><?php echo esc_textarea( $item->description ?? '' ); ?></textarea></td>
					</tr>
				</table>
				<?php submit_button( __( 'Salveaza', 'byot-accounting' ), 'primary', 'save_expense' ); ?>
				<a href="<?php echo esc_url( admin_url( 'admin.php?page=byot-expenses' ) ); ?>" class="button"><?php esc_html_e( 'Inapoi', 'byot-accounting' ); ?></a>
			</form>
		</div>
		<?php
	}

	/**
	 * Renders the expenses list table.
	 */
	private static function render_list() {
		global $wpdb;
		$table = $wpdb->prefix . 'byot_expenses';
		$items = $wpdb->get_results( "SELECT * FROM {$table} ORDER BY expense_date DESC LIMIT 100" ); // phpcs:ignore WordPress.DB.DirectDatabaseQuery.DirectQuery, WordPress.DB.DirectDatabaseQuery.NoCaching, WordPress.DB.PreparedSQL.InterpolatedNotPrepared -- custom plugin table (name from $wpdb->prefix, not user input), no core object-cache API applies.
		?>
		<div class="wrap">
			<h1><?php echo esc_html( get_admin_page_title() ); ?>
				<a href="<?php echo esc_url( admin_url( 'admin.php?page=byot-expenses&action=add' ) ); ?>" class="page-title-action"><?php esc_html_e( 'Adauga noua', 'byot-accounting' ); ?></a>
			</h1>
			<?php if ( isset( $_GET['saved'] ) ) : // phpcs:ignore WordPress.Security.NonceVerification.Recommended -- read-only success notice flag, no state change. ?>
				<div class="notice notice-success"><p><?php esc_html_e( 'Salvat cu succes.', 'byot-accounting' ); ?></p></div>
			<?php endif; ?>
			<?php if ( isset( $_GET['deleted'] ) ) : // phpcs:ignore WordPress.Security.NonceVerification.Recommended -- read-only success notice flag, no state change. ?>
				<div class="notice notice-success"><p><?php esc_html_e( 'Sters cu succes.', 'byot-accounting' ); ?></p></div>
			<?php endif; ?>

			<table class="wp-list-table widefat fixed striped">
				<thead>
					<tr>
						<th><?php esc_html_e( 'ID', 'byot-accounting' ); ?></th>
						<th><?php esc_html_e( 'Data', 'byot-accounting' ); ?></th>
						<th><?php esc_html_e( 'Categorie', 'byot-accounting' ); ?></th>
						<th><?php esc_html_e( 'Descriere', 'byot-accounting' ); ?></th>
						<th><?php esc_html_e( 'Suma', 'byot-accounting' ); ?></th>
						<th><?php esc_html_e( 'Metoda', 'byot-accounting' ); ?></th>
						<th><?php esc_html_e( 'Furnizor', 'byot-accounting' ); ?></th>
						<th><?php esc_html_e( 'Actiuni', 'byot-accounting' ); ?></th>
					</tr>
				</thead>
				<tbody>
					<?php if ( ! empty( $items ) ) : ?>
						<?php foreach ( $items as $item ) : ?>
							<tr>
								<td><?php echo esc_html( $item->id ); ?></td>
								<td><?php echo esc_html( date_i18n( get_option( 'date_format' ), strtotime( $item->expense_date ) ) ); ?></td>
								<td><?php echo esc_html( $item->category ); ?></td>
								<td><?php echo esc_html( wp_trim_words( $item->description, 10 ) ); ?></td>
								<td><?php echo wp_kses_post( wc_price( $item->amount ) ); ?></td>
								<td><?php echo esc_html( $item->payment_method ); ?></td>
								<td><?php echo esc_html( $item->supplier ); ?></td>
								<td>
									<a href="<?php echo esc_url( admin_url( 'admin.php?page=byot-expenses&action=edit&id=' . $item->id ) ); ?>"><?php esc_html_e( 'Editeaza', 'byot-accounting' ); ?></a> |
									<a href="<?php echo esc_url( wp_nonce_url( admin_url( 'admin.php?page=byot-expenses&delete=' . $item->id ), 'byot_delete_expense' ) ); ?>" onclick="return confirm('<?php esc_attr_e( 'Sigur stergi?', 'byot-accounting' ); ?>')"><?php esc_html_e( 'Sterge', 'byot-accounting' ); ?></a>
								</td>
							</tr>
						<?php endforeach; ?>
					<?php else : ?>
						<tr><td colspan="8"><?php esc_html_e( 'Nu exista inregistrari.', 'byot-accounting' ); ?></td></tr>
					<?php endif; ?>
				</tbody>
			</table>
		</div>
		<?php
	}
}