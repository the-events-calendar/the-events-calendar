<?php
/**
 * Manages the legacy Category Colors plugin and its migration.
 *
 * @since   TBD
 *
 * @package TEC\Events\Category_Colors\Migration
 */

namespace TEC\Events\Category_Colors\Migration;

use TEC\Common\StellarWP\AdminNotices\AdminNotice;
use TEC\Common\StellarWP\AdminNotices\AdminNotices;
use TEC\Events\Category_Colors\Migration\Notice\Migration_Flow;
use Tribe__Events__Main;
use Tribe__Template;

/**
 * Class Plugin_Manager
 *
 * @since TBD
 */
class Plugin_Manager {
	/**
	 * The legacy plugin's main file path.
	 *
	 * @since TBD
	 *
	 * @var string
	 */
	private const PLUGIN_FILE = 'the-events-calendar-category-colors/the-events-calendar-category-colors.php';

	/**
	 * Register hooks that should run when the legacy plugin is active.
	 *
	 * @since TBD
	 *
	 * @return void
	 */
	public function register_legacy_hooks(): void {
		if ( ! $this->is_plugin_active() ) {
			return;
		}

		// Inline notice on the Category Colors settings tab.
		add_action(
			'tribe_settings_before_content_tab_category-colors',
			function () {
				self::render_category_colors_notice();
			}
		);

		// Add thickbox support and modal content for migration.
		add_action( 'admin_footer', [ __CLASS__, 'render_thickbox_content' ] );
		add_filter( 'tribe_settings_no_save_tabs', [ $this, 'disable_save_button_for_category_colors' ], 10, 3 );

		// Register the migration handler.
		add_action( 'admin_post_tec_start_category_colors_migration', [ $this, 'handle_migration' ] );
	}

	/**
	 * Render the Category Colors migration notice.
	 *
	 * @since TBD
	 *
	 * @return void
	 */
	public static function render_category_colors_notice() {
		$thickbox_url   = '#TB_inline?width=550&height=325&inlineId=tec-category-colors-migration-thickbox';
		$migrate_url    = esc_attr( $thickbox_url );
		$learn_more_url = esc_url( 'https://theeventscalendar.com/knowledgebase/k/migrating-category-colors/' );

		$message = sprintf(
			'<p>%s</p><p>%s</p>
                <p>
                    <a href="%s" name="%s" class="thickbox button button-primary">%s</a>
                    <a href="%s" class="button" target="_blank" rel="noopener noreferrer">%s</a>
                </p>',
			esc_html__( 'We\'ve detected that you\'re using the Category Colors plugin. This functionality is now included in The Events Calendar!', 'the-events-calendar' ),
			esc_html__( 'To continue using category colors, please migrate your settings. The settings have been disabled until you complete the migration.', 'the-events-calendar' ),
			$migrate_url,
			esc_attr__( 'Category Colors Migration', 'the-events-calendar' ),
			esc_html__( 'Migrate Now', 'the-events-calendar' ),
			$learn_more_url,
			esc_html__( 'Learn More', 'the-events-calendar' )
		);
		$notice  = ( new AdminNotice( 'category-colors-inline', $message ) )
			->urgency( 'info' )
			->inline()
			->dismissible( false )
			->withWrapper();
		echo '<div class="tec-settings-form__header-block tec-settings-form__header-block--horizontal">' . esc_html( AdminNotices::render( $notice, false ) ) . '</div>';
		// Ensure thickbox scripts/styles are loaded.
		add_thickbox();
	}

	/**
	 * Renders the thickbox content for the migration modal.
	 *
	 * @since TBD
	 *
	 * @return void
	 */
	public static function render_thickbox_content(): void {
		$template = new Tribe__Template();
		$template->set_template_origin( Tribe__Events__Main::instance() );
		$template->set_template_folder( 'src/admin-views/category-colors/partials/' );
		$template->set_template_context_extract( true );
		$template->set_template_folder_lookup( false );
		$context = [];
		$template->template( 'migration-modal', $context );
	}

	/**
	 * Checks if the old Category Colors plugin is active.
	 *
	 * @since TBD
	 *
	 * @return bool
	 */
	public function is_plugin_active(): bool {
		return is_plugin_active( self::PLUGIN_FILE );
	}

	/**
	 * Checks if migration has not started.
	 *
	 * @since TBD
	 *
	 * @return bool
	 */
	public function is_migration_not_started(): bool {
		$migration_status = Status::get_migration_status();

		return (
			! isset( $migration_status['status'] )
			|| $migration_status['status'] === Status::$not_started
		);
	}

	/**
	 * Checks if the plugin has any category meta values.
	 *
	 * @since TBD
	 *
	 * @return bool
	 */
	public function has_category_meta(): bool {
		$categories = get_terms(
			[
				'taxonomy'   => Tribe__Events__Main::TAXONOMY,
				'hide_empty' => false,
				'number'     => 1,
			]
		);

		if ( empty( $categories ) ) {
			return false;
		}

		// Check for border color meta (primary in new system).
		return ! empty( get_term_meta( $categories[0]->term_id, Config::META_KEY_PREFIX . Config::META_KEY_MAP['border'], true ) );
	}

	/**
	 * Checks if the plugin has original settings.
	 *
	 * @since TBD
	 *
	 * @return bool
	 */
	public function has_original_settings(): bool {
		return ! empty( get_option( Config::ORIGINAL_SETTINGS_OPTION ) );
	}

	/**
	 * Deactivates the legacy plugin.
	 *
	 * @since TBD
	 *
	 * @return void
	 */
	public function deactivate_plugin(): void {
		deactivate_plugins( self::PLUGIN_FILE );
	}

	/**
	 * Prevents the legacy plugin from being reactivated.
	 *
	 * @since TBD
	 *
	 * @return void
	 */
	public function prevent_reactivation(): void {
		add_filter(
			'plugin_action_links_' . self::PLUGIN_FILE,
			function ( $actions ) {
				unset( $actions['activate'] );

				return $actions;
			}
		);
	}

	/**
	 * Handles the migration process when the user clicks the migration button.
	 *
	 * @since TBD
	 */
	public function handle_migration(): void {
		check_admin_referer( 'tec_start_category_colors_migration' );

		if ( ! current_user_can( 'manage_options' ) ) {
			wp_die( esc_html__( 'You do not have sufficient permissions to perform this action.', 'the-events-calendar' ) );
		}

		$flow   = tribe( Migration_Flow::class );
		$result = $flow->initialize();

		if ( is_wp_error( $result ) ) {
			// Show error notice.
			AdminNotices::show(
				'tec_category_colors_migration_error',
				sprintf(
					'<p><strong>%s</strong></p><p>%s</p>',
					__( 'Migration Error', 'the-events-calendar' ),
					$result->get_error_message()
				)
			)
				->urgency( 'error' )
				->dismissible( true )
				->inline( true );
		} else {
			// Set a one-time user meta flag for the background notice.
			update_user_meta( get_current_user_id(), '_tec_category_colors_migration_notice', 1 );
		}

		// Redirect back to the same page (no redirect to category page).
		wp_safe_redirect( wp_get_referer() ?: admin_url() );
		tribe_exit();
	}

	/**
	 * Disables the save button for the Category Colors settings tab.
	 *
	 * @since TBD
	 *
	 * @param array<string> $no_save_tabs The tabs that should not save.
	 * @param string        $admin_page   The admin page.
	 * @param array<string> $settings     The settings.
	 *
	 * @return array<string> The tabs that should not save.
	 */
	public function disable_save_button_for_category_colors(
		$no_save_tabs,
		$admin_page,
		$settings
	) {
		$no_save_tabs[] = 'category-colors';

		return $no_save_tabs;
	}
}
