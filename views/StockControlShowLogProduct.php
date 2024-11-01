<?php
namespace OACS\StockControl\Views;

use OACS\StockControl\Views\StockControlLogListTable;
/**
 * Manages general overview of stock log. Admin menu Products >> Stock Log
 */
class StockControlShowLogProduct {

	public function add_stock_log_menu() {
		$hook = add_submenu_page(
			'edit.php?post_type=product',
			__( 'Stock Log', 'stock-control' ),
			__( 'Stock Log', 'stock-control' ),
			'manage_options',
			'stock-log',
			array( $this, 'stock_log_page' )
		);

		// Add the action to register screen options and the filter to set screen options after the submenu page is added
		add_action( "load-$hook", array( $this, 'register_screen_options' ) );
		add_filter( 'set-screen-option', array( $this, 'set_screen_option' ), 10, 3 );

		// Add the action to update the user meta for logs per page
		add_action( 'admin_init', array( $this, 'update_logs_per_page_user_meta' ) );
	}

	/**
	 * Register screen options for the log display.
	 *
	 * This function adds the screen option for changing the number
	 * of logs displayed per page.
	 */
	public function register_screen_options() {
		add_screen_option(
			'per_page',
			array(
				'label'   => __( 'Logs per page', 'stock-control' ),
				'default' => 20,
				'option'  => 'logs_per_page',
			)
		);
	}

	/**
	 * Sets the value for the screen option to display logs per page.
	 *
	 * @param mixed  $status The result of the default filter applied to the screen option.
	 * @param string $option The option name being set.
	 * @param int    $value  The value being set for the option.
	 * @return mixed The value if option is 'logs_per_page', otherwise the original status.
	 */
	public function set_screen_option( $status, $option, $value ) {
		if ( 'logs_per_page' === $option ) {
			return $value;
		}

		return $status;
	}

	public function update_logs_per_page_user_meta() {
		// Check if our nonce is set.
		if ( ! isset( $_POST['screenoptionnonce'] ) ) {
			return;
		}

		// Verify that the nonce is valid.
		if ( ! wp_verify_nonce( sanitize_text_field( wp_unslash( $_POST['screenoptionnonce'] ) ), 'screen-options-nonce' ) ) {
			return;
		}

		if ( isset( $_POST['wp_screen_options']['option'] ) && 'logs_per_page' === $_POST['wp_screen_options']['option'] ) {
			$value = isset( $_POST['wp_screen_options']['value'] ) ? (int) $_POST['wp_screen_options']['value'] : 0;
			if ( 0 < $value ) {
				update_user_meta( get_current_user_id(), 'logs_per_page', $value );
			}
		}
	}


	public function stock_log_page() {
		// Instantiate the StockControlLogListTable class
		$stock_log_list_table = new StockControlLogListTable();

		// Check if a search is submitted
		if ( isset( $_POST['nonce'] ) && wp_verify_nonce( sanitize_text_field( wp_unslash( $_POST['nonce'] ) ), 'search_stock_log' ) ) {
			$search = isset( $_POST['search'] ) ? sanitize_text_field( wp_unslash( $_POST['search'] ) ) : '';
		} else {
			$search = '';
		}

		// Prepare the items and display the table with the search parameter
		$stock_log_list_table->prepare_items( $search );

		// Display the table
		?>
		<div class="wrap">
		<h1><?php esc_html_e( 'Stock Log', 'stock-control' ); ?></h1>
			<form method="post" action="">
				<input type="text" name="search" placeholder="Search" value="<?php echo esc_attr( $search ); ?>">
				<input type="submit" name="submit" value="Search" class="button button-primary">
				<?php wp_nonce_field( 'search_stock_log', 'nonce' ); ?>
			</form>
			<?php $stock_log_list_table->display(); ?>
			<form method="post" action="">
				<input type="submit" name="clear_results" value="Clear Search Results" class="button button-primary">
				<?php wp_nonce_field( 'clear_results_nonce' ); ?>
			</form>
		</div>
		<?php
	}
}
