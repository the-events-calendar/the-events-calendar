<?php

/**
 * Class Tribe__Events__Importer__File_Importer_Events
 */
class Tribe__Events__Importer__File_Importer_Events extends Tribe__Events__Importer__File_Importer {

	protected $required_fields = array( 'event_name', 'event_start_date' );

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

		add_filter( 'posts_search', array( $this, 'filter_query_for_title_search' ), 10, 2 );
		$matches = get_posts( $query_args );
		remove_filter( 'posts_search', array( $this, 'filter_query_for_title_search' ), 10 );

		if ( empty( $matches ) ) {
			return 0;
		}

		return reset( $matches );
	}

	protected function update_post( $post_id, array $record ) {
		$event = $this->build_event_array( $post_id, $record );
		Tribe__Events__API::updateEvent( $post_id, $event );
	}


	protected function create_post( array $record ) {
		$event = $this->build_event_array( false, $record );
		$id    = Tribe__Events__API::createEvent( $event );

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

	private function get_boolean_value_by_key( $record, $key, $return_true_value = '1', $accepted_true_values = array( 'yes', 'true', '1' ) ) {
		$value = strtolower( $this->get_value_by_key( $record, $key ) );
		if ( in_array( $value, $accepted_true_values ) ) {
			$value = $return_true_value;
		}

		return $value;
	}

	private function build_event_array( $event_id, array $record ) {
		$start_date = strtotime( $this->get_event_start_date( $record ) );
		$end_date   = strtotime( $this->get_event_end_date( $record ) );

		$featured_image_content = $this->get_value_by_key( $record, 'featured_image' );
		$featured_image         = $event_id ? '' === get_post_meta( $event_id, '_wp_attached_file', true ) : $this->featured_image_uploader( $featured_image_content )->upload_and_get_attachment();
		$event                  = array(
			'post_type'             => Tribe__Events__Main::POSTTYPE,
			'post_title'            => $this->get_value_by_key( $record, 'event_name' ),
			'post_status'           => Tribe__Events__Importer__Options::get_default_post_status( 'csv' ),
			'post_content'          => $this->get_value_by_key( $record, 'event_description' ),
			'EventStartDate'        => date( 'Y-m-d', $start_date ),
			'EventStartHour'        => date( 'h', $start_date ),
			'EventStartMinute'      => date( 'i', $start_date ),
			'EventStartMeridian'    => date( 'a', $start_date ),
			'EventEndDate'          => date( 'Y-m-d', $end_date ),
			'EventEndHour'          => date( 'h', $end_date ),
			'EventEndMinute'        => date( 'i', $end_date ),
			'EventEndMeridian'      => date( 'a', $end_date ),
			'EventShowMapLink'      => $this->get_boolean_value_by_key( $record, 'event_show_map_link' ),
			'EventShowMap'          => $this->get_boolean_value_by_key( $record, 'event_show_map' ),
			'EventCost'             => $this->get_value_by_key( $record, 'event_cost' ),
			'EventAllDay'           => $this->get_boolean_value_by_key( $record, 'event_all_day', 'yes' ),
			'EventHideFromUpcoming' => $this->get_value_by_key( $record, 'event_hide' ),
			'EventURL'              => $this->get_value_by_key( $record, 'event_website' ),
			'EventCurrencySymbol'   => $this->get_value_by_key( $record, 'event_currency_symbol' ),
			'EventCurrencyPosition' => $this->get_value_by_key( $record, 'event_currency_position' ),
			'FeaturedImage'         => $featured_image,
		);

		if ( $organizer_id = $this->find_matching_organizer_id( $record ) ) {
			$event['Organizer'] = array( 'OrganizerID' => $organizer_id );
		}

		if ( $venue_id = $this->find_matching_venue_id( $record ) ) {
			$event['Venue'] = array( 'VenueID' => $venue_id );
		}

		if ( $cats = $this->get_value_by_key( $record, 'event_category' ) ) {
			$event['tax_input'][ Tribe__Events__Main::TAXONOMY ] = $this->translate_terms_to_ids( explode( ',', $cats ) );
		}

		if ( $tags = $this->get_value_by_key( $record, 'event_tags' ) ) {
			$event['tax_input']['post_tag'] = $tags;
		}

		// don't create the _EventHideFromUpcoming meta key/value pair if it doesn't need to be created
		if ( ! $event['EventHideFromUpcoming'] ) {
			unset( $event['EventHideFromUpcoming'] );
		}

		$additional_fields = apply_filters( 'tribe_events_csv_import_event_additional_fields', array() );
		if ( ! empty ( $additional_fields ) ) {
			foreach ( $additional_fields as $key => $csv_column ) {
				$event[ $key ] = $this->get_value_by_key( $record, $key );
			}
		}

		return $event;

	}

	private function find_matching_organizer_id( $record ) {
		$name = $this->get_value_by_key( $record, 'event_organizer_name' );

		return $this->find_matching_post_id( $name, Tribe__Events__Main::ORGANIZER_POST_TYPE );
	}

	private function find_matching_venue_id( $record ) {
		$name = $this->get_value_by_key( $record, 'event_venue_name' );

		return $this->find_matching_post_id( $name, Tribe__Events__Main::VENUE_POST_TYPE );
	}

	/**
	 * When passing terms to wp_insert_post(), we're required to have IDs
	 * for hierarchical taxonomies, not strings
	 *
	 * @param array $terms
	 *
	 * @return int[]
	 */
	private function translate_terms_to_ids( array $terms ) {
		$term_ids = array();
		// duplicating some code from wp_set_object_terms()
		foreach ( $terms as $term ) {
			if ( ! strlen( trim( $term ) ) ) {
				continue;
			}

			if ( ! $term_info = term_exists( $term, Tribe__Events__Main::TAXONOMY ) ) {
				// Skip if a non-existent term ID is passed.
				if ( is_int( $term ) ) {
					continue;
				}
				$term_info = wp_insert_term( $term, Tribe__Events__Main::TAXONOMY );
			}
			if ( is_wp_error( $term_info ) ) {
				continue;
			}
			$term_ids[] = $term_info['term_id'];
		}

		return $term_ids;
	}

}
