<?php


class Tribe__Events__Dates__Known_Range {

	/**
	 * @var static
	 */
	protected static $instance;

	/**
	 * The class singleton constructor.
	 *
	 * @return Tribe__Events__Dates__Known_Range
	 */
	public static function instance() {
		if ( empty( self::$instance ) ) {
			self::$instance = new self();
		}

		return self::$instance;
	}

	/**
	 * Determine the earliest start date and latest end date currently in the database
	 * and store those values for future use.
	 */
	public function rebuild_known_range() {
		/**
		 * Allows third-party code to alter the update process of tknown range and bail out of
		 * this implementation entirely.
		 *
		 * @since 4.9
		 *
		 * @param bool $rebuilt Whether the known range was rebuilt or not; defaults to `false`
		 *                      to let the method proceed to the update.
		 */
		$rebuilt = apply_filters( 'tribe_events_rebuild_known_range', false );
		if ( true === $rebuilt ) {
			return;
		}

		global $wpdb;
		remove_action( 'deleted_post', array( $this, 'rebuild_known_range' ) );

		$_stati = array( 'publish', 'private', 'protected' );
		$_stati = apply_filters( 'tribe_events_known_range_stati', $_stati );
		$stati  = "('" . implode( "','", $_stati ) . "')";

		$earliest = strtotime( $wpdb->get_var( $wpdb->prepare( "
				SELECT MIN(meta_value) FROM $wpdb->postmeta
				JOIN $wpdb->posts ON post_id = ID
				WHERE meta_key = '_EventStartDate'
				AND post_type = '%s'
				AND post_status IN $stati
			",
			Tribe__Events__Main::POSTTYPE ) ) );

		$latest = strtotime( $wpdb->get_var( $wpdb->prepare( "
				SELECT MAX(meta_value) FROM $wpdb->postmeta
				JOIN $wpdb->posts ON post_id = ID
				WHERE meta_key = '_EventEndDate'
				AND post_type = '%s'
				AND post_status IN $stati
			",
			Tribe__Events__Main::POSTTYPE ) ) );

		if ( $earliest ) {
			$earliest_date = date( Tribe__Date_Utils::DBDATETIMEFORMAT, $earliest );
			tribe_update_option( 'earliest_date', $earliest_date );
			// get all posts that have a start date equal to the earliest date
			$earliest_ids = $wpdb->get_col( $wpdb->prepare( "
				SELECT pm.post_id FROM $wpdb->postmeta pm
				JOIN $wpdb->posts p ON p.ID = pm.post_id
				WHERE p.post_type = %s
				AND pm.meta_key = '_EventStartDate'
				AND pm.meta_value = %s
			",
				Tribe__Events__Main::POSTTYPE,
				$earliest_date ) );
			// save those post ids as new earliest date markers
			tribe_update_option( 'earliest_date_markers', $earliest_ids );
		}
		if ( $latest ) {
			$latest_date = date( Tribe__Date_Utils::DBDATETIMEFORMAT, $latest );
			tribe_update_option( 'latest_date', $latest_date );
			// get all posts that have an end date equal to the latest date
			$latest_ids = $wpdb->get_col( $wpdb->prepare( "
				SELECT pm.post_id FROM $wpdb->postmeta pm
				JOIN $wpdb->posts p ON p.ID = pm.post_id
				WHERE p.post_type = %s
				AND pm.meta_key = '_EventEndDate'
				AND pm.meta_value = %s
			",
				Tribe__Events__Main::POSTTYPE,
				$latest_date ) );
			// save those post ids as new latest date markers
			tribe_update_option( 'latest_date_markers', $latest_ids );
		}
	}

	/**
	 * Intelligently updates our record of the earliest start date/latest event date in
	 * the system. If the existing earliest/latest values have not been superseded by the new post's
	 * start/end date then no update takes place.
	 *
	 * This is deliberately hooked into save_post, rather than save_post_tribe_events, to avoid issues
	 * where the removal/restoration of hooks within addEventMeta() etc might stop this method from
	 * actually being called (relates to a core WP bug).
	 *
	 * @param int $event_id
	 */
	public function update_known_range( $event_id ) {
		$is_earliest_date_marker = in_array( $event_id, tribe_get_option( 'earliest_date_markers', array() ) );
		$is_latest_date_marker   = in_array( $event_id, tribe_get_option( 'latest_date_markers', array() ) );
		if ( $is_earliest_date_marker || $is_latest_date_marker ) {
			$this->rebuild_known_range();

			return;
		}
		$current_min = tribe_events_earliest_date();
		$current_max = tribe_events_latest_date();

		$event_start = tribe_get_start_date( $event_id, false, Tribe__Date_Utils::DBDATETIMEFORMAT );
		$event_end   = tribe_get_end_date( $event_id, false, Tribe__Date_Utils::DBDATETIMEFORMAT );


		if ( $current_min > $event_start ) {
			$this->rebuild_known_range();
			tribe_update_option( 'earliest_date', $event_start );
		}
		if ( $current_max < $event_end ) {
			$this->rebuild_known_range();
			tribe_update_option( 'latest_date', $event_end );
		}
	}

	/**
	 * Intended to run when the save_post_tribe_events action is fired.
	 *
	 * At this point we know an event is being updated or created and, if the post is going to
	 * be visible, we can set up a further action to handle updating our record of the
	 * populated date range once the post meta containing the start and end date for the post
	 * has saved.
	 */
	public function maybe_update_known_range( $post_id ) {
		// If the event isn't going to be visible (perhaps it's been trashed) rebuild dates and bail
		if ( ! in_array( get_post_status( $post_id ), array( 'publish', 'private', 'protected' ) ) ) {
			$this->rebuild_known_range();

			return;
		}

		add_action( 'tribe_events_update_meta', array( $this, 'update_known_range' ) );
	}

	/**
	 * Fires on delete_post and decides whether or not to rebuild our record or
	 * earliest/latest event dates (which will be done when deleted_post fires,
	 * so that the deleted event is removed from the db before we recalculate).
	 *
	 * @param $post_id
	 */
	public function maybe_rebuild_known_range( $post_id ) {
		if ( Tribe__Events__Main::POSTTYPE === get_post_type( $post_id ) ) {
			add_action( 'deleted_post', array( $this, 'rebuild_known_range' ) );
		}
	}
}
