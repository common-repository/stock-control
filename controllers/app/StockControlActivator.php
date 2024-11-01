<?php
namespace OACS\StockControl\Controllers\App;

/**
 * Fired during plugin activation
 */

defined( 'ABSPATH' ) || exit;
class StockControlActivator {

	public static function activate_stock_control() {
		self::create_stock_log_table();
	}

	/**
	 * Creates a new "stock_log" table in the WordPress database, if it does not exist.
	 *
	 * This table is used for storing logging information related to stock management.
	 * The table has the following columns:
	 * - id: (Bigint) Unique ID
	 * - product_id: (Bigint) Product ID
	 * - user_id: (Bigint) User ID
	 * - stock_count: (Int) Stock Count
	 * - stock_status: (Varchar) Stock Status
	 * - backorder_status: (Varchar) Backorder Status
	 * - manage_stock: (Varchar) Manage Stock
	 * - timestamp: (Datetime) Timestamp
	 *
	 * @return void
	 */
	private static function create_stock_log_table() {
		global $wpdb;

		$table_name      = $wpdb->prefix . 'stock_log';
		$charset_collate = $wpdb->get_charset_collate();

		$sql = "CREATE TABLE $table_name (
			id BIGINT(20) UNSIGNED NOT NULL AUTO_INCREMENT,
			product_id BIGINT(20) UNSIGNED NOT NULL,
			user_id BIGINT(20) UNSIGNED NOT NULL,
			stock_count INT NOT NULL,
			stock_status VARCHAR(50) NOT NULL,
			backorder_status VARCHAR(50) NOT NULL,
			manage_stock VARCHAR(3) NOT NULL DEFAULT 'no',
			timestamp DATETIME NOT NULL,
			PRIMARY KEY  (id)
		) $charset_collate;";

		require_once ABSPATH . 'wp-admin/includes/upgrade.php';
		dbDelta( $sql );
	}
}
