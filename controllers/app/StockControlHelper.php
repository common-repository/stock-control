<?php

namespace OACS\StockControl\Controllers\App;

class StockControlHelper {

	public static $plugin_path           = __DIR__ . '/..';
	public static $plugin_version        = '1.0.0';
	public static $table_name            = 'stock_log';
	public static $admin_page_slug       = 'stock_control';
	public static $plugin_view_directory = __DIR__ . '/views';
	public static $failed_nonce_message  = 'Failed security check';
	public static $plugin_main_php_file  = __DIR__ . '/..' . '/stock-control.php';
	public static $settings_page_slug    = 'stock_control';
	public static $review_link           = 'https://wordpress.org/support/plugin/stock-control/reviews/#new-post';
	public static $stock_control_link    = 'https://stock-control.app/';
	public static $support_link          = 'https://wordpress.org/support/plugin/stock-control/';
}
