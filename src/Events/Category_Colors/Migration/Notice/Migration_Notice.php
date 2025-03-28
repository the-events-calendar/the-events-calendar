<?php
/**
 * Handles the migration notice UI and user interaction for the Category Colors migration.
 *
 * @since   TBD
 *
 * @package TEC\Events\Category_Colors\Migration
 */

namespace TEC\Events\Category_Colors\Migration\Notice;

use TEC\Common\StellarWP\AdminNotices\AdminNotices;

/**
 * Class Migration_Notice
 *
 * @since   TBD
 *
 * @package TEC\Events\Category_Colors\Migration
 */
class Migration_Notice {
	/**
	 * @since TBD
	 *
	 * @var Migration_Flow
	 */
	private Migration_Flow $flow;

	/**
	 * Constructor.
	 *
	 * @since TBD
	 *
	 * @param Migration_Flow $flow The migration flow controller.
	 */
	public function __construct( Migration_Flow $flow ) {
		$this->flow = $flow;
	}

	/**
	 * The notice ID for the migration prompt.
	 *
	 * @since TBD
	 *
	 * @var string
	 */
	protected string $migration_notice_id = 'tec_category_colors_migration_notice';

	/**
	 * The notice ID for the migration success.
	 *
	 * @since TBD
	 *
	 * @var string
	 */
	protected string $success_notice_id = 'tec_category_colors_migration_success';

	/**
	 * The notice ID for the migration error.
	 *
	 * @since TBD
	 *
	 * @var string
	 */
	protected string $error_notice_id = 'tec_category_colors_migration_error';

	/**
	 * Sets up the admin UI hooks.
	 *
	 * @since TBD
	 */
	public function hook(): void {
		add_action( 'admin_init', [ $this, 'maybe_show_migration_notice' ] );
		add_action( 'admin_post_tec_start_category_colors_migration', [ $this, 'handle_migration' ] );
	}

	/**
	 * Shows the migration notice if needed.
	 *
	 * @since TBD
	 */
	public function maybe_show_migration_notice(): void {
		// Check if we should force show the notice.
		$force_show = apply_filters( 'tec_events_category_colors_force_migration_notice', true );

		// Only show if forced or if conditions are met.
		if ( ! $force_show && ! $this->flow->should_show_migration() ) {
			return;
		}

		$message = sprintf(
			'<p><strong>%s</strong></p><p>%s</p><p>%s</p>',
			__( 'Important: Category Colors Migration Required', 'the-events-calendar' ),
			__( "We've detected that you're using the Category Colors plugin. This functionality is now included in The Events Calendar! To continue using category colors, please migrate your settings.", 'the-events-calendar' ),
			$this->get_migration_button()
		);

		AdminNotices::show( $this->migration_notice_id, $message )
			->urgency( 'warning' )
			->dismissible( false )
			->inline( true );
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

		$result = $this->flow->initialize();

		if ( is_wp_error( $result ) ) {
			// Show error notice.
			AdminNotices::show(
				$this->error_notice_id,
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
			// Show success notice.
			AdminNotices::show( $this->success_notice_id, $this->get_success_message() )
				->urgency( 'success' )
				->dismissible( true )
				->inline( true );
		}

		//phpcs:ignore WordPressVIPMinimum.Security.ExitAfterRedirect.NoExit
		wp_safe_redirect( admin_url( 'edit-tags.php?taxonomy=tribe_events_cat&post_type=tribe_events' ) );
		tribe_exit();
	}

	/**
	 * Gets the migration button HTML.
	 *
	 * @since TBD
	 *
	 * @return string The migration button HTML.
	 */
	protected function get_migration_button(): string {
		$url = wp_nonce_url(
			admin_url( 'admin-post.php?action=tec_start_category_colors_migration' ),
			'tec_start_category_colors_migration'
		);

		return sprintf(
			'<a href="%s" class="button button-primary">%s</a> <a href="%s" class="button button-secondary">%s</a>',
			esc_url( $url ),
			esc_html__( 'Start Category Colors Migration', 'the-events-calendar' ),
			'https://evnt.is/category-colors-migration',
			esc_html__( 'Learn More', 'the-events-calendar' )
		);
	}

	/**
	 * Gets the success message.
	 *
	 * @since TBD
	 *
	 * @return string The success message.
	 */
	protected function get_success_message(): string {
		return sprintf(
			'<p><strong>%s</strong></p><p>%s</p><p><a href="%s" class="button button-primary">%s</a> <a href="%s" class="button button-secondary">%s</a></p>',
			__( 'Migration Started', 'the-events-calendar' ),
			__( 'Your category colors migration has been scheduled. The migration will run in the background. You can continue using the site while it processes.', 'the-events-calendar' ),
			admin_url( 'edit-tags.php?taxonomy=tribe_events_cat&post_type=tribe_events' ),
			__( 'View Categories', 'the-events-calendar' ),
			'https://evnt.is/category-colors-migration',
			__( 'Learn More', 'the-events-calendar' )
		);
	}
}
