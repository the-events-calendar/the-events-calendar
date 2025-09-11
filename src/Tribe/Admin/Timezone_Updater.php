<?php
/**
 * Tool for adding timezone data to events.
 *
 * The application for this is in transitioning any event data created in 3.11.x or
 * earlier that hasn't since been updated, so that it becomes "time zone ready".
 */

class Tribe__Events__Admin__Timezone_Updater {
	/**
	 * A count of events in need of updating - used to determine the percentage
	 * of the task that has been completed.
	 *
	 * @var int
	 */
	protected $initial_count = 0;

	/**
	 * The name of the option used for tracking if a timezone update is needed.
	 *
	 * @since 6.15.2
	 *
	 * @var string
	 */
	protected const TIMED_OPTION_KEY = 'events_timezone_update_needed';

	/**
	 * Initializes the update process.
	 *
	 * Determines if events are still in need of an update and triggers an update of an
	 * initial batch of events if so.
	 *
	 * Once these are processed, notices are set to communicate the state of the update
	 * back to the user (which also serves as a vehicle for continuing the update via
	 * an ajax loop).
	 *
	 * @since 4.0 0
	 * @since 6.15.2 Make use of a timed option to determine if an update is needed.
	 *
	 * @return void
	 */
	public function init_update() {
		if ( $this->update_needed() ) {
			/**
			 * Provides an opportunity to change the maximum number of events that will be
			 * updated with timezone data in a single batch.
			 *
			 * @param int $batch_size Number of events to be processed in a single batch.
			 */
			$batch_size          = (int) apply_filters( 'tribe_events_timezone_updater_batch_size', 50 );
			$this->initial_count = $this->count_ids();

			if ( $this->initial_count <= 0 ) {
				tec_timed_option()->set( self::TIMED_OPTION_KEY, false );
			}

			$this->process( $batch_size );
		}

		$this->notice_setup();
	}

	/**
	 * Set up an admin-notice-based progress report along with supporting assets to facilitate
	 * an ajax loop for further processing where needed.
	 */
	protected function notice_setup() {
		add_action( 'admin_notices', [ $this, 'notice_display' ] );
		add_action( 'admin_enqueue_scripts', [ $this, 'notice_assets' ] );
	}

	/**
	 * Renders the admin notice.
	 *
	 * This effectively just wraps notice_inner(), which is independently called to build
	 * ajax responses.
	 */
	public function notice_display() {
		$update = $this->notice_inner();
		echo '<div class="tribe-events-timezone-update-msg updated updating">' . wp_kses_post( $update ) . '</div>';
	}

	/**
	 * Provides a progress report relating to the status of the time zone data update process.
	 *
	 * @return string
	 */
	public function notice_inner() {
		$remaining = $this->count_ids();
		$spinner   = '<img src="' . get_admin_url( null, '/images/spinner.gif' ) . '">';

		$progress = ( 0 < $remaining )
			? $this->calculate_progress( $remaining )
			: 100;

		$update = $remaining
			? __( 'Please wait while time zone data is added to your events.', 'the-events-calendar' )
			: __( 'Update complete: time zone data has been added to all events in the database.', 'the-events-calendar' );

		$update = "<p>$update</p>";

		if ( 100 === $progress ) {
			$spinner = '';
		}

		if ( $progress >= 0 ) {
			$percent = sprintf( __( '%d%% complete', 'the-events-calendar' ), $progress );
			$update .= '<div class="tribe-update-bar"><div class="progress" title="' . $percent . '"><div class="bar" style="width: ' . $progress . '%"></div></div>' . $spinner . '</div>';
		}

		return $update;
	}

	/**
	 * Sets up the JavaScript needed to facilitate the ajax loop on the frontend.
	 */
	public function notice_assets() {
		$plugin = Tribe__Events__Main::instance();
		$script = trailingslashit( $plugin->plugin_url ) . 'build/js/events-admin-timezone-updater.js';
		$handle = 'tribe-events-ajax-timezone-update';

		wp_enqueue_script( $handle, $script, [ 'jquery' ], false, true );
		wp_localize_script( $handle, 'tribe_timezone_update', [
			'continue'    => $this->update_needed(),
			'failure_msg' => __( 'A problem stopped the time zone update process from completing. Please refresh and try again.', 'the-events-calendar' ),
			'check'       => wp_create_nonce( 'timezone-settings' ),
		] );
	}

	/**
	 * Returns an integer representing the degree to which the update task has progressed
	 * as a percentage of events in need of updating.
	 *
	 * @param int $remaining The number of events remaining to be processed.
	 *
	 * @return int
	 */
	protected function calculate_progress( $remaining ) {
		if ( $this->initial_count ) {
			$percentage = ( $this->initial_count - $remaining ) / $this->initial_count;

			return (int) ( $percentage * 100 );
		}

		return 0;
	}

	/**
	 * Updates the next batch of non-timezone ready events.
	 *
	 * @param int $batch_size The batch size. Defaults to -1 meaning "update all".
	 */
	public function process( $batch_size = -1 ) {
		$site_timezone = Tribe__Timezones::wp_timezone_string();

		foreach ( $this->get_ids( $batch_size ) as $event_id ) {
			$local_start_time = tribe_get_start_date( $event_id, true, Tribe__Date_Utils::DBDATETIMEFORMAT );
			$utc_start_time   = Tribe__Timezones::to_utc( $local_start_time, $site_timezone );

			$local_end_time = tribe_get_end_date( $event_id, true, Tribe__Date_Utils::DBDATETIMEFORMAT );
			$utc_end_time   = Tribe__Timezones::to_utc( $local_end_time, $site_timezone );

			// The abbreviation needs to be calculated per event as it can vary according to the actual date.
			$site_timezone_abbr = Tribe__Timezones::wp_timezone_abbr( $local_start_time );

			update_post_meta( $event_id, '_EventTimezone', $site_timezone );
			update_post_meta( $event_id, '_EventTimezoneAbbr', $site_timezone_abbr );
			update_post_meta( $event_id, '_EventStartDateUTC', $utc_start_time );
			update_post_meta( $event_id, '_EventEndDateUTC', $utc_end_time );
		}
	}

	/**
	 * Return an array of event IDs for those events that still do not have
	 * time zone data.
	 *
	 * @param int $limit The number of events to return. Defaults to -1 meaning "all".
	 *
	 * @return array
	 */
	public function get_ids( $limit = -1 ) {
		return $this->find( $limit );
	}

	/**
	 * Get the number of events that still require time zone data.
	 *
	 * @return int
	 */
	public function count_ids() {
		return $this->find( -1, true );
	}

	/**
	 * Indicates if there are still events that need to be updated with time zone data.
	 *
	 * @return bool
	 */
	public function update_needed() {
		$update_needed = tec_timed_option()->get( self::TIMED_OPTION_KEY );

		if ( null === $update_needed ) {
			$update_needed = (bool) $this->find( 1, true );
			tec_timed_option()->set( self::TIMED_OPTION_KEY, $update_needed );
		}

		return $update_needed;
	}

	/**
	 * Utility function that can return either an array of IDs for all (or the specified
	 * number) of events without time zone data, or alternatively can return a count of
	 * those events.
	 *
	 * @param int        $limit The number of events to return. Defaults to -1 meaning "all".
	 * @param bool|false $count Whether to return an array of IDs (default) or a count of the events.
	 *
	 * @return array|int
	 */
	protected function find( $limit = -1, $count = false ) {
		global $wpdb;

		// Form the limit clause if needed
		$limit = ( (int) $limit > 0 )
			? 'LIMIT ' . absint( $limit )
			: '';

		// Are we making a count or obtaining the actual IDs?
		$requested_data = $count
			? 'COUNT( DISTINCT( ID ) )'
			: 'DISTINCT( ID )';

		$query = "
			-- Look for events not returned by the inner query
			SELECT $requested_data
			FROM   $wpdb->posts
			WHERE  $wpdb->posts.post_type = %s
			AND    $wpdb->posts.post_status <> 'auto-draft'
			AND    ID NOT IN (
			           -- Find those posts that already have timezone meta data
			           SELECT DISTINCT ( post_id )
			           FROM   $wpdb->postmeta
			           WHERE  meta_key = '_EventTimezone'
			       )
			$limit;
		";

		$prepared_query = $wpdb->prepare( $query, Tribe__Events__Main::POSTTYPE );

		return $count
			? (int) $wpdb->get_var( $prepared_query )
			: (array) $wpdb->get_col( $prepared_query );
	}
}
