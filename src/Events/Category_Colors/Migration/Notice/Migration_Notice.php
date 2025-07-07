<?php
/**
 * Handles the migration notice UI and user interaction for the Category Colors migration.
 *
 * @since 6.14.0
 *
 * @package TEC\Events\Category_Colors\Migration
 */

namespace TEC\Events\Category_Colors\Migration\Notice;

use TEC\Common\StellarWP\AdminNotices\AdminNotices;
use TEC\Events\Category_Colors\Migration\Plugin_Manager;
use TEC\Events\Category_Colors\Migration\Status;
use Tribe__Template;

/**
 * Class Migration_Notice
 *
 * @since 6.14.0
 *
 * @package TEC\Events\Category_Colors\Migration
 */
class Migration_Notice {
	/**
	 * @since 6.14.0
	 *
	 * @var Migration_Flow
	 */
	private Migration_Flow $flow;

	/**
	 * @var Tribe__Template
	 */
	protected $template;

	/**
	 * Constructor.
	 *
	 * @since 6.14.0
	 *
	 * @param Migration_Flow       $flow     The migration flow controller.
	 * @param Tribe__Template|null $template The template object.
	 */
	public function __construct( Migration_Flow $flow, ?Tribe__Template $template = null ) {
		$this->flow = $flow;
		if ( null === $template || empty( $template->get_template_folder() ) ) {
			$template = new Tribe__Template();
			$template->set_template_origin( \Tribe__Events__Main::instance() );
			$template->set_template_folder( 'src/admin-views/category-colors/partials/' );
			$template->set_template_context_extract( true );
			$template->set_template_folder_lookup( false );
		}
		$this->template = $template;
	}

	/**
	 * The notice ID for the migration prompt.
	 *
	 * @since 6.14.0
	 *
	 * @var string
	 */
	protected const MIGRATION_NOTICE_ID = 'tec_category_colors_migration_notice';

	/**
	 * The notice ID for the migration error.
	 *
	 * @since 6.14.0
	 *
	 * @var string
	 */
	protected const ERROR_NOTICE_ID = 'tec_category_colors_migration_error';

	/**
	 * Shows the migration notice if needed.
	 *
	 * @since 6.14.0
	 */
	public function maybe_show_migration_notice(): void {
		$status         = Status::get_migration_status();
		$current_status = $status['status'] ?? null;

		if ( ! tribe( Plugin_Manager::class )->is_tec_admin_page() ) {
			return;
		}

		// 1. Show the "Start Migration" thickbox notice if migration has not started.
		if ( $current_status === null || $current_status === Status::$not_started ) {
			add_thickbox();
			AdminNotices::show( self::MIGRATION_NOTICE_ID, $this->get_notice_message() )
				->urgency( 'warning' )
				->dismissible( true )
				->inline( true );
			// Output the Thickbox content in the footer using a static method.
			add_action( 'admin_footer', [ __CLASS__, 'render_thickbox_content' ] );

			return;
		}

		// 3. If migration is completed or skipped, show no notice.
		if (
			$current_status === Status::$postprocessing_completed ||
			$current_status === Status::$preprocessing_skipped
		) {
			return;
		}

		// 2. Show the background notice for all other in-progress statuses.
		AdminNotices::show(
			'tec_category_colors_migration_background_notice',
			sprintf(
				'<p><strong>%s</strong></p><p>%s</p>',
				__( 'Migration Started', 'the-events-calendar' ),
				__( 'Your category colors migration is running in the background. You can continue using the site while it processes.', 'the-events-calendar' )
			)
		)
			->urgency( 'info' )
			->dismissible( true )
			->inline( true );
	}

	/**
	 * Gets the appropriate notice message based on migration status.
	 *
	 * @since 6.14.0
	 *
	 * @return string The formatted message.
	 */
	protected function get_notice_message(): string {
		$title        = __( 'Category Colors Migration', 'the-events-calendar' );
		$thickbox_url = '#TB_inline?width=550&height=325&inlineId=tec-category-colors-migration-thickbox';
		$docs_url     = 'https://theeventscalendar.com/knowledgebase/k/migrating-category-colors/';

		return sprintf(
			'<p><strong>%1$s</strong></p>
			<p>%2$s</p>
			<p>
				<a href="%3$s" name="%4$s" class="thickbox button button-primary">%5$s</a>
				<a href="%7$s" class="button button-link" rel="noreferrer noopener" target="_blank">%6$s</a>
			</p>',
			esc_html( $title ),
			esc_html__( "We've detected that you're using the Category Colors plugin. This functionality is now included in The Events Calendar! To continue using category colors, please migrate your settings.", 'the-events-calendar' ),
			esc_attr( $thickbox_url ),
			esc_attr( $title ),
			esc_html__( 'Migrate Now', 'the-events-calendar' ),
			esc_html__( 'What happens during migration?', 'the-events-calendar' ),
			esc_url( $docs_url )
		);
	}

	/**
	 * Gets the template instance.
	 *
	 * @since 6.14.0
	 *
	 * @return Tribe__Template The template instance.
	 */
	public function get_template(): Tribe__Template {
		return $this->template;
	}

	/**
	 * Renders the thickbox content for the migration modal.
	 *
	 * @since 6.14.0
	 *
	 * @return void
	 */
	public static function render_thickbox_content(): void {
		$instance = tribe( static::class );
		$instance->get_template()->template( 'migration-modal' );
	}

	/**
	 * Handles the migration process when the user clicks the migration button.
	 *
	 * @since 6.14.0
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
				self::ERROR_NOTICE_ID,
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
		//phpcs:ignore WordPressVIPMinimum.Security.ExitAfterRedirect.NoExit
		wp_safe_redirect( wp_get_referer() ?: admin_url() );
		tribe_exit();
	}

	/**
	 * Gets a migration action button.
	 *
	 * @since 6.14.0
	 *
	 * @param string|null $text  Optional. Button text override.
	 * @param string      $style_class Optional. Button class.
	 *
	 * @return string Button HTML.
	 */
	protected function get_migration_action_button( string $text = null, string $style_class = 'button button-primary' ): string {
		$url = wp_nonce_url(
			admin_url( 'admin-post.php?action=tec_start_category_colors_migration' ),
			'tec_start_category_colors_migration'
		);

		$text ??= __( 'Start Category Colors Migration', 'the-events-calendar' );

		return sprintf(
			'<a href="%s" class="%s">%s</a>',
			esc_url( $url ),
			esc_attr( $style_class ),
			esc_html( $text )
		);
	}

	/**
	 * Gets the learn more button.
	 *
	 * @since 6.14.0
	 *
	 * @return string The Learn More button HTML.
	 */
	protected function get_learn_more_button(): string {
		return sprintf(
			'<a href="%s" class="button button-secondary">%s</a>',
			'https://evnt.is/category-colors-migration',
			__( 'Learn More', 'the-events-calendar' )
		);
	}

	/**
	 * Gets the success message.
	 *
	 * @since 6.14.0
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
