<?php
namespace OACS\StockControl\Views;

use OACS\StockControl\Controllers\StockControlSettings;
/**
 * Manages WooCommerce settings tab
 */
class StockControlAdminSettings {

	public function add_settings_tab( $settings_tabs ) {
		$settings_tabs['stock_control'] = __( 'Stock Control', 'stock-control' );
		return $settings_tabs;
	}

	public function settings_tab() {
		woocommerce_admin_fields( $this->get_settings() );
		$settings_controller = new \OACS\StockControl\Controllers\StockControlSettings();
		$settings_controller->display_purge_data_info();

		if ( ! wp_script_is( 'jquery', 'enqueued' ) ) {
			wp_enqueue_script( 'jquery' );
		}

		?>
		<script>
			document.querySelector('.woocommerce form').addEventListener('submit', function(event) {
				var deleteCheckbox = document.querySelector('#oacs_sc_stock_control_delete_all_data');
				if (deleteCheckbox.checked) {
					if (!confirm('Are you sure you want to delete all data from the database?')) {
						event.preventDefault();
					}
				}
			});
		</script>
		<?php
		wp_nonce_field( 'oacs_sc_stock_control_delete_all_data_action', 'oacs_sc_stock_control_delete_all_data_nonce' );
	}


	private function purge_all_log_data() {
		global $wpdb;
		$wpdb->query( 'TRUNCATE TABLE ' . $wpdb->prefix . 'stock_log' );
	}


	public function reset_delete_all_data_checkbox() {
		if ( isset( $_POST['oacs_sc_stock_control_delete_all_data_nonce'] ) ) {
			$nonce = sanitize_key( $_POST['oacs_sc_stock_control_delete_all_data_nonce'] );
			if ( wp_verify_nonce( $nonce, 'oacs_sc_stock_control_delete_all_data_action' ) ) {
				if ( isset( $_POST['oacs_sc_stock_control_delete_all_data'] ) && sanitize_key( $_POST['oacs_sc_stock_control_delete_all_data'] ) === '1' ) {
					update_option( 'oacs_sc_stock_control_delete_all_data', '0' );
				}
			}
		}
	}




	public function update_settings() {
		if ( wp_unslash( sanitize_key( isset( $_POST['oacs_sc_stock_control_delete_all_data_nonce'] ) ) ) && wp_verify_nonce( wp_unslash( sanitize_key( $_POST['oacs_sc_stock_control_delete_all_data_nonce'] ) ), 'oacs_sc_stock_control_delete_all_data_action' ) ) {
			if ( isset( $_POST['oacs_sc_stock_control_delete_all_data'] ) && '1' === $_POST['oacs_sc_stock_control_delete_all_data'] ) {
				$this->purge_all_log_data();
			}
			woocommerce_update_options( $this->get_settings() );
		}
	}




	private function get_product_categories() {
		$product_categories = get_terms(
			'product_cat'
		);

		$categories = array();
		if ( ! is_wp_error( $product_categories ) && ! empty( $product_categories ) ) {
			foreach ( $product_categories as $category ) {
				$categories[ $category->term_id ] = $category->name;
			}
		}

		return $categories;
	}

	private function get_products_list() {
		$args          = array(
			'post_type'      => 'product',
			'posts_per_page' => -1,
		);
		$query         = new \WP_Query( $args );
		$products      = $query->get_posts();
		$products_list = array();

		foreach ( $products as $product ) {
			$products_list[ $product->ID ] = $product->post_title;
		}

		return $products_list;
	}



	public function get_settings() {
		$settings = array(
			'section_title'            => array(
				'name' => __( 'Stock Control Settings', 'stock-control' ),
				'type' => 'title',
				'desc' => '',
				'id'   => 'oacs_sc_stock_control_section_title',
			),
			'disable_parent_inventory' => array(
				'name' => __( 'Disable inventory management for parent variable products', 'stock-control' ),
				'type' => 'checkbox',
				'desc' => '',
				'id'   => 'oacs_sc_stock_control_disable_parent_inventory',
			),
			// 'purge_data_after_days'      => array(
			// 'name'              => __( 'Purge all database data after X days', 'stock-control' ),
			// 'type'              => 'number',
			// 'desc'              => '',
			// 'id'                => 'oacs_sc_stock_control_purge_data_after_days',
			// 'custom_attributes' => array(
			// 'min'  => 1,
			// 'step' => 1,
			// ),
			// ),
			// 'exclude_product_categories' => array(
			// 'name'    => __( 'Exclude products by category', 'stock-control' ),
			// 'type'    => 'multiselect',
			// 'class'   => 'wc-enhanced-select',
			// 'options' => $this->get_product_categories(),
			// 'desc'    => __( 'Select product categories to exclude from stock control.', 'stock-control' ),
			// 'id'      => 'oacs_sc_stock_control_exclude_product_categories',
			// ),
			// 'exclude_specific_products'  => array(
			// 'name'    => __( 'Exclude specific products', 'stock-control' ),
			// 'type'    => 'multiselect',
			// 'options' => $this->get_products_list(),
			// 'desc'    => '',
			// 'id'      => 'oacs_sc_stock_control_exclude_specific_products',
			// 'class'   => 'wc-enhanced-select',
			// 'css'     => 'width: 100%;',
			// ),
			'delete_all_data'          => array(
				'name' => __( '⚠️: Delete all data from the database', 'stock-control' ),
				'type' => 'checkbox',
				'desc' => '',
				'id'   => 'oacs_sc_stock_control_delete_all_data',
			),
			'section_end'              => array(
				'type' => 'sectionend',
				'id'   => 'oacs_sc_stock_control_section_end',
			),
		);

		return apply_filters( 'oacs_sc_stock_control_settings', $settings );
	}
}
