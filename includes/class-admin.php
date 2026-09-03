<?php
/**
 * Admin menu registration and asset enqueuing.
 *
 * @package BYOT_Accounting
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Registers the plugin's admin menu pages and enqueues admin assets.
 */
class BYOT_Acc_Admin {

	/**
	 * Wires up the admin_menu and admin_enqueue_scripts hooks.
	 */
	public static function init() {
		add_action( 'admin_menu', array( __CLASS__, 'register_menus' ) );
		add_action( 'admin_enqueue_scripts', array( __CLASS__, 'enqueue_assets' ) );
	}

	/**
	 * Registers the top-level menu and its submenu pages.
	 */
	public static function register_menus() {
		add_menu_page(
			__( 'BYOT Accounting', 'byot-accounting' ),
			__( 'BYOT Conta', 'byot-accounting' ),
			'manage_woocommerce',
			'byot-accounting',
			array( 'BYOT_Acc_Dashboard', 'render' ),
			'dashicons-chart-area',
			58
		);

		add_submenu_page(
			'byot-accounting',
			__( 'Dashboard', 'byot-accounting' ),
			__( 'Panou Principal', 'byot-accounting' ),
			'manage_woocommerce',
			'byot-accounting',
			array( 'BYOT_Acc_Dashboard', 'render' )
		);

		add_submenu_page(
			'byot-accounting',
			__( 'Sales', 'byot-accounting' ),
			__( 'Vanzari', 'byot-accounting' ),
			'manage_woocommerce',
			'byot-sales',
			array( 'BYOT_Acc_Sales', 'render' )
		);

		add_submenu_page(
			'byot-accounting',
			__( 'Expenses', 'byot-accounting' ),
			__( 'Cheltuieli', 'byot-accounting' ),
			'manage_woocommerce',
			'byot-expenses',
			array( 'BYOT_Acc_Expenses', 'render' )
		);

		add_submenu_page(
			'byot-accounting',
			__( 'Purchases', 'byot-accounting' ),
			__( 'Achizitii', 'byot-accounting' ),
			'manage_woocommerce',
			'byot-purchases',
			array( 'BYOT_Acc_Purchases', 'render' )
		);
	}

	/**
	 * Enqueues admin CSS/JS on the plugin's own pages only.
	 *
	 * @param string $hook The current admin page hook suffix.
	 */
	public static function enqueue_assets( $hook ) {
		if ( strpos( $hook, 'byot' ) === false ) {
			return;
		}

		wp_enqueue_style( 'byot-admin-css', BYOT_ACC_PLUGIN_URL . 'assets/css/admin.css', array(), BYOT_ACC_VERSION );
		wp_enqueue_script( 'chart-js', 'https://cdn.jsdelivr.net/npm/chart.js@4.4.1/dist/chart.umd.min.js', array(), '4.4.1', true );
		wp_enqueue_script( 'byot-admin-js', BYOT_ACC_PLUGIN_URL . 'assets/js/admin.js', array( 'jquery', 'chart-js' ), BYOT_ACC_VERSION, true );

		wp_localize_script(
			'byot-admin-js',
			'byotAjax',
			array(
				'ajaxUrl' => admin_url( 'admin-ajax.php' ),
				'nonce'   => wp_create_nonce( 'byot_nonce' ),
			)
		);
	}
}
