<?php
/*
 * Plugin Name:       Stock Control
 * Plugin URI:        https://wordpress.org/plugins/stock-control/.
 * Description:       Bulk edit and log for WooCommerce products.
 * Version:           1.0.0
 * Author:            oacstudio
 * Author URI:        https://oacstudio.de
 * License:           GPL-2.0+
 * License URI:       http://www.gnu.org/licenses/gpl-2.0.txt
 * Text Domain:       stock-control
 * Domain Path:       /languages
 */


// If this file is called directly, abort.
if ( ! defined( 'WPINC' ) ) {
	die;
}

// Friendly advice:  namespace declarations in root plugin file will eat plugin settings links functions that don't use namespaces ;). Avoid Namespaces in the plugin root file alltogether. The plugin root file = procedual code for the win.

function oacs_sc_myplugin_settings_link( $links ) {
	$url           = get_admin_url() . 'admin.php?page=wc-settings&tab=stock_control';
	$settings_link = '<a href="' . $url . '">' . __( 'Settings', 'stock-control' ) . '</a>';
	array_unshift( $links, $settings_link );
	return $links;
}
add_filter( 'plugin_action_links_' . plugin_basename( __FILE__ ), 'oacs_sc_myplugin_settings_link' );


/**
 * Plugin version. - https://semver.org
 */
define( 'OACS_SC_STOCK_CONTROL_VERSION', '1.0.0' );

/**
 * Activation
 */

function oacs_sc_activate_stock_control() {
	// OACS\SimplePostLike\Controllers\App\SolidPostLikesActivator::activate();
}


/**
 * Deactivation.
 */
function oacs_sc_deactivate_stock_control() {
	// OACS\SimplePostLike\Controllers\App\SolidPostLikesDeactivator::deactivate();
}

/**
 * Deinstallation.
 */
function oacs_sc_deinstall_stock_control() {

}

/**
* Watch the Namespace syntax. Shoutout:
* https://developer.wordpress.org/reference/functions/register_activation_hook/#comment-2167
*/
register_activation_hook( __FILE__, 'OACS\\StockControl\\Controllers\\App\\StockControlActivator::activate_stock_control' );

// register_deactivation_hook( __FILE__, __NAMESPACE__ . '\deactivate_stock_control' );
// register_uninstall_hook( __FILE__, __NAMESPACE__ . '\deinstall_stock_control' );
/**
* Instead of: register_activation_hook( __FILE__, 'activate_stock_control' );
* Using the file constant did not work for me.
*/


// include the Composer autoload file
require plugin_dir_path( __FILE__ ) . 'vendor/autoload.php';

/**
 * Engage.
 */
function oacs_sc_run_stock_control() {

	$plugin = new OACS\StockControl\Controllers\App\StockControlPlugin();
	$plugin->run();

}
oacs_sc_run_stock_control();
