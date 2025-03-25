<?php
/**
 * Handles the admin UI for the Category Colors migration.
 *
 * @since   TBD
 *
 * @package TEC\Events\Category_Colors\Migration
 */

namespace TEC\Events\Category_Colors\Migration;

use TEC\Common\StellarWP\AdminNotices\AdminNotices;
use TEC\Events\Category_Colors\CSS\Generator;
use TEC\Events\Category_Colors\Migration\Scheduler\Preprocessing_Action;

/**
 * Class Admin_UI
 *
 * @since   TBD
 *
 * @package TEC\Events\Category_Colors\Migration
 */
class Admin_UI {
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
		add_action( 'admin_init', [ $this, 'maybe_reset_migration' ] );
		add_action( 'admin_post_tec_start_category_colors_migration', [ $this, 'handle_migration' ] );
	}

	/**
	 * Shows the migration notice if needed.
	 *
	 * @since TBD
	 */
	public function maybe_show_migration_notice(): void {
		// Only show if old plugin data exists and migration hasn't completed
		if ( ! $this->should_show_migration_notice() ) {
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
			wp_die( __( 'You do not have sufficient permissions to perform this action.', 'the-events-calendar' ) );
		}

		// Initialize the migration
		$handler = tribe( Handler::class );
		$handler->initialize_migration();

		// Schedule the preprocessing action
		$preprocessing_action = tribe( Preprocessing_Action::class );
		$preprocessing_action->schedule();
		printr( 'Scheduled' );
		die();

		// Show success notice
		AdminNotices::show( $this->success_notice_id, $this->get_success_message() )
			->urgency( 'success' )
			->dismissible( true )
			->inline( true );

		wp_safe_redirect( admin_url( 'edit-tags.php?taxonomy=tribe_events_cat&post_type=tribe_events' ) );
		exit;
	}

	/**
	 * Checks if the migration notice should be shown.
	 *
	 * @since TBD
	 *
	 * @return bool Whether the migration notice should be shown.
	 */
	protected function should_show_migration_notice(): bool {
		$status = Handler::get_migration_status();

		// Don't show if migration is already completed
		if ( Status::$postprocessing_completed === $status['status'] ) {
			return false;
		}

		// Check if old plugin data exists (teccc_options)
		$old_options = get_option( 'teccc_options' );
		if ( empty( $old_options ) ) {
			return false;
		}

		// Check if old plugin is active
		if ( ! is_plugin_active( 'the-events-calendar-category-colors/the-events-calendar-category-colors.php' ) ) {
			return false;
		}

		return true;
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

	/**
	 * Helper method to reset migration status for testing purposes.
	 * This method should only be used during development and testing.
	 *
	 * @since TBD
	 *
	 * Usage: Add ?tec_reset_category_colors_migration=1
	 */
	public function maybe_reset_migration(): void {
		if ( ! isset( $_GET['tec_reset_category_colors_migration'] ) || $_GET['tec_reset_category_colors_migration'] !== '1' ) {
			return;
		}

		// Get all categories to reset their meta
		$categories = get_terms(
			[
				'taxonomy'   => Handler::$taxonomy,
				'hide_empty' => false,
			]
		);

		if ( is_wp_error( $categories ) ) {
			wp_die( $categories->get_error_message() );
		}

		// Reset all category meta
		foreach ( $categories as $category ) {
			delete_term_meta( $category->term_id, 'tec-events-cat-colors-primary' );
			delete_term_meta( $category->term_id, 'tec-events-cat-colors-secondary' );
			delete_term_meta( $category->term_id, 'tec-events-cat-colors-text' );
			delete_term_meta( $category->term_id, 'tec-events-cat-colors-priority' );
		}

		// Reset migration status and data
		delete_option( Config::$migration_status_option );
		delete_option( Config::$migration_data_option );

		// Reset CSS
		delete_option( 'tec_events_category_color_css' );

		// Add admin notice about reset
		AdminNotices::show( 'tec_category_colors_migration_reset', __( 'Category Colors migration status has been reset.', 'the-events-calendar' ) )
			->urgency( 'info' )
			->dismissible( true )
			->inline( true );

		// Redirect to remove the reset parameters
		wp_safe_redirect( remove_query_arg( [ 'tec_reset_category_colors_migration', '_wpnonce' ] ) );
		exit;
	}
}
