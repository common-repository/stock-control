<?php
namespace OACS\StockControl\Controllers;

/**
 * Handles settings logic
 */

defined( 'ABSPATH' ) || exit;
class StockControlSettingsSchedule {

	public function schedule_stock_purge() {
		if ( ! wp_next_scheduled( 'purge_stock_data' ) ) {
			wp_schedule_event( time(), 'daily', 'purge_stock_data' );
		}
	}

	public function purge_stock_data() {
		$purge_days = get_option( 'oacs_sc_stock_control_purge_data_after_days' );
		if ( $purge_days ) {
			global $wpdb;
			$table_name  = $wpdb->prefix . 'stock_log';
			$cutoff_date = gmdate( 'Y-m-d H:i:s', strtotime( "-{$purge_days} days" ) );

			$wpdb->query( $wpdb->prepare( 'DELETE FROM your_table_name WHERE timestamp < %s', $cutoff_date ) );

			update_option( 'oacs_sc_stock_control_last_data_purge', current_time( 'mysql' ) );
			update_option( 'oacs_sc_stock_control_next_data_purge', gmdate( 'Y-m-d H:i:s', strtotime( '+1 day' ) ) );
		}
	}

}



