<?php

/**
 * Initialize Gutenberg Event Meta fields
 *
 * @since 4.7
 */
class Tribe__Events__Editor__Meta extends Tribe__Editor__Meta {
	/**
	 * Register the required Meta fields for good Gutenberg saving
	 *
	 * @since 4.7
	 *
	 * @return void
	 */
	public function register() {

		// Provide backwards compatibility for meta data
		$post_type = Tribe__Events__Main::POSTTYPE;
		add_filter( "rest_prepare_{$post_type}", array( $this, 'meta_backwards_compatibility' ), 10, 3 );
		add_filter( "rest_pre_insert_{$post_type}", array( $this, 'add_utc_dates' ), 10, 2 );

		register_meta( 'post', '_EventAllDay', $this->boolean() );
		register_meta( 'post', '_EventTimezone', $this->text() );
		register_meta( 'post', '_EventStartDate', $this->text() );
		register_meta( 'post', '_EventEndDate', $this->text() );
		register_meta( 'post', '_EventStartDateUTC', $this->text() );
		register_meta( 'post', '_EventEndDateUTC', $this->text() );
		register_meta( 'post', '_EventShowMap', $this->boolean() );
		register_meta( 'post', '_EventShowMapLink', $this->boolean() );
		register_meta( 'post', '_EventURL', $this->text() );
		register_meta( 'post', '_EventCost', $this->text() );
		register_meta( 'post', '_EventCostDescription', $this->text() );
		register_meta( 'post', '_EventCurrencySymbol', $this->text() );
		register_meta( 'post', '_EventCurrencyPosition', $this->text() );

		// Use sanitize_textarea_field to allow whitespaces
		register_meta(
			'post',
			'_EventDateTimeSeparator',
			array_merge(
				$this->text(),
				array(
					'sanitize_callback' => array( $this, 'sanitize_separator' ),
				)
			)
		);
		register_meta(
			'post',
			'_EventTimeRangeSeparator',
			array_merge(
				$this->text(),
				array(
					'sanitize_callback' => array( $this, 'sanitize_separator' ),
				)
			)
		);
		register_meta(
			'post',
			'_EventOrganizerID',
			array_merge(
				$this->numeric_array(),
				array(
					'description' => __( 'Event Organizers', 'the-events-calendar' ),
				)
			)
		);

		register_meta(
			'post',
			'_EventVenueID',
			array(
				'description'       => __( 'Event Organizers', 'the-events-calendar' ),
				'auth_callback'     => array( $this, 'auth_callback' ),
				'sanitize_callback' => 'absint',
				'type'              => 'integer',
				'single'            => true,
				'show_in_rest'      => true,
			)
		);

		// Organizers Meta
		register_meta( 'post', '_OrganizerEmail', $this->text() );
		register_meta( 'post', '_OrganizerPhone', $this->text() );
		register_meta( 'post', '_OrganizerWebsite', $this->text() );

		// Venue Meta
		register_meta( 'post', '_VenueAddress', $this->text() );
		register_meta( 'post', '_VenueCity', $this->text() );
		register_meta( 'post', '_VenueCountry', $this->text() );
		register_meta( 'post', '_VenueProvince', $this->text() );
		register_meta( 'post', '_VenueZip', $this->text() );
		register_meta( 'post', '_VenuePhone', $this->text() );
		register_meta( 'post', '_VenueURL', $this->text() );
		register_meta( 'post', '_VenueStateProvince', $this->text() );
		register_meta( 'post', '_VenueLat', $this->text() );
		register_meta( 'post', '_VenueLng', $this->text() );
	}

	/**
	 * Make sure we make the REST response backwards compatible.
	 *
	 * @since 4.7
	 *
	 * @param WP_REST_Response $data
	 * @param WP_Post          $post    Post object.
	 * @param WP_REST_Request  $request Request object.
	 *
	 * @return WP_REST_Response $data
	 */
	public function meta_backwards_compatibility( $data, $post, $request ) {

		$all_day = get_post_meta( $post->ID, '_EventAllDay', true );

		if ( $all_day ) {
			// transform `yes` and `no` to booleans
			$data->data['meta']['_EventAllDay'] = tribe_is_truthy( $all_day );
		}

		$timezone_string = get_post_meta( $post->ID, '_EventTimezone', true );

		if ( ! $timezone_string ) {
			$timezone_string = Tribe__Events__Timezones::wp_timezone_string();
		}

		$data->data['meta']['_EventStartDateUTC'] = Tribe__Events__Timezones::to_utc( $data->data['meta']['_EventStartDate'], $timezone_string );
		$data->data['meta']['_EventEndDateUTC']   = Tribe__Events__Timezones::to_utc( $data->data['meta']['_EventEndDate'], $timezone_string );

		return $data;
	}

	/**
	 * Adds, triggering their updates, the UTC start and end dates to the post insertion or
	 * update REST payload.
	 *
	 * @since 4.7.4
	 *
	 * @param             \stdClass     $post_data The post insertion/update payload.
	 * @param \WP_REST_Request $request The current insertion or update request object.
	 *
	 * @return \stdClass The post insertion/update payload with an added `meta_input` entry if
	 *                   the insertion/update of UTC dates is required.
	 */
	public function add_utc_dates( $post_data, WP_REST_Request $request ) {
		$json_params = $request->get_json_params();
		$meta = Tribe__Utils__Array::get( $json_params, 'meta', array() );

		// No changes to start and end? No need to update UTC dates.
		if ( ! ( isset( $meta['_EventStartDate'] ) && isset( $meta['_EventEndDate'] ) ) ) {
			return $post_data;
		}

		if ( ! isset( $post_data->meta_input ) ) {
			$post_data->meta_input = array();
		}

		$post_id          = $request->get_param( 'id' );
		$event_start_date = isset( $meta['_EventStartDate'] ) ? $meta['_EventStartDate'] : get_post_meta( $post_id, '_EventStartDate', true );
		$event_end_date   = isset( $meta['_EventEndDate'] ) ? $meta['_EventEndDate'] : get_post_meta( $post_id, '_EventEndDate', true );
		$timezone_string  = Tribe__Events__Timezones::get_event_timezone_string( $post_id );
		$timezone_string  = Tribe__Utils__Array::get( $meta, '_EventTimezone', $timezone_string );

		// If a specific timezone was not specified, default to the sitewide timezone
		if ( empty( $timezone_string ) ) {
			$timezone_string = Tribe__Events__Timezones::wp_timezone_string();
		}

		$post_data->meta_input['_EventTimezone']     = $timezone_string;
		$post_data->meta_input['_EventStartDateUTC'] = Tribe__Events__Timezones::to_utc( $event_start_date, $timezone_string );
		$post_data->meta_input['_EventEndDateUTC']   = Tribe__Events__Timezones::to_utc( $event_end_date, $timezone_string );

		return $post_data;
	}
}