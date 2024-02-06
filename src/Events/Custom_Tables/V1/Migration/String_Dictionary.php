<?php
/**
 * A centralized repository of localized, filterable, strings.
 *
 * @since   6.0.0
 *
 * @package TEC\Events\Custom_Tables\V1\Migration;
 */

namespace TEC\Events\Custom_Tables\V1\Migration;

use Tribe__Dependency as Plugins;

/**
 * Class Strings.
 *
 * @since   6.0.0
 *
 * @package TEC\Events\Custom_Tables\V1\Migration;
 */
class String_Dictionary {
	/**
	 * Whether the strings have been initialized or not.
	 *
	 * @since 6.0.0
	 *
	 * @var bool
	 */
	private $did_init = false;
	/**
	 * A map from string slugs to their filtered, localized, version.
	 *
	 * @since 6.0.0
	 *
	 * @var array
	 */
	private $map = [];
	/**
	 * A reference to the current plugin dependencies handler.
	 *
	 * @since 6.0.0
	 *
	 * @var Plugins
	 */
	private $plugins;

	/**
	 * String_Dictionary constructor.
	 *
	 * @since 6.0.0
	 *
	 * @param Plugins $plugins A reference to the current plugin dependencies handler.
	 */
	public function __construct( Plugins $plugins ) {
		$this->plugins = $plugins;
	}

	/**
	 * Initializes the strings map filtering it.
	 *
	 * @since 6.0.0
	 * @since 6.3.0 Added the `$force` parameter.
	 *
	 * @param bool $force Whether to force the initialization or not.
	 *
	 * @return void The method does not return any value and will
	 *              lazily initialize the strings map.
	 */
	private function init(bool $force = false) {
		if ( $this->did_init && ! $force ) {
			return;
		}

		$this->did_init = true;

		/**
		 * Filters the string map that will be used to provide the Migration UI
		 * messages.
		 *
		 * Note: this filter will run only once, the first time a string is requested.
		 *
		 * @since 6.0.0
		 *
		 * @param array<string,string> A map from string keys to their localized, filtered,
		 *                             version.
		 */
		$this->map = apply_filters( 'tec_events_custom_tables_v1_migration_strings', [
			'confirm_cancel_migration'                              => __(
				"To safely cancel the migration, we need to reverse the process on any data that has already been modified. Your site will remain in maintenance mode until we have safely reversed the process.",
				'the-events-calendar'
			),
			'confirm_revert_migration'                              => __(
			// @todo We need to update the UI
				"Are you sure you want to reverse the recurring event system migration process?

- You will no longer be able to use the new recurring event and Series features.
- All event edits you have made since migration will be lost.
- As with migration, you will not be able to edit events or calendar options during this process and some frontend actions will be paused.
- Reverse migration should take less time than the original migration, but there is no time estimate available.
",
				'the-events-calendar'
			),
			'migration-complete-screenshot-url'                     => plugins_url(
				'src/resources/images/migration/migration-complete-screenshot.png',
				TRIBE_EVENTS_FILE
			),
			'migration-prompt-screenshot-url'                       => plugins_url(
				'src/resources/images/migration/migration-prompt-screenshot.png',
				TRIBE_EVENTS_FILE
			),
			'migration-failed-complete-screenshot-url'              => plugins_url(
				'src/resources/images/migration/migration-prompt-screenshot.png',
				TRIBE_EVENTS_FILE
			),
			'preview-in-progress-screenshot-url'                    => plugins_url(
				'src/resources/images/migration/preview-in-progress-screenshot.png',
				TRIBE_EVENTS_FILE
			),
			'migration-in-progress-screenshot-url'                  => plugins_url(
				'src/resources/images/migration/preview-in-progress-screenshot.png',
				TRIBE_EVENTS_FILE
			),
			'cancel-in-progress-screenshot-url'                     => plugins_url(
				'src/resources/images/migration/preview-in-progress-screenshot.png',
				TRIBE_EVENTS_FILE
			),
			'revert-in-progress-screenshot-url'                     => plugins_url(
				'src/resources/images/migration/preview-in-progress-screenshot.png',
				TRIBE_EVENTS_FILE
			),
			'preview-prompt-screenshot-url'                         => plugins_url(
				'src/resources/images/migration/preview-prompt-screenshot.png',
				TRIBE_EVENTS_FILE
			),
			'cancel-complete-screenshot-url'                        => plugins_url(
				'src/resources/images/migration/preview-prompt-screenshot.png',
				TRIBE_EVENTS_FILE
			),
			'revert-complete-screenshot-url'                        => plugins_url(
				'src/resources/images/migration/preview-prompt-screenshot.png',
				TRIBE_EVENTS_FILE
			),
			'preview-screenshot-alt'                                => __(
				'screenshot of updated calendar views',
				'the-events-calendar'
			),
			'preview-prompt-get-ready'                              => __(
				'Get ready for the new recurring events!',
				'the-events-calendar'
			),
			'preview-prompt-upgrade-cta'                            => __( 'Upgrade your event data storage system.', 'the-events-calendar' ),
			'preview-prompt-features'                               => __(
				"We've completely revamped the way event information is stored on your site's database. Upgrade now to take advantage of faster calendar load times and improved performance. As with any significant site change, we recommend %screating a site backup%s before beginning the migration process.",
				'the-events-calendar'
			),
			'preview-prompt-ready'                                  => __(
				'Ready to go? The first step is a migration preview.',
				'the-events-calendar'
			),
			'preview-prompt-scan-events'                            => __(
				"We'll scan your events and let you know how long your migration will take. The preview runs in the background, so you'll be able to continue using your site.",
				'the-events-calendar'
			),
			'learn-more-button-url'                                 => __(
				'https://evnt.is/1b79',
				'the-events-calendar'
			),
			'learn-more-button'                                     => __(
				'Learn more about the migration',
				'the-events-calendar'
			),
			'start-migration-preview-button'                        => __(
				'Start migration preview',
				'the-events-calendar'
			),
			'preview-in-progress'                                   => __(
				'Migration preview in progress',
				'the-events-calendar'
			),
			'preview-scanning-events'                               => __(
				"We're scanning your existing events so you'll know what to expect from the migration process. You can keep using your site and managing events. Check back later for the next step of migration.",
				'the-events-calendar'
			),
			'preview-complete'                                      => __(
				'Preview complete',
				'the-events-calendar'
			),
			'preview-complete-paragraph'                            => __(
				'The migration preview is done and ready for your review. No changes have been made to your events, but this report shows what adjustments will be made during the migration to the new system. If you have any questions, please %1$sreach out to our support team%2$s.',
				'the-events-calendar'
			),
			'preview-estimate'                                      => __(
				'From this preview, we estimate that the full migration process will take approximately %3$d minutes. During this time, %1$syou will not be able to create, edit, or manage your events.%2$s Your calendar will still be visible on your site.',
				'the-events-calendar'
			),
			'preview-unsupported'                                   => __(
				'Your system doesn\'t allow us to run a preview, but you can still try a migration. During migration, %1$syou will not be able to create, edit, or manage your events.%2$s Your calendar will still be visible on your site.',
				'the-events-calendar'
			),
			'previewed-date-heading'                                => __(
				'Preview completed',
				'the-events-calendar'
			),
			'previewed-total-heading'                               => __(
				'Total events previewed:',
				'the-events-calendar'
			),
			're-run-preview-button'                                 => __(
				'Re-run preview',
				'the-events-calendar'
			),
			'start-migration-button'                                => __(
				'Start migration',
				'the-events-calendar'
			),
			'estimated-time-singular'                               => __(
				'(Estimated time: %1$s minute)',
				'the-events-calendar'
			),
			'estimated-time-plural'                                 => __(
				'(Estimated time: %1$s minutes)',
				'the-events-calendar'
			),
			'migration-in-progress'                                 => __(
				'Migration in progress',
				'the-events-calendar'
			),
			'migration-in-progress-paragraph'                       => __(
				'Your events are being migrated to the new system. During migration, %1$syou cannot make changes to your calendar or events.%2$s Your calendar will still be visible on the frontend.',
				'the-events-calendar'
			),
			'loading-message'                                       => __(
				'Loading...',
				'the-events-calendar'
			),
			'cancel-migration-button'                               => __(
				'Cancel Migration',
				'the-events-calendar'
			),
			'retry-preview-button'                                  => __(
				'Retry',
				'the-events-calendar'
			),
			'cancel-migration-preview-button'                       => __(
				'Cancel Migration Preview',
				'the-events-calendar'
			),
			'migration-complete'                                    => __(
				'Migration complete!',
				'the-events-calendar'
			),
			'migration-complete-paragraph'                        => __(
				'Your site is now using the upgraded event data storage system. Go ahead and %1$scheck out your events%2$s or %3$sview your calendar.%2$s',
				'the-events-calendar'
			),
			'migration-date-heading'                                => __(
				'Migration completed',
				'the-events-calendar'
			),
			'migration-total-heading'                               => __(
				'Total events migrated:',
				'the-events-calendar'
			),
			'reverse-migration-button'                              => __(
				'Reverse migration',
				'the-events-calendar'
			),
			'cancel-migration-in-progress'                          => __(
				'Cancelation in progress',
				'the-events-calendar'
			),
			'cancel-migration-in-progress-paragraph'                => __(
				'We are canceling your site\'s migration to the new system. During this time, %1$syou cannot create, edit, or manage your events%2$s. Your calendar will still be visible on your site but some frontend actions will be paused.',
				'the-events-calendar'
			),
			'cancel-migration-complete-notice'                      => __(
				'Cancelation complete.',
				'the-events-calendar'
			),
			'revert-migration-complete-notice'                      => __(
				'Reverse migration complete.',
				'the-events-calendar'
			),
			'reverse-migration-in-progress'                         => __(
				'Reverse migration in progress',
				'the-events-calendar'
			),
			'reverse-migration-in-progress-paragraph'               => __(
				'We are reversing your site\'s migration to the new system. During this time, %1$syou cannot create, edit, or manage your events%2$s. Your calendar will still be visible on your site but some frontend actions will be paused.',
				'the-events-calendar'
			),
			'migration-prompt-no-changes-to-events'                 => __(
				'Events can migrate with no changes!',
				'the-events-calendar'
			),
			'migration-prompt-strategy-tec-single-event-strategy'   => sprintf( __(
				'The following %1$s will be migrated with no adjustments:',
				'the-events-calendar'
			), tribe_get_event_label_plural_lowercase() ),
			'migration-complete-strategy-tec-single-event-strategy' => sprintf( __(
				'The following %1$s have been migrated with no adjustments:',
				'the-events-calendar'
			), tribe_get_event_label_plural_lowercase() ),
			'migration-prompt-unknown-strategy'                     => __(
				'Unknown strategy applied to this event.',
				'the-events-calendar'
			),
			'migration-prompt-learn-about-report-button'            => __(
				'Migration help',
				'the-events-calendar'
			),
			'migration-is-blocked'                                  => __(
				'We detected one or more events that cannot be properly migrated. Please review the report below for more information. These issues must be resolved before you can migrate your site. Once you have updated or removed problematic events, please re-run the migration preview.',
				'the-events-calendar'
			),
			'preview-progress-bar-events-done'                      => _x(
				'%1$s%2$d%3$s events previewed',
				'Number of events previewed',
				'the-events-calendar'
			),
			'preview-progress-bar-events-remaining'                 => _x(
				'%1$s%2$d%3$s remaining',
				'Number of events awaiting preview',
				'the-events-calendar'
			),
			'migration-progress-bar-events-done'                    => _x(
				'%1$s%2$d%3$s events migrated',
				'Number of events migrated',
				'the-events-calendar'
			),
			'migration-progress-bar-events-remaining'               => _x(
				'%1$s%2$d%3$s remaining',
				'Number of events awaiting migration',
				'the-events-calendar'
			),

			'migration-error-k-upsert-failed'                            => __(
				'The event %s generated an error: [%s]. Update the event and try again, or check out our %sTroubleshooting%s tips.',
			'the-events-calendar'
		),
			'migration-error-k-canceled'                            => __(
				'The event %s generated an error: Migration was canceled. Please try again.',
				'the-events-calendar'
			),
			'migration-error-k-exit'                                => __(
				'The event %s generated an error: The "die" or "exit" function was called during the migration process; output: %s. Please try again or check out our %sTroubleshooting%s tips.',
				'the-events-calendar'
			),
			'migration-error-k-exception'                           => __(
				'The event %s generated an error: [%s]. Please try again or check out our %sTroubleshooting%s tips.',
				'the-events-calendar'
			),
			'migration-error-k-tickets-exception'                   => __(
				'The event %s cannot be migrated because we do not yet support tickets on recurring events. Remove the tickets or wait to migrate until a path is available (%sRead more%s).',
				'the-events-calendar'
			),
			'migration-error-k-enqueue-failed'                      => __(
				'The event %s generated an error: Cannot enqueue action to migrate Event with post ID %s. Your site may be under high load and unable to process the necessary requests. Try again later.',
				'the-events-calendar'
			),
			'migration-error-k-check-phase-enqueue-failed'          => __(
				'The event %s generated an error: Cannot enqueue action to check migration status. Your site may be under high load and unable to process the necessary requests. Try again when site activity is low.',
				'the-events-calendar'
			),
			'migration-error-k-unknown-shutdown'                    => __(
				'The event %s generated an unknown error. Please try again or check out our %sTroubleshooting%s tips.',
				'the-events-calendar'
			),
			'migration-view-report-button'                          => __(
				'View the migration report',
				'the-events-calendar'
			),
			'migration-canceled'                                    => __(
				'Migration canceled',
				'the-events-calendar'
			),
			'migration-reversed'                                    => __(
				'Migration reversed',
				'the-events-calendar'
			),
			'migration-okay-button'                                 => __(
				'Okay',
				'the-events-calendar'
			),
			'migration-failure-complete'                            => __(
				'Migration failure',
				'the-events-calendar'
			),
			'migration-failure-complete-paragraph'                  => __(
				'Your site could not be safely migrated to the new events system.',
				'the-events-calendar'
			),
			'migration-failure-complete-alert'                      => __(
				'We detected an event that cannot be properly migrated. Please review the report below for more information. This issue must be resolved before you can migrate your site. Once you have updated or removed the problematic event, re-run the migration preview to make sure your site is ready to go.',
				'the-events-calendar'
			),
			'migration-failure-complete-date-heading'               => __(
				'Migration attempted',
				'the-events-calendar'
			),
			'migration-failed'                                      => __(
				'Migration failed',
				'the-events-calendar'
			),
			'migration-failure-complete-view-report-button'         => __(
				'View the error report',
				'the-events-calendar'
			),
			'migration-download-report-button'                      => __(
				'Download report',
				'the-events-calendar'
			),
			'migration-prompt-plugin-state-addendum'                => $this->get_plugin_state_migration_addendum(),
		] );
	}

	/**
	 * Gets the migration prompt trailing message based on plugin activation state.
	 *
	 * Note this code will sense around for both .org and premium plugins: it's by
	 * design and meant to keep the logic lean.
	 *
	 * @since 6.0.0
	 *
	 * @return string
	 */
	public function get_plugin_state_migration_addendum() {
		// Free plugins.
		$et_active = $this->plugins->is_plugin_active( 'Tribe__Tickets__Main' );
		$ea_active = tribe( 'events-aggregator.main' )->has_license_key();
		// Premium plugins.
		$ce_active = $this->plugins->is_plugin_active( 'Tribe__Events__Community__Main' );
		$eb_active = $this->plugins->is_plugin_active( 'Tribe__Events__Tickets__Eventbrite__Main' );
		$text      = '';

		if ( $et_active && $ce_active && ( $ea_active || $eb_active ) ) {
			$text = __( 'Ticket sales, RSVPs, event submissions, and automatic imports will be paused until migration is complete.', 'the-events-calendar' );
		}

		if ( $et_active && ( $ea_active || $eb_active ) ) {
			$text = __( 'Ticket sales, RSVPs, and automatic imports will be paused until migration is complete.', 'the-events-calendar' );
		}

		if ( $ce_active && ( $ea_active || $eb_active ) ) {
			$text = __( 'Event submissions and automatic imports will be paused until migration is complete.', 'the-events-calendar' );
		}

		if ( $et_active && $ce_active ) {
			$text = __( 'Ticket sales, RSVPs, and event submissions will be paused until migration is complete.', 'the-events-calendar' );
		}

		if ( $et_active ) {
			$text = __( 'Ticket sales and RSVPs will be paused until migration is complete.', 'the-events-calendar' );
		}

		if ( $ce_active ) {
			$text = __( 'Event submissions will be paused until migration is complete.', 'the-events-calendar' );
		}

		if ( $ea_active || $eb_active ) {
			$text = __( 'Automatic imports will be paused until migration is complete.', 'the-events-calendar' );
		}

		/**
		 * The messaging around the active plugins and the effects on each plugin during a migration.
		 *
		 * @since 6.0.0
		 *
		 * @param string $text The messaging text.
		 */
		return apply_filters( 'tec_events_custom_tables_v1_migration_get_plugin_state_migration_addendum', $text );
	}

	/**
	 * Returns the filtered, localized string for a slug.
	 *
	 * @since 6.0.0
	 *
	 * @param string $key The key to return the string for.
	 *
	 * @return string The filtered localized string for the key,
	 *                or the key itself if no string for the key
	 *                can be found.
	 */
	public function get( $key ) {
		$this->init();

		if ( isset( $this->map[ $key ] ) ) {
			return $this->map[ $key ];
		}

		// If we cannot find a string for the slug, then return the key.
		return $key;
	}

	/**
	 * Forces the re-initialization of the strings map.
	 *
	 * @since 6.3.0
	 *
	 * @return String_Dictionary For chaining.
	 */
	public function reinit(): String_Dictionary {
		$this->init( true );

		return $this;
	}
}
