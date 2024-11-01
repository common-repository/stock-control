<?php
namespace OACS\StockControl\Views;

use OACS\StockControl\Controllers\App\StockControlHelper;

/**
 * Manages general overview of stock overview. Admin menu Products >> Stock Overview
 */
// Include the WP_List_Table class.
if ( ! class_exists( 'WP_List_Table' ) ) {
	require_once ABSPATH . 'wp-admin/includes/class-wp-list-table.php';
}

// Create a new class that extends WP_List_Table.
class StockControlLogListTable extends \WP_List_Table {
	// Prepare the items to display.
	public function prepare_items( $search = '' ) {
		// Set the columns
		$this->_column_headers = array( $this->get_columns(), array(), $this->get_sortable_columns() );

		// Set the pagination.
		$logs_per_page = (int) get_user_option( 'logs_per_page' );
		$logs_per_page = $logs_per_page > 0 ? $logs_per_page : 20;

		// Get the data.
		$this->items = $this->get_stock_logs( $search, $logs_per_page, $this->get_pagenum() );

		$this->set_pagination_args(
			array(
				'total_items' => $this->get_total_items( $search ),
				'per_page'    => $logs_per_page,
				'total_pages' => ceil( $this->get_total_items( $search ) / $logs_per_page ),
			)
		);
	}

	// Define the columns.
	public function get_columns() {
		return array(
			'product'          => __( 'Product', 'stock-control' ),
			'user'             => __( 'User', 'stock-control' ),
			'stock_count'      => __( 'Stock Count', 'stock-control' ),
			'stock_status'     => __( 'Stock Status', 'stock-control' ),
			'backorder_status' => __( 'Backorder Status', 'stock-control' ),
			'timestamp'        => __( 'Timestamp', 'stock-control' ),
		);
	}

	// Define the sortable columns.
	public function get_sortable_columns() {
		return array(
			'product'          => array( 'product', false ),
			'user'             => array( 'user', false ),
			'stock_count'      => array( 'stock_count', false ),
			'stock_status'     => array( 'stock_status', false ),
			'backorder_status' => array( 'backorder_status', false ),
			'timestamp'        => array( 'timestamp', false ),
		);
	}

	// Display the column data
	public function column_default( $item, $column_name ) {
		switch ( $column_name ) {
			case 'product':
				$product_url   = get_permalink( $item->product_id );
				$product_title = get_the_title( $item->product_id );
				return '<a href="' . esc_url( $product_url ) . '">' . esc_html( $product_title ) . '</a>';
			case 'user':
				$user_url          = get_author_posts_url( $item->user_id );
				$user_display_name = get_the_author_meta( 'display_name', $item->user_id );
				return '<a href="' . esc_url( $user_url ) . '">' . esc_html( $user_display_name ) . '</a>';
			case 'stock_count':
			case 'stock_status':
			case 'backorder_status':
			case 'timestamp':
				return $item->{$column_name};
			default:
				return print_r( $item, true );
		}
	}

	// Get the stock logs data
	private function get_stock_logs( $search = '', $logs_per_page = 20, $current_page = 1 ) {
		global $wpdb;

		$offset = ( $current_page - 1 ) * $logs_per_page;

		// If there's a search term, prepare the statement with the WHERE clause
		if ( ! empty( $search ) ) {
			$like = '%' . $wpdb->esc_like( $search ) . '%';

			$results = $wpdb->get_results(
				$wpdb->prepare(
					"SELECT `{$wpdb->prefix}stock_log`.*, `{$wpdb->prefix}posts`.post_title, `{$wpdb->prefix}postmeta`.meta_value
				FROM `{$wpdb->prefix}stock_log`
				INNER JOIN `{$wpdb->prefix}posts` ON `{$wpdb->prefix}stock_log`.product_id = `{$wpdb->prefix}posts`.ID
				INNER JOIN `{$wpdb->prefix}postmeta` ON `{$wpdb->prefix}stock_log`.product_id = `{$wpdb->prefix}postmeta`.post_id
				WHERE `{$wpdb->prefix}postmeta`.meta_key = '_stock'
				AND `{$wpdb->prefix}posts`.post_title LIKE %s
				ORDER BY `{$wpdb->prefix}stock_log`.timestamp DESC
				LIMIT %d OFFSET %d",
					$like,
					$logs_per_page,
					$offset
				)
			);
		} else {
			// If there's no search term, prepare the statement without the WHERE clause
			$results = $wpdb->get_results(
				$wpdb->prepare(
					"SELECT `{$wpdb->prefix}stock_log`.*, `{$wpdb->prefix}posts`.post_title, `{$wpdb->prefix}postmeta`.meta_value
				FROM `{$wpdb->prefix}stock_log`
				INNER JOIN `{$wpdb->prefix}posts` ON `{$wpdb->prefix}stock_log`.product_id = `{$wpdb->prefix}posts`.ID
				INNER JOIN `{$wpdb->prefix}postmeta` ON `{$wpdb->prefix}stock_log`.product_id = `{$wpdb->prefix}postmeta`.post_id
				WHERE `{$wpdb->prefix}postmeta`.meta_key = '_stock'
				ORDER BY `{$wpdb->prefix}stock_log`.timestamp DESC
				LIMIT %d OFFSET %d",
					$logs_per_page,
					$offset
				)
			);
		}

		return $results;
	}


	private function get_total_items( $search = '' ) {
		global $wpdb;

		// If there's a search term, prepare the statement with the WHERE clause
		if ( ! empty( $search ) ) {
			$search      = '%' . $wpdb->esc_like( $search ) . '%';
			$total_items = $wpdb->get_var(
				$wpdb->prepare(
					'
			SELECT COUNT(*)
			FROM ' . $wpdb->prefix . 'stock_log
			INNER JOIN ' . $wpdb->prefix . 'posts ON ' . $wpdb->prefix . 'stock_log.product_id = ' . $wpdb->prefix . 'posts.ID
			WHERE ' . $wpdb->prefix . 'posts.post_title LIKE %s
		  ',
					$search
				)
			);
		} else {
			// If there's no search term, no need to use prepare
			$total_items = $wpdb->get_var(
				'
			SELECT COUNT(*)
			FROM ' . $wpdb->prefix . 'stock_log
			INNER JOIN ' . $wpdb->prefix . 'posts ON ' . $wpdb->prefix . 'stock_log.product_id = ' . $wpdb->prefix . 'posts.ID
		  '
			);
		}

		return $total_items;
	}
}
