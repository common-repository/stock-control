<?php
namespace OACS\StockControl\Controllers\App;

use OACS\StockControl\Controllers\App\StockControlLoader; // All actions and filters
use OACS\StockControl\Controllers\App\StockControlI18n; // language
use OACS\StockControl\Views\StockControlAdmin; // admin settings
use OACS\StockControl\Views\StockControlPublic; // views output
use OACS\StockControl\Controllers\StockControlProductLog; // log product changes
use OACS\StockControl\Views\StockControlShowLogProduct;
use OACS\StockControl\Views\StockControlStockOverview;
use OACS\StockControl\Views\StockControlAdminSettings;
use OACS\StockControl\Controllers\StockControlSettings;
use OACS\StockControl\Controllers\StockControlSettingsSchedule;
use OACS\StockControl\Controllers\StockControlOverview;

/**
 * The core plugin class.
 *
 * This is used to define internationalization, admin-specific hooks, and
 * public-facing site hooks.
 *
 * Also maintains the unique identifier of this plugin as well as the current
 * version of the plugin.
 *
 * @since      1.0.0
 * @package    StockControl
 * @author     oacsTudio <oacsTudio>
 */
class StockControlPlugin {

	/**
	 * The loader that's responsible for maintaining and registering all hooks that power
	 * the plugin.
	 *
	 * @since    1.0.0
	 * @access   protected
	 * @var      StockControlLoader    $loader    Maintains and registers all hooks for the plugin.
	 */
	protected $loader;

	/**
	 * The unique identifier of this plugin.
	 *
	 * @since    1.0.0
	 * @access   protected
	 * @var      string    $plugin_name    The string used to uniquely identify this plugin.
	 */
	protected $plugin_name;

	/**
	 * The current version of the plugin.
	 *
	 * @since    1.0.0
	 * @access   protected
	 * @var      string    $version    The current version of the plugin.
	 */
	protected $version;

	/**
	 * Define the core functionality of the plugin.
	 *
	 * Set the plugin name and the plugin version that can be used throughout the plugin.
	 * Load the dependencies, define the locale, and set the hooks for the admin area and
	 * the public-facing side of the site.
	 *
	 * @since    1.0.0
	 */
	public function __construct() {
		if ( defined( 'STOCK_CONTROL_VERSION' ) ) {
			$this->version = STOCK_CONTROL_VERSION;
		} else {
			$this->version = '1.0.0';
		}
		$this->plugin_name = 'stock-control';

		$this->load_dependencies();
		$this->set_locale();
		$this->define_admin_hooks();
		$this->define_public_hooks();
	}

	/**
	 * Load the required dependencies for this plugin.
	 *
	 * Include the following files that make up the plugin:
	 *
	 * - Simple_Post_Likes_Loader. Orchestrates the hooks of the plugin.
	 * - Simple_Post_Likes_i18n. Defines internationalization functionality.
	 * - Simple_Post_Likes_Admin. Defines all hooks for the admin area.
	 * - Simple_Post_Likes_Public. Defines all hooks for the public side of the site.
	 *
	 * Create an instance of the loader which will be used to register the hooks
	 * with WordPress.
	 *
	 * @since    1.0.0
	 * @access   private
	 */
	private function load_dependencies() {

		$this->loader = new StockControlLoader(); // get Loader instance to make hooks work.
	}

	/**
	 * Define the locale for this plugin for internationalization.
	 *
	 * Uses the StockControlI18n class in order to set the domain and to register the hook
	 * with WordPress.
	 *
	 * @since    1.0.0
	 * @access   private
	 */
	private function set_locale() {

		$plugin_i18n = new StockControlI18n();

		$this->loader->add_action( 'plugins_loaded', $plugin_i18n, 'load_plugin_textdomain' );
	}

	/**
	 * Register all of the hooks related to the admin area functionality
	 * of the plugin.
	 *
	 * @since    1.0.0
	 * @access   private
	 */
	private function define_admin_hooks() {

		$plugin_admin    = new StockControlAdmin( $this->get_plugin_name(), $this->get_version() );
		$product_log     = new StockControlProductLog();
		$product_metabox = new StockControlShowLogProduct();
		$stock_overview  = new StockControlStockOverview();

		$admin_settings      = new StockControlAdminSettings();
		$admin_schedule      = new StockControlSettingsSchedule();
		$stock_settings      = new StockControlSettings();
		$overview_controller = new StockControlOverview();

		$this->loader->add_action( 'admin_enqueue_scripts', $plugin_admin, 'enqueue_styles' );
		$this->loader->add_action( 'admin_enqueue_scripts', $plugin_admin, 'enqueue_scripts' );

		$this->loader->add_action( 'woocommerce_product_set_stock', $product_log, 'log_stock_change', 10, 1 );

		$this->loader->add_action( 'admin_menu', $product_metabox, 'add_stock_log_menu' );
		$this->loader->add_action( 'admin_menu', $stock_overview, 'add_stock_overview_menu' );

		// Add settings tab
		$this->loader->add_filter( 'woocommerce_settings_tabs_array', $admin_settings, 'add_settings_tab', 50 );
		$this->loader->add_action( 'woocommerce_settings_tabs_stock_control', $admin_settings, 'settings_tab' );
		$this->loader->add_action( 'woocommerce_update_options_stock_control', $admin_settings, 'update_settings' );
		$this->loader->add_action( 'woocommerce_update_options_stock_control', $admin_settings, 'reset_delete_all_data_checkbox' );

		$this->loader->add_action( 'woocommerce_update_options_stock_control', $stock_settings, 'update_purge_dates' );

		$this->loader->add_action( 'wp', $admin_schedule, 'schedule_stock_purge' );
		$this->loader->add_action( 'purge_stock_data', $admin_schedule, 'purge_stock_data' );

		$this->loader->add_action( 'wp_ajax_save_stock_control', $overview_controller, 'save_stock_control_callback' );
	}

	/**
	 * Register all of the hooks related to the public-facing functionality
	 * of the plugin.
	 *
	 * @since    1.0.0
	 * @access   private
	 */
	private function define_public_hooks() {

		$plugin_public = new StockControlPublic( $this->get_plugin_name(), $this->get_version() );

		$this->loader->add_action( 'wp_enqueue_scripts', $plugin_public, 'enqueue_styles' );
		$this->loader->add_action( 'wp_enqueue_scripts', $plugin_public, 'enqueue_scripts' );
	}

	/**
	 * Run the loader to execute all of the hooks with WordPress.
	 *
	 * @since    1.0.0
	 */
	public function run() {
		$this->loader->run();
	}

	/**
	 * The name of the plugin used to uniquely identify it within the context of
	 * WordPress and to define internationalization functionality.
	 *
	 * @since     1.0.0
	 * @return    string    The name of the plugin.
	 */
	public function get_plugin_name() {
		return $this->plugin_name;
	}

	/**
	 * The reference to the class that orchestrates the hooks with the plugin.
	 *
	 * @since     1.0.0
	 * @return    StockControlLoader    Orchestrates the hooks of the plugin.
	 */
	public function get_loader() {
		return $this->loader;
	}

	/**
	 * Retrieve the version number of the plugin.
	 *
	 * @since     1.0.0
	 * @return    string    The version number of the plugin.
	 */
	public function get_version() {
		return $this->version;
	}
}
