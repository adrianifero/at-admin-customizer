<?php
/**
 * Plugin Name: Customize Admin Dashboard
 * Plugin URI:  https://wordpress.org/plugins/customize-admin-dashboard/
 * Description: Brand your WordPress admin with your own colors, and curate dashboard widgets — lightweight, no white-label bloat.
 * Version:     1.1.0
 * Author:      Adrian Toro
 * Author URI:  https://adriantoro.com
 * Text Domain: customize-admin-dashboard
 * Domain Path: /languages
 * Requires at least: 6.0
 * Requires PHP: 7.4
 * Tested up to: 7.1
 * License:     GPLv2 or later
 * License URI: https://www.gnu.org/licenses/gpl-2.0.html
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

define( 'CAD_VERSION', '1.1.0' );
define( 'CAD_PLUGIN_FILE', __FILE__ );
define( 'CAD_PLUGIN_DIR', plugin_dir_path( __FILE__ ) );
define( 'CAD_PLUGIN_URL', plugin_dir_url( __FILE__ ) );

require_once CAD_PLUGIN_DIR . 'includes/class-brand-colors.php';
require_once CAD_PLUGIN_DIR . 'includes/class-dashboard-widgets.php';
require_once CAD_PLUGIN_DIR . 'includes/class-plugin.php';

/**
 * Bootstrap.
 *
 * @return CAD_Plugin
 */
function cad_plugin() {
	static $instance = null;
	if ( null === $instance ) {
		$instance = new CAD_Plugin();
	}
	return $instance;
}

cad_plugin();
