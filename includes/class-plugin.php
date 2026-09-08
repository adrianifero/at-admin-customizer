<?php
/**
 * Plugin bootstrap and settings screen.
 *
 * @package AT_Admin_Customizer
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Main plugin class.
 */
final class CAD_Plugin {

	/**
	 * Brand colors module.
	 *
	 * @var CAD_Brand_Colors
	 */
	public $brand;

	/**
	 * Dashboard widgets module.
	 *
	 * @var CAD_Dashboard_Widgets
	 */
	public $widgets;

	/**
	 * Constructor.
	 */
	public function __construct() {
		$this->brand   = new CAD_Brand_Colors();
		$this->widgets = new CAD_Dashboard_Widgets();

		add_action( 'admin_menu', array( $this, 'register_settings_page' ) );
		add_filter( 'plugin_action_links_' . plugin_basename( CAD_PLUGIN_FILE ), array( $this, 'plugin_action_links' ) );
	}

	/**
	 * Settings → AT Admin Customizer.
	 */
	public function register_settings_page() {
		add_options_page(
			__( 'AT Admin Customizer', 'at-admin-customizer' ),
			__( 'AT Admin Customizer', 'at-admin-customizer' ),
			'manage_options',
			'at-admin-customizer',
			array( $this, 'render_settings_page' )
		);
	}

	/**
	 * Plugins list quick links.
	 *
	 * @param array $links Existing links.
	 * @return array
	 */
	public function plugin_action_links( $links ) {
		$url = admin_url( 'options-general.php?page=at-admin-customizer' );
		array_unshift(
			$links,
			sprintf(
				'<a href="%s">%s</a>',
				esc_url( $url ),
				esc_html__( 'Settings', 'at-admin-customizer' )
			)
		);
		return $links;
	}

	/**
	 * Settings page: points users at the live color panel + widget options.
	 */
	public function render_settings_page() {
		if ( ! current_user_can( 'manage_options' ) ) {
			wp_die( esc_html__( 'You do not have sufficient permissions to access this page.', 'at-admin-customizer' ) );
		}

		$this->widgets->maybe_save_settings();
		$widget_settings = $this->widgets->get_settings();
		?>
		<div class="wrap cad-settings-wrap">
			<h1><?php echo esc_html__( 'AT Admin Customizer', 'at-admin-customizer' ); ?></h1>

			<div class="cad-settings-card">
				<h2><?php echo esc_html__( 'Admin colors', 'at-admin-customizer' ); ?></h2>
				<p><?php echo esc_html__( 'Pick your own palette right in the admin. Use the color button in the bottom-right corner of any admin screen, or open the panel from here.', 'at-admin-customizer' ); ?></p>
				<p>
					<button type="button" class="button button-primary" id="cad-open-color-panel">
						<?php echo esc_html__( 'Open color customizer', 'at-admin-customizer' ); ?>
					</button>
				</p>
			</div>

			<div class="cad-settings-card">
				<h2><?php echo esc_html__( 'Dashboard widgets', 'at-admin-customizer' ); ?></h2>
				<p><?php echo esc_html__( 'Add up to two custom dashboard boxes, choose which roles see them, and optionally hide the default WordPress dashboard widgets.', 'at-admin-customizer' ); ?></p>
				<?php $this->widgets->render_settings_form( $widget_settings ); ?>
			</div>
		</div>
		<?php
	}
}
