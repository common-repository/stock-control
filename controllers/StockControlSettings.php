<?php
namespace OACS\StockControl\Controllers;

/**
 * Handles settings logic
 */

defined( 'ABSPATH' ) || exit;
class StockControlSettings {

	public function display_purge_data_info() {
		$next_purge = get_option( 'oacs_sc_stock_control_next_data_purge' );
		$last_purge = get_option( 'oacs_sc_stock_control_last_data_purge' );

		// global $wpdb;
		// $query = 'SELECT SUM(data_length + index_length) FROM information_schema.TABLES WHERE table_schema = %s AND table_name = \'' . $wpdb->prefix . 'stock_log' . '\'';
		// $data_size  = $wpdb->get_var( $wpdb->prepare( $query, $wpdb->dbname ) );
		// $data_size_mb = round( $data_size / ( 1024 * 1024 ), 2 );

		// echo '<h3>' . esc_html__( 'Database', 'stock-control' ) . '</h3>';
		// echo '<p><strong>' . esc_html__( 'Date of next data purge:', 'stock-control' ) . '</strong> ' . esc_html( $next_purge ) . '</p>';
		// echo '<p><strong>' . esc_html__( 'Date of last data purge:', 'stock-control' ) . '</strong> ' . esc_html( $last_purge ? $last_purge : '-' ) . '</p>';
		// echo '<p><strong>' . esc_html__( 'Size of data in the stock log table:', 'stock-control' ) . '</strong> ' . esc_html( $data_size_mb ) . ' MB</p>';
	}

	public function update_purge_dates() {
		$purge_days = get_option( 'oacs_sc_stock_control_purge_data_after_days', 0 );

		if ( $purge_days > 0 ) {
			$last_purge_date = get_option( 'oacs_sc_stock_control_last_data_purge' );
			if ( ! $last_purge_date ) {
				$last_purge_date = current_time( 'mysql' );
				update_option( 'oacs_sc_stock_control_last_data_purge', $last_purge_date );
			}

			$next_purge_date = gmdate( 'Y-m-d H:i:s', strtotime( $last_purge_date . " +{$purge_days} days" ) );
			update_option( 'oacs_sc_stock_control_next_data_purge', $next_purge_date );
		}
	}
}
