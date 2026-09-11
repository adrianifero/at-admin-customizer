<?php
/**
 * Custom dashboard widgets (legacy feature, cleaned up).
 *
 * @package AT_Admin_Palette
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Dashboard widgets module.
 */
final class ATAC_Dashboard_Widgets {

	const OPTION_KEY = 'atac_dashboard_widgets';

	/**
	 * Constructor.
	 */
	public function __construct() {
		add_action( 'wp_dashboard_setup', array( $this, 'register_widgets' ), 20 );
		add_action( 'wp_dashboard_setup', array( $this, 'maybe_remove_defaults' ), 999 );
	}

	/**
	 * Default settings.
	 *
	 * @return array
	 */
	public static function defaults() {
		return array(
			'remove_defaults' => false,
			'boxes'           => array(
				array(
					'title'   => '',
					'content' => '',
					'roles'   => array(),
					'context' => 'normal',
				),
				array(
					'title'   => '',
					'content' => '',
					'roles'   => array(),
					'context' => 'side',
				),
			),
		);
	}

	/**
	 * Get settings (with migration from old option keys).
	 *
	 * @return array
	 */
	public function get_settings() {
		$saved = get_option( self::OPTION_KEY, null );
		if ( ! is_array( $saved ) ) {
			$legacy = get_option( 'cad_dashboard_widgets', null );
			if ( is_array( $legacy ) ) {
				update_option( self::OPTION_KEY, $legacy, false );
				delete_option( 'cad_dashboard_widgets' );
				$saved = $legacy;
			}
		}
		if ( is_array( $saved ) ) {
			return wp_parse_args( $saved, self::defaults() );
		}

		// Migrate from 1.0.x option keys if present.
		$migrated = self::defaults();
		$old_remove = get_option( 'remove_default_metaboxes' );
		if ( $old_remove ) {
			$migrated['remove_defaults'] = (bool) $old_remove;
		}

		$title1 = get_option( 'Box01Title' );
		$content1 = get_option( 'Box01Content' );
		$title2 = get_option( 'Box02Title' );
		$content2 = get_option( 'Box02Content' );
		$roles = get_option( 'show_to_user_role' );

		if ( $title1 || $content1 || $title2 || $content2 ) {
			$migrated['boxes'][0]['title']   = is_string( $title1 ) ? $title1 : '';
			$migrated['boxes'][0]['content'] = is_string( $content1 ) ? $content1 : '';
			$migrated['boxes'][1]['title']   = is_string( $title2 ) ? $title2 : '';
			$migrated['boxes'][1]['content'] = is_string( $content2 ) ? $content2 : '';

			if ( is_array( $roles ) ) {
				$migrated['boxes'][0]['roles'] = isset( $roles[0] ) && is_array( $roles[0] ) ? array_keys( array_filter( $roles[0] ) ) : array();
				$migrated['boxes'][1]['roles'] = isset( $roles[1] ) && is_array( $roles[1] ) ? array_keys( array_filter( $roles[1] ) ) : array();
			}

			update_option( self::OPTION_KEY, $migrated, false );
		}

		return $migrated;
	}

	/**
	 * Save settings from the settings form.
	 */
	public function maybe_save_settings() {
		if ( empty( $_POST['atac_widgets_nonce'] ) ) {
			return;
		}
		if ( ! wp_verify_nonce( sanitize_text_field( wp_unslash( $_POST['atac_widgets_nonce'] ) ), 'atac_save_widgets' ) ) {
			return;
		}
		if ( ! current_user_can( 'manage_options' ) ) {
			return;
		}

		$settings = self::defaults();
		$settings['remove_defaults'] = ! empty( $_POST['atac_remove_defaults'] );

		for ( $i = 0; $i < 2; $i++ ) {
			$title_key   = 'atac_box_' . $i . '_title';
			$content_key = 'atac_box_' . $i . '_content';
			$roles_key   = 'atac_box_' . $i . '_roles';

			$settings['boxes'][ $i ]['title'] = isset( $_POST[ $title_key ] )
				? sanitize_text_field( wp_unslash( $_POST[ $title_key ] ) )
				: '';
			$settings['boxes'][ $i ]['content'] = isset( $_POST[ $content_key ] )
				? wp_kses_post( wp_unslash( $_POST[ $content_key ] ) )
				: '';
			$settings['boxes'][ $i ]['roles'] = array();
			if ( ! empty( $_POST[ $roles_key ] ) && is_array( $_POST[ $roles_key ] ) ) {
				foreach ( wp_unslash( $_POST[ $roles_key ] ) as $role ) { // phpcs:ignore WordPress.Security.ValidatedSanitizedInput.InputNotSanitized
					$settings['boxes'][ $i ]['roles'][] = sanitize_key( $role );
				}
			}
			$settings['boxes'][ $i ]['context'] = ( 0 === $i ) ? 'normal' : 'side';
		}

		update_option( self::OPTION_KEY, $settings, false );
		add_settings_error(
			'atac_dashboard_widgets',
			'atac_saved',
			__( 'Dashboard widget settings saved.', 'at-admin-palette' ),
			'success'
		);
	}

	/**
	 * Settings form HTML.
	 *
	 * @param array $settings Current settings.
	 */
	public function render_settings_form( $settings ) {
		settings_errors( 'atac_dashboard_widgets' );
		$roles = wp_roles()->roles;
		?>
		<form method="post" action="">
			<?php wp_nonce_field( 'atac_save_widgets', 'atac_widgets_nonce' ); ?>

			<p>
				<label>
					<input type="checkbox" name="atac_remove_defaults" value="1" <?php checked( ! empty( $settings['remove_defaults'] ) ); ?> />
					<?php echo esc_html__( 'Hide default WordPress dashboard widgets', 'at-admin-palette' ); ?>
				</label>
			</p>

			<?php foreach ( $settings['boxes'] as $index => $box ) : ?>
				<fieldset class="atac-box-fieldset">
					<legend>
						<?php
						echo esc_html(
							sprintf(
								/* translators: %d: box number */
								__( 'Custom box %d', 'at-admin-palette' ),
								$index + 1
							)
						);
						?>
					</legend>
					<p>
						<label>
							<?php echo esc_html__( 'Title', 'at-admin-palette' ); ?><br />
							<input type="text" class="regular-text" name="<?php echo esc_attr( 'atac_box_' . $index . '_title' ); ?>" value="<?php echo esc_attr( $box['title'] ); ?>" />
						</label>
					</p>
					<p>
						<label><?php echo esc_html__( 'Content', 'at-admin-palette' ); ?></label>
					</p>
					<?php
					wp_editor(
						$box['content'],
						'atac_box_' . $index . '_content',
						array(
							'textarea_name' => 'atac_box_' . $index . '_content',
							'textarea_rows' => 8,
							'media_buttons' => true,
						)
					);
					?>
					<p><?php echo esc_html__( 'Visible to roles:', 'at-admin-palette' ); ?></p>
					<ul class="atac-role-list">
						<?php foreach ( $roles as $role_key => $role ) : ?>
							<li>
								<label>
									<input
										type="checkbox"
										name="<?php echo esc_attr( 'atac_box_' . $index . '_roles[]' ); ?>"
										value="<?php echo esc_attr( $role_key ); ?>"
										<?php checked( in_array( $role_key, $box['roles'], true ) || in_array( $role['name'], $box['roles'], true ) ); ?>
									/>
									<?php echo esc_html( translate_user_role( $role['name'] ) ); ?>
								</label>
							</li>
						<?php endforeach; ?>
					</ul>
				</fieldset>
			<?php endforeach; ?>

			<?php submit_button( __( 'Save widget settings', 'at-admin-palette' ) ); ?>
		</form>
		<?php
	}

	/**
	 * Register custom boxes.
	 */
	public function register_widgets() {
		$settings = $this->get_settings();
		$user     = wp_get_current_user();
		$user_roles = (array) $user->roles;

		foreach ( $settings['boxes'] as $index => $box ) {
			if ( '' === trim( (string) $box['title'] ) && '' === trim( wp_strip_all_tags( (string) $box['content'] ) ) ) {
				continue;
			}

			if ( ! $this->user_can_see_box( $user_roles, (array) $box['roles'] ) ) {
				continue;
			}

			$id = 'atac_custom_box_' . $index;
			wp_add_dashboard_widget(
				$id,
				$box['title'] ? $box['title'] : __( 'Custom box', 'at-admin-palette' ),
				function () use ( $box ) {
					echo wp_kses_post( apply_filters( 'the_content', $box['content'] ) );
				},
				null,
				null,
				isset( $box['context'] ) ? $box['context'] : 'normal',
				'high'
			);
		}
	}

	/**
	 * Whether the current user roles match the box visibility list.
	 *
	 * @param array $user_roles Current user role slugs.
	 * @param array $allowed    Allowed role slugs or display names (legacy).
	 * @return bool
	 */
	private function user_can_see_box( $user_roles, $allowed ) {
		if ( empty( $allowed ) ) {
			return false;
		}

		$allowed_lower = array_map( 'strtolower', $allowed );
		$all_roles     = wp_roles()->roles;

		foreach ( $user_roles as $role ) {
			$role = strtolower( (string) $role );
			if ( in_array( $role, $allowed_lower, true ) ) {
				return true;
			}
			if ( isset( $all_roles[ $role ]['name'] ) ) {
				$name = strtolower( $all_roles[ $role ]['name'] );
				if ( in_array( $name, $allowed_lower, true ) ) {
					return true;
				}
			}
		}

		return false;
	}

	/**
	 * Remove default dashboard widgets when enabled.
	 */
	public function maybe_remove_defaults() {
		$settings = $this->get_settings();
		if ( empty( $settings['remove_defaults'] ) ) {
			return;
		}

		remove_action( 'welcome_panel', 'wp_welcome_panel' );

		$ids = array(
			'dashboard_site_health'    => 'normal',
			'dashboard_right_now'      => 'normal',
			'dashboard_activity'       => 'normal',
			'dashboard_quick_press'    => 'side',
			'dashboard_primary'        => 'side',
			'dashboard_incoming_links' => 'normal',
			'dashboard_plugins'        => 'normal',
			'dashboard_recent_drafts'  => 'side',
			'dashboard_recent_comments'=> 'normal',
			'dashboard_secondary'      => 'side',
		);

		foreach ( $ids as $id => $context ) {
			remove_meta_box( $id, 'dashboard', $context );
		}
	}
}
