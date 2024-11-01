<?php
namespace OACS\StockControl\Controllers;

/**
 * Handles log creation of each product
 */

defined( 'ABSPATH' ) || exit;
class StockControlProductLog {

	public function log_stock_change( $product ) {
		global $wpdb;

		$product_id       = $product->get_id();
		$user_id          = get_current_user_id();
		$stock_count      = $product->get_stock_quantity();
		$stock_status     = $product->get_stock_status();
		$backorder_status = $product->get_backorders();
		$manage_stock     = $product->get_manage_stock() ? 'yes' : 'no';
		$timestamp        = current_time( 'mysql' );

		$result = $wpdb->query(
			$wpdb->prepare(
				"INSERT INTO {$wpdb->prefix}stock_log (product_id, user_id, stock_count, stock_status, backorder_status, manage_stock, timestamp) VALUES (%d, %d, %d, %s, %s, %s, %s)",
				$product_id,
				$user_id,
				$stock_count,
				$stock_status,
				$backorder_status,
				$manage_stock,
				$timestamp
			)
		);

		if ( false === $result ) {
			error_log( 'Failed to log stock change for product ID: ' . $product_id );
		}
	}
}
