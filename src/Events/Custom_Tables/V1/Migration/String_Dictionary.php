<?php
/**
 * A centralized repository of localized, filterable, strings.
 *
 * @since   TBD
 *
 * @package TEC\Events\Custom_Tables\V1\Migration;
 */

namespace TEC\Events\Custom_Tables\V1\Migration;

use Tribe__Dependency as Plugins;

/**
 * Class Strings.
 *
 * @since   TBD
 *
 * @package TEC\Events\Custom_Tables\V1\Migration;
 */
class String_Dictionary {
	/**
	 * Whether the strings have been initialized or not.
	 *
	 * @since TBD
	 *
	 * @var bool
	 */
	private $did_init = false;
	/**
	 * A map from string slugs to their filtered, localized, version.
	 *
	 * @since TBD
	 *
	 * @var array
	 */
	private $map = [];
	/**
	 * A reference to the current plugin dependencies handler.
	 *
	 * @since TBD
	 *
	 * @var Plugins
	 */
	private $plugins;

	/**
	 * String_Dictionary constructor.
	 *
	 * @since TBD
	 *
	 * @param Plugins $plugins A reference to the current plugin dependencies handler.
	 */
	public function __construct( Plugins $plugins ) {
		$this->plugins = $plugins;
	}

	/**
	 * Initializes the strings map filtering it.
	 *
	 * @since TBD
	 *
	 * @return void The method does not return any value and will
	 *              lazily initialize the strings map.
	 */
	private function init() {
		if ( $this->did_init ) {
			return;
		}

		$this->did_init = true;

		/**
		 * Filters the string map that will be used to provide the Migration UI
		 * messages.
		 *
		 * Note: this filter will run only once, the first time a string is requested.
		 *
		 * @since TBD
		 *
		 * @param array<string,string> A map from string keys to their localized, filtered,
		 *                             version.
		 */
		$this->map = apply_filters( 'tec_events_custom_tables_v1_migration_strings', [
			'completed-screenshot-url'                            => plugins_url(
			// @todo correct screenshot here
				'src/resources/images/upgrade-views-screenshot.png',
				TRIBE_EVENTS_FILE
			),
			'completed-site-upgraded'                             => __(
				'Your site is now using the upgraded event data storage system.',
				'the-events-calendar'
			),
			'preview-prompt-get-ready'                            => __(
				'Get ready for the new recurring events!',
				'the-events-calendar'
			),
			'preview-prompt-upgrade-cta'                          => __( 'Upgrade your recurring events.', 'the-events-calendar' ),
			'preview-prompt-features'                             => __(
				'Faster event editing. Smarter save options. More flexibility. Events Calendar 6.0  ' .
				'is full of features to make managing recurring and connected events better than ever. ' .
				'Before you get started, we need to migrate your existing events into the new system.',
				'the-events-calendar'
			),
			'preview-prompt-ready'                                => __(
				'Ready to go? The first step is a migration preview.',
				'the-events-calendar'
			),
			'preview-prompt-scan-events'                          => __(
				'We\'ll scan all existing events and let you know what to expect from the migration process. You\'ll also get an idea of how long your migration will take. The preview runs in the background, so you’ll be able to continue using your site.',
				'the-events-calendar'
			),
			'learn-more-button-url'                               => __(
				'https://evnt.is/recurrence-2-0',
				'the-events-calendar'
			),
			'learn-more-button'                                   => __(
				'Learn more about the migration.',
				'the-events-calendar'
			),
			'start-migration-preview-button'                      => __(
				'Start migration preview',
				'the-events-calendar'
			),
			'updated-views-screenshot-alt'                        => __(
				'screenshot of updated calendar views',
				'the-events-calendar'
			),
			'preview-in-progress'                                 => __(
				'Migration preview in progress',
				'the-events-calendar'
			),
			'preview-scanning-events'                             => __(
				'We\'re scanning your existing events so you’ll know what to expect from the migration process. You can keep using your site and managing events. Check back later for a full preview report and the next steps for migration.',
				'the-events-calendar'
			),
			'preview-complete'                                    => __(
				'Preview complete',
				'the-events-calendar'
			),
			'preview-complete-paragraph'                          => __(
				'The migration preview is done and ready for your review. No changes have been made to your events, but this report shows what adjustments will be made during the migration to the new system. If you have any questions, please %1$sreach out to our support team%2$s.',
				'the-events-calendar'
			),
			'preview-estimate'                                    => __(
				'From this preview, we estimate that the full migration process will take approximately %3$s hour(s). During this time, %1$syou will not be able to create, edit, or manage your events.%2$s Your calendar will still be visible on your site.',
				'the-events-calendar'
			),
			'previewed-date-heading'                              => __(
				'Preview completed',
				'the-events-calendar'
			),
			'previewed-total-heading'                             => __(
				'Total events previewed:',
				'the-events-calendar'
			),
			're-run-preview-button'                               => __(
				'Re-run preview',
				'the-events-calendar'
			),
			'start-migration-button'                              => __(
				'Start migration',
				'the-events-calendar'
			),
			'estimated-time-singular'                             => __(
				'(Estimated time: %1$s hour)',
				'the-events-calendar'
			),
			'estimated-time-plural'                               => __(
				'(Estimated time: %1$s hours)',
				'the-events-calendar'
			),
			'migration-in-progress'                               => __(
				'Migration in progress',
				'the-events-calendar'
			),
			'migration-in-progress-paragraph'                     => __(
				'Your events are being migrated to the new system. During migration, %1$syou cannot make changes to your calendar or events.%2$s Your calendar will still be visible on the frontend.',
				'the-events-calendar'
			),
			'loading-message'                                     => __(
				'Loading...',
				'the-events-calendar'
			),
			'cancel-migration-button'                             => __(
				'Cancel Migration',
				'the-events-calendar'
			),
			'migration-complete'                                  => __(
				'Migration complete!',
				'the-events-calendar'
			),
			'migration-complete-paragraph'                        => __(
				'Go ahead and %1$scheck out your events%2$s, %3$sview your calendar%2$s, or %4$sread more%2$s.',
				'the-events-calendar'
			),
			'migration-date-heading'                              => __(
				'Migration completed',
				'the-events-calendar'
			),
			'migration-total-heading'                             => __(
				'Total events migrated:',
				'the-events-calendar'
			),
			'reverse-migration-button'                            => __(
				'Reverse migration',
				'the-events-calendar'
			),
			'reverse-migration-in-progress'                       => __(
				'Reverse migration in progress',
				'the-events-calendar'
			),
			'reverse-migration-in-progress-paragraph'             => __(
				'We are reversing your site’s migration to the new system. During this time, %1$syou cannot create, edit, or manage your events%2$s. Your calendar will still be visible on your site but some frontend actions will be paused.',
				'the-events-calendar'
			),
			'migration-prompt-changes-to-events'                  => __(
				'Changes to events!',
				'the-events-calendar'
			),
			'migration-prompt-events-modified'                    => __(
				'The following events will be modified during the migration process:',
				'the-events-calendar'
			),
			'migration-prompt-no-changes-to-events'               => __(
				'Events can migrate with no changes!',
				'the-events-calendar'
			),
			'migration-prompt-strategy-split'                     => __(
				'This event will be %1$ssplit into %2$s recurring events%3$s with identical content.',
				'the-events-calendar'
			),
			'migration-prompt-strategy-tec-single-event-strategy' => __(
				'This single event will be updated with identical content.',
				'the-events-calendar'
			),
			'migration-prompt-strategy-split-new-series'          => __(
				'The events will be part of a new %1$s.',
				'the-events-calendar'
			),
			'migration-prompt-strategy-modified-exclusions'       => __(
				'%1$sOne or more exclusion rules will be modified%2$s, but no occurrences will be added or removed.',
				'the-events-calendar'
			),
			'migration-prompt-strategy-modified-rules'            => __(
				'%1$sOne or more recurrence rules will be modified%2$s, but no occurrences will be added or removed.',
				'the-events-calendar'
			),
			'migration-prompt-unknown-strategy'                   => __(
				'Unknown strategy applied to this event.',
				'the-events-calendar'
			),
			'migration-prompt-learn-about-report-button'          => __(
				'Learn more about your migration preview report',
				'the-events-calendar'
			),
			'migration-is-blocked'          => __(
				'Migration is blocked due to errors found during preview.',
				'the-events-calendar'
			),
			'preview-progress-bar-events-done'                    => _x(
				'%1$s%2$d%3$s events previewed',
				'Number of events previewed',
				'the-events-calendar'
			),
			'preview-progress-bar-events-remaining'               => _x(
				'%1$s%2$d%3$s remaining',
				'Number of events awaiting preview',
				'the-events-calendar'
			),
			'migration-progress-bar-events-done'                  => _x(
				'%1$s%2$d%3$s events migrated',
				'Number of events migrated',
				'the-events-calendar'
			),
			'migration-progress-bar-events-remaining'             => _x(
				'%1$s%2$d%3$s remaining',
				'Number of events awaiting migration',
				'the-events-calendar'
			),
			'migration-prompt-plugin-state-addendum'              => $this->get_plugin_state_migration_addendum(),
		] );
	}

	/**
	 * Gets the migration prompt trailing message based on plugin activation state.
	 *
	 * Note this code will sense around for both .org and premium plugins: it's by
	 * design and meant to keep the logic lean.
	 *
	 * @since TBD
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
		 * @since TBD
		 *
		 * @param string $text The messaging text.
		 */
		return apply_filters( 'tec_events_custom_tables_v1_migration_get_plugin_state_migration_addendum', $text );
	}

	/**
	 * Returns the filtered, localized string for a slug.
	 *
	 * @since TBD
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
}