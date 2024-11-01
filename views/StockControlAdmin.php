<?php
namespace OACS\StockControl\Views;

/**
 * Asset management et al. for the b
 */
class StockControlAdmin {

	/**
	 * The ID of this plugin.
	 *
	 * @since    1.0.0
	 * @access   private
	 * @var      string    $plugin_name    The ID of this plugin.
	 */
	private $plugin_name;

	/**
	 * The version of this plugin.
	 *
	 * @since    1.0.0
	 * @access   private
	 * @var      string    $version    The current version of this plugin.
	 */
	private $version;

	/**
	 * Initialize the class and set its properties.
	 *
	 * @since    1.0.0
	 * @param      string $plugin_name       The name of this plugin.
	 * @param      string $version    The version of this plugin.
	 */
	public function __construct( $plugin_name, $version ) {

		$this->plugin_name = $plugin_name;
		$this->version     = $version;
	}

	/**
	 * Register the stylesheets for the admin area.
	 *
	 * @since    1.0.0
	 */
	public function enqueue_styles() {

		wp_enqueue_style( $this->plugin_name, plugin_dir_url( __FILE__ ) . 'admin/css/stock-control-admin.css', array(), $this->version, 'all' );
	}

	/**
	 * Register the JavaScript for the admin area.
	 *
	 * @since    1.0.0
	 */
	public function enqueue_scripts() {

		wp_enqueue_script(
			'stock_control_script',
			plugin_dir_url( __FILE__ ) . 'admin/js/stock-control-admin.js',
			array( 'jquery' ),
			$this->version,
			false
		);

		wp_localize_script(
			'stock_control_script',
			'stock_control_ajax',
			array(
				'ajax_url' => admin_url( 'admin-ajax.php' ),
				'nonce'    => wp_create_nonce( 'stock_control_nonce' ),
			)
		);

		wp_enqueue_script(
			'stock-control-settings',
			plugin_dir_url( __FILE__ ) . 'admin/js/stock-control-settings.js',
			array( 'jquery' ),
			$this->version,
			true
		);

		wp_localize_script(
			'stock-control-settings',
			'oacs_sc_stock_control',
			array(
				'disable_parent_inventory' => get_option( 'oacs_sc_stock_control_disable_parent_inventory' ),
			)
		);
	}
}
