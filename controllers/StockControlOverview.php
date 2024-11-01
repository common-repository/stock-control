<?php
/**
 * Handles log creation of each product.
 *
 * @package OACS
 * @subpackage StockControl
 * @category Controllers
 */

namespace OACS\StockControl\Controllers;

use OACS\StockControl\Controllers\StockControlProductLog;

defined( 'ABSPATH' ) || exit;

/**
 * Class StockControlOverview
 *
 * @category Controllers
 * @package  OACS\StockControl\Controllers
 */
class StockControlOverview {
	/**
	 * Save stock control callback.
	 *
	 * @return void
	 */
	public function save_stock_control_callback() {
		// Verify nonce.
		$security = isset( $_POST['security'] ) ? sanitize_key( $_POST['security'] ) : '';
		if ( ! $security || ! wp_verify_nonce( wp_unslash( $security ), 'stock_control_nonce' ) ) {
			wp_send_json_error( array( 'message' => 'Nonce verification failed.' ) );
		}
		// Get POST data
		$product_id          = isset( $_POST['product_id'] ) ? intval( wp_unslash( $_POST['product_id'] ) ) : 0;
		$stock_count         = isset( $_POST['stock_count'] ) ? intval( wp_unslash( $_POST['stock_count'] ) ) : 0;
		$stock_status        = isset( $_POST['stock_status'] ) ? sanitize_text_field( wp_unslash( $_POST['stock_status'] ) ) : '';
		$backorder_status    = isset( $_POST['backorder_status'] ) ? sanitize_text_field( wp_unslash( $_POST['backorder_status'] ) ) : '';
		$manage_stock        = isset( $_POST['manage_stock'] ) ? intval( wp_unslash( $_POST['manage_stock'] ) ) : 0;
		$low_stock_threshold = isset( $_POST['low_stock_threshold'] ) ? intval( wp_unslash( $_POST['low_stock_threshold'] ) ) : 0;

		// Check if product_id is valid
		if ( $product_id <= 0 ) {
			wp_send_json_error( array( 'message' => 'Invalid product ID.' ) );
		}

		$current_stock_count         = intval( get_post_meta( $product_id, '_stock', true ) );
		$current_stock_status        = get_post_meta( $product_id, '_stock_status', true );
		$current_backorder_status    = get_post_meta( $product_id, '_backorders', true );
		$current_manage_stock        = get_post_meta( $product_id, '_manage_stock', true ) === 'yes' ? 1 : 0;
		$current_low_stock_threshold = intval( get_post_meta( $product_id, '_low_stock_amount', true ) );

		// Check if values have changed and update them
		$updated_stock_count         = $stock_count !== $current_stock_count ? update_post_meta( $product_id, '_stock', $stock_count ) : true;
		$updated_stock_status        = $stock_status !== $current_stock_status ? update_post_meta( $product_id, '_stock_status', $stock_status ) : true;
		$updated_backorder_status    = $backorder_status !== $current_backorder_status ? update_post_meta( $product_id, '_backorders', $backorder_status ) : true;
		$updated_manage_stock        = $manage_stock !== $current_manage_stock ? update_post_meta( $product_id, '_manage_stock', $manage_stock ? 'yes' : 'no' ) : true;
		$updated_low_stock_threshold = $low_stock_threshold !== $current_low_stock_threshold ? update_post_meta( $product_id, '_low_stock_amount', $low_stock_threshold ) : true;

			// Check if values have changed
			$stock_count_changed         = $stock_count !== $current_stock_count;
			$stock_status_changed        = $stock_status !== $current_stock_status;
			$backorder_status_changed    = $backorder_status !== $current_backorder_status;
			$manage_stock_changed        = $manage_stock !== $current_manage_stock;
			$low_stock_threshold_changed = $low_stock_threshold !== $current_low_stock_threshold;

			// Check if all updates were successful
		if ( $updated_stock_count && $updated_stock_status && $updated_backorder_status && $updated_manage_stock && $updated_low_stock_threshold ) {
			// Check if any value has changed
			if ( $stock_count_changed || $stock_status_changed || $backorder_status_changed || $manage_stock_changed || $low_stock_threshold_changed ) {
				// Create an instance of StockControlProductLog class
				$stock_control_product_log = new StockControlProductLog();

				// Get the WC_Product instance
				$product = wc_get_product( $product_id );

				// Call the log_stock_change method
				$stock_control_product_log->log_stock_change( $product );
			}
				wp_send_json_success( array( 'message' => 'Stock data saved successfully.' ) );
		} else {
			// Return detailed error messages
			$error_messages = array();
			if ( ! $updated_stock_count ) {
				$error_messages[] = 'Failed to update stock count.';
			}
			if ( ! $updated_stock_status ) {
				$error_messages[] = 'Failed to update stock status.';
			}
			if ( ! $updated_backorder_status ) {
				$error_messages[] = 'Failed to update backorder status.';
			}
			if ( ! $updated_manage_stock ) {
				$error_messages[] = 'Failed to update manage stock setting.';
			}
			if ( ! $updated_low_stock_threshold ) {
				$error_messages[] = 'Failed to update low stock threshold.';
			}

			wp_send_json_error(
				array(
					'message' => 'Failed to save stock data.',
					'errors'  => $error_messages,
				)
			);
		}

		// Don't forget to die() at the end of the callback function
		wp_die();
	}
}
