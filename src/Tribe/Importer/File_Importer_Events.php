<?php

/**
 * Class Tribe__Events__Importer__File_Importer_Events
 */
class Tribe__Events__Importer__File_Importer_Events extends Tribe__Events__Importer__File_Importer {

	protected $required_fields = array( 'event_name', 'event_start_date' );

	/**
	 * Searches the database for an existing event matching the one described
	 * by the specified record.
	 *
	 * @param array $record An array of values from the Events CSV file.
	 *
	 * @return int An event matching the one described by the record or `0` if no matching
	 *            events are found.
	 */
	protected function match_existing_post( array $record ) {
		$start_date = $this->get_event_start_date( $record );
		$end_date   = $this->get_event_end_date( $record );
		$all_day    = $this->get_boolean_value_by_key( $record, 'event_all_day' );

		// Base query - only the meta query will be different
		$query_args = array(
			'post_type'        => Tribe__Events__Main::POSTTYPE,
			'post_title'       => $this->get_value_by_key( $record, 'event_name' ),
			'fields'           => 'ids',
			'posts_per_page'   => 1,
			'suppress_filters' => false,
			'post_status'      => 'any',
		);

		// When trying to find matches for all day events, the comparison should only be against the date
		// component only since a) the time is irrelevant and b) the time may have been adjusted to match
		// the eod cutoff setting
		if ( Tribe__Date_Utils::is_all_day( $all_day ) ) {
			$meta_query = array(
				array(
					'key'     => '_EventStartDate',
					'value'   => $this->get_event_start_date( $record, true ),
					'compare' => 'LIKE',
				),
				array(
					'key'     => '_EventAllDay',
					'value'   => 'yes',
				),
			);
		// For regular, non-all day events, use the full date *and* time in the start date comparison
		} else {
			$meta_query = array(
				array(
					'key'   => '_EventStartDate',
					'value' => $start_date,
				),
			);
		}

		// Optionally use the end date/time for matching, where available
		if ( ! empty( $end_date ) && ! $all_day ) {
			$meta_query[] = array(
				'key'   => '_EventEndDate',
				'value' => $end_date,
			);
		}

		$query_args['meta_query'] = $meta_query;
		$query_args['tribe_remove_date_filters'] = true;

		add_filter( 'posts_search', array( $this, 'filter_query_for_title_search' ), 10, 2 );

		/**
		 * Add an option to change the $matches that are duplicates.
		 *
		 * @since 4.6.15
		 *
		 * @param array $matches Array with the duplicate matches
		 * @param array $query_args Array with the arguments used to get the posts.
		 */
		$matches = (array) apply_filters( 'tribe_events_import_event_duplicate_matches', get_posts( $query_args ), $query_args );
		remove_filter( 'posts_search', array( $this, 'filter_query_for_title_search' ), 10 );

		if ( empty( $matches ) ) {
			return 0;
		}

		return reset( $matches );
	}

	protected function update_post( $post_id, array $record ) {
		$update_authority_setting = tribe( 'events-aggregator.settings' )->default_update_authority( 'csv' );

		$event = $this->build_event_array( $post_id, $record );

		if ( 'retain' === $update_authority_setting ) {
			$this->skipped[] = $event;

			if ( $this->is_aggregator && ! empty( $this->aggregator_record ) ) {
				$this->aggregator_record->meta['activity']->add( 'event', 'skipped', $post_id );
			}

			return false;
		}

		if ( 'preserve_changes' === $update_authority_setting ) {
			$event['ID'] = $post_id;
			$event       = Tribe__Events__Aggregator__Event::preserve_changed_fields( $event );
		}

		add_filter( 'tribe_tracker_enabled', '__return_false' );

		Tribe__Events__API::updateEvent( $post_id, $event );

		if ( $this->is_aggregator && ! empty( $this->aggregator_record ) ) {
			$this->aggregator_record->meta['activity']->add( 'event', 'updated', $post_id );
		}

		remove_filter( 'tribe_tracker_enabled', '__return_false' );
	}

	protected function create_post( array $record ) {
		$event = $this->build_event_array( false, $record );
		$id    = Tribe__Events__API::createEvent( $event );

		if ( $this->is_aggregator && ! empty( $this->aggregator_record ) ) {
			Tribe__Events__Aggregator__Records::instance()->add_record_to_event( $id, $this->aggregator_record->id, 'csv' );
			$this->aggregator_record->meta['activity']->add( 'event', 'created', $id );
		}

		return $id;
	}

	private function get_event_start_date( array $record, $date_only = false ) {
		$start_date = $this->get_value_by_key( $record, 'event_start_date' );
		$start_time = $this->get_value_by_key( $record, 'event_start_time' );

		if ( ! $date_only && ! empty( $start_time ) ) {
			$start_date .= ' ' . $start_time;
		}

		$start_date = $date_only
			? date( Tribe__Date_Utils::DBDATEFORMAT, strtotime( $start_date ) )
			: date( Tribe__Date_Utils::DBDATETIMEFORMAT, strtotime( $start_date ) );

		return $start_date;
	}

	private function get_event_end_date( array $record ) {
		$start_date = $this->get_event_start_date( $record );
		$end_date   = $this->get_value_by_key( $record, 'event_end_date' );
		$end_time   = $this->get_value_by_key( $record, 'event_end_time' );
		if ( empty( $end_date ) ) {
			$end_date = $start_date;
		}
		if ( ! empty( $end_time ) ) {
			$end_date .= ' ' . $end_time;
		}
		if ( ! empty( $end_date ) ) {
			$end_date = date( 'Y-m-d H:i:s', strtotime( $end_date ) );
		}
		if ( $end_date < $start_date ) {
			$end_date = $start_date;
		}

		return $end_date;
	}

	private function build_event_array( $event_id, array $record ) {
		$start_date = strtotime( $this->get_event_start_date( $record ) );
		$end_date   = strtotime( $this->get_event_end_date( $record ) );

		if ( $this->default_post_status ) {
			$post_status_setting = $this->default_post_status;
		} else {
			$post_status_setting = tribe( 'events-aggregator.settings' )->default_post_status( 'csv' );
		}

		$event = array(
			'post_type'             => Tribe__Events__Main::POSTTYPE,
			'post_title'            => $this->get_value_by_key( $record, 'event_name' ),
			'post_status'           => $post_status_setting,
			'post_content'          => $this->get_value_by_key( $record, 'event_description' ),
			'comment_status'        => $this->get_boolean_value_by_key( $record, 'event_comment_status', 'open', 'closed' ),
			'ping_status'           => $this->get_boolean_value_by_key( $record, 'event_ping_status', 'open', 'closed' ),
			'post_excerpt'          => $this->get_post_excerpt( $event_id, $this->get_value_by_key( $record, 'event_excerpt' ) ),
			'menu_order'            => $this->get_boolean_value_by_key( $record, 'event_sticky', '-1', '0' ),
			'EventStartDate'        => date( 'Y-m-d', $start_date ),
			'EventStartHour'        => date( 'h', $start_date ),
			'EventStartMinute'      => date( 'i', $start_date ),
			'EventStartMeridian'    => date( 'a', $start_date ),
			'EventEndDate'          => date( 'Y-m-d', $end_date ),
			'EventEndHour'          => date( 'h', $end_date ),
			'EventEndMinute'        => date( 'i', $end_date ),
			'EventEndMeridian'      => date( 'a', $end_date ),
			'EventShowMapLink'      => $this->get_boolean_value_by_key( $record, 'event_show_map_link', '1', '' ),
			'EventShowMap'          => $this->get_boolean_value_by_key( $record, 'event_show_map', '1', '' ),
			'EventCost'             => $this->get_value_by_key( $record, 'event_cost' ),
			'EventAllDay'           => $this->get_boolean_value_by_key( $record, 'event_all_day', 'yes' ),
			'EventHideFromUpcoming' => $this->get_boolean_value_by_key( $record, 'event_hide', 'yes', '' ),
			'EventURL'              => $this->get_value_by_key( $record, 'event_website' ),
			'EventCurrencySymbol'   => $this->get_value_by_key( $record, 'event_currency_symbol' ),
			'EventCurrencyPosition' => $this->get_currency_position( $record ),
			'EventTimezone'         => $this->get_timezone( $this->get_value_by_key( $record, 'event_timezone' ) ),
			'feature_event'         => $this->get_boolean_value_by_key( $record, 'feature_event', '1', '' ),
		);

		if ( $organizer_id = $this->find_matching_organizer_id( $record ) ) {
			$event['organizer'] = is_array( $organizer_id ) ? $organizer_id : array( 'OrganizerID' => $organizer_id );
		}

		if ( $venue_id = $this->find_matching_venue_id( $record ) ) {
			$event['venue'] = array( 'VenueID' => $venue_id );
		}

		$cats = $this->get_value_by_key( $record, 'event_category' );

		if ( $this->is_aggregator && ! empty( $this->default_category ) ) {
			$cats = $cats ? $cats . ',' . $this->default_category : $this->default_category;
		} elseif ( $category_setting = tribe( 'events-aggregator.settings' )->default_category( 'csv' ) ) {
			$cats = $cats ? $cats . ',' . $category_setting : $category_setting;
		}

		if ( $cats ) {
			$events_cat = Tribe__Events__Main::TAXONOMY;
			$event['tax_input'][ $events_cat ] = Tribe__Terms::translate_terms_to_ids( explode( ',', $cats ), $events_cat );
		}

		if ( $tags = $this->get_value_by_key( $record, 'event_tags' ) ) {
			$event['tax_input']['post_tag'] = $tags;
		}

		// don't create the _EventHideFromUpcoming meta key/value pair if it doesn't need to be created
		if ( ! $event['EventHideFromUpcoming'] ) {
			unset( $event['EventHideFromUpcoming'] );
		}

		if ( $event['menu_order'] == '-1' ) {
			$event['EventShowInCalendar'] = 'yes';
		}

		$additional_fields = apply_filters( 'tribe_events_csv_import_event_additional_fields', array() );

		if ( ! empty ( $additional_fields ) ) {
			foreach ( $additional_fields as $key => $csv_column ) {
				$event[ $key ] = $this->get_value_by_key( $record, $key );
			}
		}

		return $event;
	}

	/**
	 * Filter allowing user to customize the separator used for organizers
	 * Defaults to comma ','
	 * @since 4.6.19
	 *
	 * @return mixed
	 */
	private function get_separator() {
		return apply_filters( 'tribe_get_event_import_organizer_separator', ',' );
	}

	/**
	 * Find organizer matches from separated string
	 * Attempts to compensate for names with separators in them - Like "Woodhouse, Chelsea S."
	 * @since 4.6.19
	 * @param $organizers
	 *
	 * @return array
	 */
	private function match_organizers( $organizers ) {
		$matches   = array();
		$separator = $this->get_separator(); // We allow this to be filtered
		$skip      = false; // For concatenation checks

		for ( $i = 0, $len = count( $organizers ); $i < $len; $i++ ) {
			if ( $skip ) {
				$skip = false;
				continue;
			}

			$potential_match = $this->find_matching_post_id( trim( $organizers[ $i ] ), Tribe__Events__Organizer::POSTTYPE, 'any' );

			// We've got a match so we add it and move on
			if ( ! empty( $potential_match ) ) {
				$matches[] = $potential_match;
				$skip      = false;
				continue;
			}

			// No match - test for separator in name by grabbing the next item and concatenating
			$test_organizer  = trim( $organizers[ $i ] ) . $separator . ' ' . trim( $organizers[ $i + 1 ] );
			$potential_match = $this->find_matching_post_id( $test_organizer, Tribe__Events__Organizer::POSTTYPE, 'any' );

			// Still no match, skip this item and move on
			if ( empty( $potential_match ) ) {
				$skip = false;
				continue;
			}

			// we got a match when combined with the next, so we flag to skip the next item
			$skip       = true;
			$matches[] = $potential_match;
		}

		$matches = array_filter( array_unique( $matches ) );

		// If we get something outlandish - like no organizers or more organizers than expected, bail
		if ( empty( $matches ) || count( $matches ) > count( $organizers ) ) {
			return array();
		}

		$organizer_ids = array( 'OrganizerID' => array() );
		foreach ( $matches as $id ) {
			$organizer_ids[ 'OrganizerID' ][] = $id;
		}

		return $organizer_ids;
	}

	/**
	 * Determine if organizer is a list of space-separated IDs
	 * @param $organizer
	 *
	 * @return array[]|bool|false|string[]
	 */
	private function organizer_is_space_separated_ids( $organizer ) {
		$pattern = '/\s+/';
		if (
			preg_match( $pattern, $organizer )
			&& is_numeric( preg_replace( $pattern, '', $organizer ) )
		) {
			return preg_split( $pattern, $organizer );
		}

		return false;
	}

	/**
	 * * Determine if organizer is a list of $separator-separated IDs
	 * @param $organizer
	 *
	 * @return array[]|bool|false|string[]
	 */
	private function maybe_organizer_is_separated_list( $organizer ) {
		$separator = $this->get_separator();

		// When we require php > 5.5 we can combine these
		$cleared_separator = trim( $separator );// clear whitespace
		$pattern           = ! empty( $cleared_separator ) ? '/' . $cleared_separator . '+/' : '/\s+/';

		// event_organizer_name is a list of $separator-separated names and/or IDs
		if ( false !== stripos( $organizer, $separator ) ) {
			return preg_split( $pattern, $organizer );
		}

		return false;
	}

	/**
	 * Handle finding the matching organizer(s) for the event
	 * @since 4.6.19
	 * @param $record - the event record from the import
	 *
	 * @return array
	 */
	private function find_matching_organizer_id( $record ) {
		$organizer = $this->get_value_by_key( $record, 'event_organizer_name' );

		// Test for space-separated IDs separately
		if ( $maybe_spaced_organizers = $this->organizer_is_space_separated_ids( $organizer ) ) {
			return $this->match_organizers( $maybe_spaced_organizers );
		}

		// Check for $separator list
		if ( $maybe_separated_organizers = $this->maybe_organizer_is_separated_list( $organizer ) ) {
			return $this->match_organizers( $maybe_separated_organizers );
		}

		// Just in case something went wrong
		// We've likely got a single item - either a number or a name (with optional spaces)
		$matching_post_ids = $this->find_matching_post_id( $organizer, Tribe__Events__Organizer::POSTTYPE, 'any' );

		if ( ! is_array( $matching_post_ids ) ) {
			$matching_post_ids = array( $matching_post_ids );
		}

		return array( 'OrganizerID' => $matching_post_ids );
	}

	private function find_matching_venue_id( $record ) {
		$name = $this->get_value_by_key( $record, 'event_venue_name' );

		return $this->find_matching_post_id( $name, Tribe__Events__Venue::POSTTYPE, 'any' );
	}

	/**
	 * Parses a timezone string candidate and returns a TEC supported timezone string.
	 *
	 * @param string $timezone_candidate
	 *
	 * @return bool|string Either the timezone string or `false` if the timezone candidate is invalid.
	 */
	private function get_timezone( $timezone_candidate ) {
		if ( Tribe__Timezones::is_utc_offset( $timezone_candidate ) ) {
			return $timezone_candidate;
		}

		return Tribe__Timezones::get_timezone( $timezone_candidate, false ) ? $timezone_candidate : false;
	}

	/**
	 * Returns the `post_excerpt` to use.
	 *
	 * Will return the existing one if present.
	 *
	 * @param int $event_id
	 * @param string $import_excerpt
	 *
	 * @return string
	 */
	private function get_post_excerpt( $event_id, $import_excerpt ) {
		if ( $event_id ) {
			$post_excerpt = get_post( $event_id )->post_excerpt;

			return empty( $post_excerpt ) && ! empty( $import_excerpt ) ? $import_excerpt : $post_excerpt;
		}

		return $import_excerpt;
	}

	/**
	 * Allows the user to specify the currency position using alias terms.
	 *
	 * @param array $record
	 *
	 * @return string Either `prefix` or `suffix`; will fall back on the first if the specified position is not
	 *                a recognized alias.
	 */
	private function get_currency_position( array $record ) {
		$currency_position = $this->get_value_by_key( $record, 'event_currency_position' );
		$after_aliases     = array( 'suffix', 'after' );

		foreach ( $after_aliases as $after_alias ) {
			if ( preg_match( '/' . $after_alias . '/i', $currency_position ) ) {
				return 'suffix';
			}
		}

		return 'prefix';
	}
}
