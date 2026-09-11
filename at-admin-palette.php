<?php
/**
 * Plugin Name: AT Admin Palette
 * Plugin URI:  https://wordpress.org/plugins/at-admin-palette/
 * Description: Brand your WordPress admin with your own colors, and curate dashboard widgets. Lightweight, no white-label bloat.
 * Version:     1.1.0
 * Author:      Adrian Toro
 * Author URI:  https://adriantoro.com
 * Text Domain: at-admin-palette
 * Domain Path: /languages
 * Requires at least: 6.0
 * Requires PHP: 7.4
 * License:     GPLv2 or later
 * License URI: https://www.gnu.org/licenses/gpl-2.0.html
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

define( 'ATAC_VERSION', '1.1.0' );
define( 'ATAC_PLUGIN_FILE', __FILE__ );
define( 'ATAC_PLUGIN_DIR', plugin_dir_path( __FILE__ ) );
define( 'ATAC_PLUGIN_URL', plugin_dir_url( __FILE__ ) );

require_once ATAC_PLUGIN_DIR . 'includes/class-brand-colors.php';
require_once ATAC_PLUGIN_DIR . 'includes/class-dashboard-widgets.php';
require_once ATAC_PLUGIN_DIR . 'includes/class-plugin.php';

/**
 * Bootstrap.
 *
 * @return ATAC_Plugin
 */
function atac_plugin() {
	static $instance = null;
	if ( null === $instance ) {
		$instance = new ATAC_Plugin();
	}
	return $instance;
}

atac_plugin();
