<?php
/**
 * Admin brand colors: live panel + CSS custom properties.
 *
 * @package AT_Admin_Customizer
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Brand / palette module.
 */
final class CAD_Brand_Colors {

	const OPTION_KEY = 'cad_brand_colors';

	/**
	 * Constructor.
	 */
	public function __construct() {
		add_action( 'admin_enqueue_scripts', array( $this, 'enqueue_assets' ) );
		add_action( 'admin_head', array( $this, 'print_live_css' ), 99 );
		add_action( 'admin_footer', array( $this, 'render_panel_markup' ) );
		add_action( 'wp_ajax_cad_save_brand_colors', array( $this, 'ajax_save' ) );
		add_action( 'wp_ajax_cad_reset_brand_colors', array( $this, 'ajax_reset' ) );
	}

	/**
	 * Default palette: WordPress admin defaults (no rebrand until the user chooses).
	 *
	 * @return array<string,string>
	 */
	public static function defaults() {
		return array(
			'menu_bg'         => '#1d2327',
			'menu_text'       => '#f0f0f1',
			'menu_highlight'  => '#2271b1',
			'admin_bar'       => '#1d2327',
			'primary_button'  => '#2271b1',
			'link'            => '#2271b1',
		);
	}

	/**
	 * Generic starter palettes (not tied to any business).
	 *
	 * @return array<string,array{label:string,colors:array<string,string>}>
	 */
	public static function presets() {
		return array(
			'default' => array(
				'label'  => __( 'WordPress default', 'at-admin-customizer' ),
				'colors' => self::defaults(),
			),
			'slate'   => array(
				'label'  => __( 'Slate', 'at-admin-customizer' ),
				'colors' => array(
					'menu_bg'        => '#2c3338',
					'menu_text'      => '#f0f0f1',
					'menu_highlight' => '#3c434a',
					'admin_bar'      => '#1d2327',
					'primary_button' => '#3858e9',
					'link'           => '#3858e9',
				),
			),
			'ocean'   => array(
				'label'  => __( 'Ocean', 'at-admin-customizer' ),
				'colors' => array(
					'menu_bg'        => '#0b3d4a',
					'menu_text'      => '#e8f4f7',
					'menu_highlight' => '#14859b',
					'admin_bar'      => '#082c36',
					'primary_button' => '#14859b',
					'link'           => '#0e6e80',
				),
			),
			'forest'  => array(
				'label'  => __( 'Forest', 'at-admin-customizer' ),
				'colors' => array(
					'menu_bg'        => '#1e3323',
					'menu_text'      => '#eef5ef',
					'menu_highlight' => '#2f6b3a',
					'admin_bar'      => '#152318',
					'primary_button' => '#2f6b3a',
					'link'           => '#246b34',
				),
			),
			'warm'    => array(
				'label'  => __( 'Warm', 'at-admin-customizer' ),
				'colors' => array(
					'menu_bg'        => '#3a2a22',
					'menu_text'      => '#faf3ee',
					'menu_highlight' => '#c45c26',
					'admin_bar'      => '#2b1f19',
					'primary_button' => '#c45c26',
					'link'           => '#a84b1c',
				),
			),
		);
	}

	/**
	 * Field labels for the panel.
	 *
	 * @return array<string,string>
	 */
	public static function field_labels() {
		return array(
			'menu_bg'        => __( 'Menu background', 'at-admin-customizer' ),
			'menu_text'      => __( 'Menu text', 'at-admin-customizer' ),
			'menu_highlight' => __( 'Menu highlight', 'at-admin-customizer' ),
			'admin_bar'      => __( 'Admin bar', 'at-admin-customizer' ),
			'primary_button' => __( 'Primary button', 'at-admin-customizer' ),
			'link'           => __( 'Links', 'at-admin-customizer' ),
		);
	}

	/**
	 * Saved colors merged with defaults.
	 *
	 * @return array<string,string>
	 */
	public function get_colors() {
		$saved = get_option( self::OPTION_KEY, array() );
		if ( ! is_array( $saved ) ) {
			$saved = array();
		}
		$colors = array_merge( self::defaults(), $saved );
		foreach ( $colors as $key => $value ) {
			$colors[ $key ] = $this->sanitize_hex( $value, self::defaults()[ $key ] );
		}
		return $colors;
	}

	/**
	 * Whether saved colors differ from WordPress defaults.
	 *
	 * @return bool
	 */
	public function is_customized() {
		return self::defaults() !== $this->get_colors();
	}

	/**
	 * Sanitize a hex color.
	 *
	 * @param string $color   Input.
	 * @param string $fallback Fallback.
	 * @return string
	 */
	public function sanitize_hex( $color, $fallback = '#000000' ) {
		$color = is_string( $color ) ? trim( $color ) : '';
		if ( preg_match( '/^#([A-Fa-f0-9]{3}){1,2}$/', $color ) ) {
			return strtolower( $color );
		}
		$sanitized = sanitize_hex_color( $color );
		return $sanitized ? strtolower( $sanitized ) : $fallback;
	}

	/**
	 * Enqueue panel assets on all admin screens for capable users.
	 *
	 * @param string $hook_suffix Current admin page.
	 */
	public function enqueue_assets( $hook_suffix ) {
		unset( $hook_suffix );

		if ( ! current_user_can( 'manage_options' ) ) {
			return;
		}

		wp_enqueue_style(
			'cad-color-panel',
			CAD_PLUGIN_URL . 'assets/css/color-panel.css',
			array(),
			CAD_VERSION
		);

		wp_enqueue_style(
			'cad-settings',
			CAD_PLUGIN_URL . 'assets/css/settings.css',
			array(),
			CAD_VERSION
		);

		wp_enqueue_script(
			'cad-color-panel',
			CAD_PLUGIN_URL . 'assets/js/color-panel.js',
			array(),
			CAD_VERSION,
			true
		);

		wp_localize_script(
			'cad-color-panel',
			'cadColorPanel',
			array(
				'ajaxUrl'  => admin_url( 'admin-ajax.php' ),
				'nonce'    => wp_create_nonce( 'cad_brand_colors' ),
				'colors'   => $this->get_colors(),
				'defaults' => self::defaults(),
				'presets'  => self::presets(),
				'labels'   => self::field_labels(),
				'i18n'     => array(
					'title'           => __( 'Admin colors', 'at-admin-customizer' ),
					'subtitle'        => __( 'Choose a starter palette or pick your own. Changes preview live. Save when you like them.', 'at-admin-customizer' ),
					'starters'        => __( 'Starters', 'at-admin-customizer' ),
					'yourColors'      => __( 'Your colors', 'at-admin-customizer' ),
					'save'            => __( 'Save palette', 'at-admin-customizer' ),
					'reset'           => __( 'Reset to default', 'at-admin-customizer' ),
					'close'           => __( 'Close', 'at-admin-customizer' ),
					'saved'           => __( 'Palette saved.', 'at-admin-customizer' ),
					'resetDone'       => __( 'Reset to WordPress default.', 'at-admin-customizer' ),
					'error'           => __( 'Could not save. Try again.', 'at-admin-customizer' ),
					'openAria'        => __( 'Open admin color customizer', 'at-admin-customizer' ),
					'saving'          => __( 'Saving…', 'at-admin-customizer' ),
				),
			)
		);
	}

	/**
	 * Print applied CSS variables + rules when customized.
	 */
	public function print_live_css() {
		if ( ! is_admin() ) {
			return;
		}

		$colors = $this->get_colors();
		if ( self::defaults() === $colors ) {
			// Still print vars so the live preview panel can override them.
			echo '<style id="cad-brand-vars">' . $this->build_css( $colors, false ) . '</style>'; // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped
			return;
		}

		echo '<style id="cad-brand-vars">' . $this->build_css( $colors, true ) . '</style>'; // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped
	}

	/**
	 * Build CSS from a color map.
	 *
	 * @param array<string,string> $colors Color map.
	 * @param bool                 $apply  Whether to apply theme rules (not only vars).
	 * @return string
	 */
	public function build_css( $colors, $apply = true ) {
		$vars = sprintf(
			':root{--cad-menu-bg:%1$s;--cad-menu-text:%2$s;--cad-menu-highlight:%3$s;--cad-admin-bar:%4$s;--cad-primary:%5$s;--cad-link:%6$s;}',
			$colors['menu_bg'],
			$colors['menu_text'],
			$colors['menu_highlight'],
			$colors['admin_bar'],
			$colors['primary_button'],
			$colors['link']
		);

		if ( ! $apply ) {
			return $vars;
		}

		$rules = <<<'CSS'
#wpadminbar{background:var(--cad-admin-bar)!important}
#wpadminbar .ab-item,#wpadminbar a.ab-item,#wpadminbar>#wp-toolbar span.ab-label,#wpadminbar>#wp-toolbar span.noticon{color:#fff!important}
#adminmenuback,#adminmenuwrap,#adminmenu{background:var(--cad-menu-bg)!important}
#adminmenu a{color:var(--cad-menu-text)!important}
#adminmenu div.wp-menu-image:before{color:var(--cad-menu-text)!important}
#adminmenu li.menu-top:hover,#adminmenu li.opensub>a.menu-top,#adminmenu li>a.menu-top:focus{background:var(--cad-menu-highlight)!important;color:#fff!important}
#adminmenu li.wp-has-current-submenu a.wp-has-current-submenu,#adminmenu li.current a.menu-top,#adminmenu .wp-menu-arrow,#adminmenu .wp-has-current-submenu .wp-submenu .wp-submenu-head{background:var(--cad-menu-highlight)!important}
#adminmenu .wp-submenu{background:#1a1d20!important}
body.wp-core-ui .button-primary{background:var(--cad-primary)!important;border-color:var(--cad-primary)!important;color:#fff!important}
body.wp-core-ui .button-primary:hover,body.wp-core-ui .button-primary:focus{filter:brightness(1.08)}
a,body a{color:var(--cad-link)}
#adminmenu .awaiting-mod,#adminmenu .update-plugins{background:var(--cad-primary)!important}
CSS;

		return $vars . $rules;
	}

	/**
	 * Panel root (filled by JS).
	 */
	public function render_panel_markup() {
		if ( ! current_user_can( 'manage_options' ) ) {
			return;
		}
		echo '<div id="cad-color-root" hidden></div>';
	}

	/**
	 * AJAX: save palette.
	 */
	public function ajax_save() {
		check_ajax_referer( 'cad_brand_colors', 'nonce' );

		if ( ! current_user_can( 'manage_options' ) ) {
			wp_send_json_error( array( 'message' => 'forbidden' ), 403 );
		}

		$incoming = isset( $_POST['colors'] ) ? wp_unslash( $_POST['colors'] ) : array(); // phpcs:ignore WordPress.Security.ValidatedSanitizedInput.InputNotSanitized
		if ( is_string( $incoming ) ) {
			$decoded = json_decode( $incoming, true );
			$incoming = is_array( $decoded ) ? $decoded : array();
		}
		if ( ! is_array( $incoming ) ) {
			wp_send_json_error( array( 'message' => 'invalid' ), 400 );
		}

		$defaults = self::defaults();
		$clean    = array();
		foreach ( $defaults as $key => $fallback ) {
			$value = isset( $incoming[ $key ] ) ? $incoming[ $key ] : $fallback;
			$clean[ $key ] = $this->sanitize_hex( $value, $fallback );
		}

		update_option( self::OPTION_KEY, $clean, false );
		wp_send_json_success(
			array(
				'colors' => $clean,
				'css'    => $this->build_css( $clean, true ),
			)
		);
	}

	/**
	 * AJAX: reset to defaults.
	 */
	public function ajax_reset() {
		check_ajax_referer( 'cad_brand_colors', 'nonce' );

		if ( ! current_user_can( 'manage_options' ) ) {
			wp_send_json_error( array( 'message' => 'forbidden' ), 403 );
		}

		delete_option( self::OPTION_KEY );
		$defaults = self::defaults();
		wp_send_json_success(
			array(
				'colors' => $defaults,
				'css'    => $this->build_css( $defaults, false ),
			)
		);
	}
}
