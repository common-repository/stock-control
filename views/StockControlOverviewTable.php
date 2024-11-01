<?php
namespace OACS\StockControl\Views;

/**
 * Manages general overview of stock overview. Admin menu Products >> Stock Overview
 */
if ( ! class_exists( 'WP_List_Table' ) ) {
	require_once ABSPATH . 'wp-admin/includes/class-wp-list-table.php';
}

class StockControlOverviewTable extends \WP_List_Table {
	public function prepare_items() {
		// Set the columns.
		$this->_column_headers = array( $this->get_columns(), array(), $this->get_sortable_columns() );

		// Get the data.
		$this->items = $this->get_stock_overview_data();

		// Get the saved items per page value.
		$user      = get_current_user_id();
		$screen    = get_current_screen();
		$option    = $screen->get_option( 'per_page', 'option' );
		$user_meta = get_user_meta( get_current_user_id() );
		$per_page  = isset( $user_meta['stock_control_per_page'][0] ) ? (int) $user_meta['stock_control_per_page'][0] : 20;

		// If the saved value is not set or it's not a valid number, use the default value.
		if ( empty( $per_page ) || $per_page < 1 ) {
			$per_page = $screen->get_option( 'per_page', 'default' );
		}

		$this->set_pagination_args(
			array(
				'total_items' => $this->get_total_items(),
				'per_page'    => $per_page,
				'total_pages' => ceil( $this->get_total_items() / $per_page ),
			)
		);
	}



	public function get_columns() {
		return array(
			'cb'                  => '<input type="checkbox" />',
			'product'             => __( 'Product', 'stock-control' ),
			'product_type'        => __( 'Product Type', 'stock-control' ),
			'stock_count'         => __( 'Stock Count', 'stock-control' ),
			'low_stock_threshold' => __( 'Low Stock Threshold', 'stock-control' ),
			'stock_status'        => __( 'Stock Status', 'stock-control' ),
			'backorder_status'    => __( 'Backorder Status', 'stock-control' ),
			'manage_stock'        => __( 'Manage stock?', 'stock-control' ),
			'action'              => __( 'Action', 'stock-control' ),
		);
	}
	public function column_cb( $item ) {
		return sprintf(
			'<input type="checkbox" name="bulk-edit[]" value="%s" />',
			$item['ID']
		);
	}

	public function get_bulk_actions() {
		return array(
			'bulk-edit' => __( 'Edit', 'stock-control' ),
		);
	}

	protected function display_tablenav( $which ) {
		?>
		<div class="tablenav <?php echo esc_attr( $which ); ?>">
			<?php
			$this->extra_tablenav( $which );
			$this->pagination( $which );
			?>
			<br class="clear" />
		</div>
		<?php
	}

	public function extra_tablenav( $which ) {
		if ( 'top' === $which ) {
			wp_nonce_field( 'bulk-' . $this->_args['plural'] );
		}
	}



	public function column_default( $item, $column_name ) {
		$product = wc_get_product( $item['ID'] ); // Ensure $item is an array and you're accessing the ID correctly.

		if ( ! $product ) { // Check if product exists.
			return __( 'Product not found', 'stock-control' );
		}

		switch ( $column_name ) {
			case 'product':
				$product_url   = get_permalink( $item['ID'] );
				$product_title = $product->get_name();
				return '<a href="' . esc_url( $product_url ) . '">' . esc_html( $product_title ) . '</a>';
			case 'product_type':
				$product_type       = $product->get_type();
				$product_type_label = ucfirst( $product_type );
				return esc_html( $product_type_label );
			case 'stock_count':
				return '<input type="number" name="stock_count[' . $item['ID'] . ']" value="' . esc_attr( $product->get_stock_quantity() ) . '">';
			case 'stock_status':
				$stock_status = $product->get_stock_status();
				$statuses     = array(
					'instock'     => __( 'In stock', 'stock-control' ),
					'outofstock'  => __( 'Out of stock', 'stock-control' ),
					'onbackorder' => __( 'On backorder', 'stock-control' ),
				);
				$output       = '<select name="stock_status[' . $item['ID'] . ']">';
				foreach ( $statuses as $key => $status ) {
					$selected = ( $stock_status === $key ) ? ' selected' : '';
					$output  .= '<option value="' . esc_attr( $key ) . '"' . $selected . '>' . esc_html( $status ) . '</option>';
				}
				$output .= '</select>';
				return $output;
			case 'backorder_status':
				$backorder_status = $product->get_backorders();
				$statuses         = array(
					'no'     => __( 'Do not allow', 'stock-control' ),
					'notify' => __( 'Allow, but notify customer', 'stock-control' ),
					'yes'    => __( 'Allow', 'stock-control' ),
				);
				$output           = '<select name="backorder_status[' . $item['ID'] . ']">';
				foreach ( $statuses as $key => $status ) {
					$selected = ( $backorder_status === $key ) ? ' selected' : '';
					$output  .= '<option value="' . esc_attr( $key ) . '"' . $selected . '>' . esc_html( $status ) . '</option>';
				}
				$output .= '</select>';
				return $output;
			case 'manage_stock':
				$manage_stock = $product->get_manage_stock();
				$checked      = $manage_stock ? ' checked' : '';
				return '<input type="checkbox" name="manage_stock[' . $item['ID'] . ']" value="1"' . $checked . '>';
			case 'low_stock_threshold':
				$low_stock_threshold = $product->get_low_stock_amount();
				return '<input type="number" name="low_stock_threshold[' . $item['ID'] . ']" value="' . esc_attr( $low_stock_threshold ) . '">';

			case 'action':
				return '<button type="button" class="button button-primary save-stock" data-product-id="' . $item['ID'] . '">' . __( 'Save', 'stock-control' ) . '</button>';
			default:
				return print_r( $item, true );
		}
	}


	private function get_stock_overview_data() {
		global $wpdb;

		// Check if nonce is set and verify it.
		if ( isset( $_REQUEST['stock_overview_search_nonce'] ) ) {
			$nonce = sanitize_text_field( wp_unslash( $_REQUEST['stock_overview_search_nonce'] ) );
			if ( ! wp_verify_nonce( $nonce, 'stock_overview_search' ) ) {
				die( 'Security check failed' );
			}
		}
		// Handle search.
		$search = '';
		if ( isset( $_REQUEST['s'] ) && ! empty( $_REQUEST['s'] ) ) {
			$search = sanitize_text_field( wp_unslash( $_REQUEST['s'] ) );
		}

		// Handle pagination.
		$per_page     = $this->get_items_per_page( 'stock_control_per_page', 20 );
		$current_page = $this->get_pagenum();
		$offset       = ( $current_page - 1 ) * $per_page;

		// Prepare the query.
		if ( ! empty( $search ) ) {
			$like    = '%' . $wpdb->esc_like( $search ) . '%';
			$results = $wpdb->get_results( $wpdb->prepare( "SELECT * FROM `{$wpdb->prefix}posts` WHERE (post_type = 'product' OR post_type = 'product_variation') AND post_status = 'publish' AND post_title LIKE %s ORDER BY post_title ASC LIMIT %d, %d", $like, $offset, $per_page ), ARRAY_A );
		} else {
			$results = $wpdb->get_results( $wpdb->prepare( "SELECT * FROM `{$wpdb->prefix}posts` WHERE (post_type = 'product' OR post_type = 'product_variation') AND post_status = 'publish' ORDER BY post_title ASC LIMIT %d, %d", $offset, $per_page ), ARRAY_A );
		}

		return $results;
	}

	private function get_total_items( $nonce = '' ) {
		global $wpdb;

		// Check if nonce is set and verify it.
		if ( isset( $_REQUEST['stock_overview_search_nonce'] ) ) {
			$nonce = sanitize_text_field( wp_unslash( $_REQUEST['stock_overview_search_nonce'] ) );
			if ( ! wp_verify_nonce( $nonce, 'stock_overview_search' ) ) {
				die( 'Security check failed' );
			}
		}

		$search_query = '';
		if ( isset( $_REQUEST['s'] ) && ! empty( $_REQUEST['s'] ) ) {
			$search         = sanitize_text_field( wp_unslash( $_REQUEST['s'] ) );
			$escaped_search = '%' . $wpdb->esc_like( $search ) . '%';
			$search_query   = $wpdb->prepare( ' AND post_title LIKE %s', $escaped_search );
		}

		// Incorporate the complete query into get_var.
		$count = $wpdb->get_var( $wpdb->prepare( "SELECT COUNT(*) FROM `{$wpdb->prefix}posts` WHERE (post_type = 'product' OR post_type = 'product_variation') AND post_status = 'publish' %s", $search_query ) );

		return $count;
	}
}

