<?php
namespace OACS\StockControl\Views;

use OACS\StockControl\Views\StockControlOverviewTable;

/**
 * Manages general overview of stock overview. Admin menu Products >> Stock Overview
 */
class StockControlStockOverview {


	public function add_stock_overview_menu() {
		$hook = add_submenu_page(
			'edit.php?post_type=product',
			__( 'Stock Overview', 'stock-control' ),
			__( 'Stock Overview', 'stock-control' ),
			'manage_options',
			'stock-overview',
			array( $this, 'stock_overview_page' )
		);

		// Add the action to register screen options and the filter to set screen options after the submenu page is added
		add_action( "load-$hook", array( $this, 'register_screen_options' ) );
		add_filter( 'set-screen-option', array( $this, 'set_screen_option_callback' ), 10, 3 );

		// Add the action to update the user meta for stock control items per page
		add_action( 'admin_init', array( $this, 'update_stock_control_per_page_user_meta' ) );
	}



	public function register_screen_options() {
		add_screen_option(
			'per_page',
			array(
				'label'   => __( 'Products per page', 'stock-control' ),
				'default' => 20,
				'option'  => 'stock_control_per_page',
			)
		);
	}

	public function set_screen_option_callback( $status, $option, $value ) {
		if ( 'stock_control_per_page' === $option ) {
			return $value;
		}

		return $status;
	}

	public function update_stock_control_per_page_user_meta() {
		// Check if our nonce is set.
		if ( ! isset( $_REQUEST['screenoptionnonce'] ) ) {
			return;
		}

		// Verify that the nonce is valid.
		if ( ! wp_verify_nonce( sanitize_text_field( wp_unslash( $_REQUEST['screenoptionnonce'] ) ), 'screen-options-nonce' ) ) {
			return;
		}

		if ( isset( $_REQUEST['wp_screen_options']['option'] ) && 'stock_control_per_page' === $_REQUEST['wp_screen_options']['option'] ) {
			$value = isset( $_REQUEST['wp_screen_options']['value'] ) ? (int) $_REQUEST['wp_screen_options']['value'] : 0;
			if ( 0 < $value ) {
				update_user_meta( get_current_user_id(), 'stock_control_per_page', $value );
			}
		}
	}



	public function stock_overview_page() {
		$nonce  = ( isset( $_REQUEST['stock_overview_search_nonce'] ) ) ? sanitize_key( wp_unslash( $_REQUEST['stock_overview_search_nonce'] ) ) : '';
		$search = ( isset( $_REQUEST['s'] ) && wp_verify_nonce( $nonce, 'stock_overview_search' ) ) ? sanitize_text_field( wp_unslash( $_REQUEST['s'] ) ) : '';

		// Instantiate the StockControlOverviewTable class
		$stock_overview_table = new StockControlOverviewTable();

		// Prepare the items and display the table
		$stock_overview_table->prepare_items( $search, $nonce );

		// Display the table
		?>
		<div class="wrap">
			<h1><?php esc_html_e( 'Stock Overview', 'stock-control' ); ?></h1>
			<form method="post">
			<input type="text" name="s" placeholder="Search" value="<?php echo esc_attr( $search ); ?>">
			<input type="submit" name="submit" value="Search" class="button button-primary">
			<?php wp_nonce_field( 'stock_overview_search', 'stock_overview_search_nonce' ); ?>
			</form>
			<?php $stock_overview_table->display(); ?>
			<button id="bulk-save" type="button" class="button button-primary">Bulk Save</button>
		</div>
		<?php
	}

	public function prepare_items( $search = '' ) {
		$this->items = $this->get_items( $search );
	}

	/**
	 * Fetches the items from the database.
	 *
	 * @param string $search The search string.
	 * @return array The array of item objects.
	 */
	protected function get_items( $search = '' ) {
		if ( $search ) {
			$args = array(
				'post_type'      => 'product',
				'posts_per_page' => -1,
				's'              => $search,
			);

			$query = new WP_Query( $args );
			return $query->get_posts();
		} else {
			$args = array(
				'post_type'      => 'product',
				'posts_per_page' => -1,
			);

			$query = new WP_Query( $args );
			return $query->get_posts();
		}
	}
}
