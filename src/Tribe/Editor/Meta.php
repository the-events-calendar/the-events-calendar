<?php

use Tribe__Date_Utils as Date;

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
		add_filter( "rest_prepare_{$post_type}", [ $this, 'meta_backwards_compatibility' ], 10, 3 );
		add_filter( "rest_after_insert_{$post_type}", [ $this, 'add_utc_dates' ], 10, 2 );
		add_filter( "rest_after_insert_{$post_type}", [ $this, 'update_cost' ], 10, 2 );
		add_filter( 'delete_post_metadata', [ $this, 'filter_allow_meta_delete_non_existent_key' ], 15, 5 );

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
		register_meta( 'post', '_EventCurrencyCode', $this->text() );
		register_meta( 'post', '_EventCurrencyPosition', $this->text() );

		// Use sanitize_textarea_field to allow whitespaces
		register_meta(
			'post',
			'_EventDateTimeSeparator',
			array_merge(
				$this->text(),
				[
					'sanitize_callback' => [ $this, 'sanitize_separator' ],
				]
			)
		);
		register_meta(
			'post',
			'_EventTimeRangeSeparator',
			array_merge(
				$this->text(),
				[
					'sanitize_callback' => [ $this, 'sanitize_separator' ],
				]
			)
		);
		register_meta(
			'post',
			'_EventOrganizerID',
			array_merge(
				$this->numeric_array(),
				[
					'description' => __( 'Event Organizers', 'the-events-calendar' ),
				]
			)
		);

		register_meta(
			'post',
			'_EventVenueID',
			array_merge(
				$this->numeric_array(),
				[
					'description' => __( 'Event Venue', 'the-events-calendar' ),
				]
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
		register_meta( 'post', '_VenueState', $this->text() );
		register_meta( 'post', '_VenueZip', $this->text() );
		register_meta( 'post', '_VenuePhone', $this->text() );
		register_meta( 'post', '_VenueURL', $this->text() );
		register_meta( 'post', '_VenueStateProvince', $this->text() );
		register_meta( 'post', '_VenueLat', $this->text() );
		register_meta( 'post', '_VenueLng', $this->text() );
		register_meta( 'post', '_VenueShowMap', $this->boolean() );
		register_meta( 'post', '_VenueShowMapLink', $this->boolean() );
	}

	/**
	 * Short-circuits deleting metadata items that don't exist, for compatibility purposes we need to make sure
	 * WordPress doesn't throw an error when the meta is not present.
	 *
	 * @since 5.5.0
	 * @since 4.6.0 Apply to all Rest Endpoints not only Events.
	 *
	 * @param null|bool $delete     Whether to allow metadata deletion of the given type.
	 * @param int       $object_id  ID of the object metadata is for.
	 * @param string    $meta_key   Metadata key.
	 * @param mixed     $meta_value Metadata value. Must be serializable if non-scalar.
	 * @param bool      $delete_all Whether to delete the matching metadata entries
	 *                              for all objects, ignoring the specified $object_id.
	 *                              Default false.
	 *
	 * @return bool
	 */
	public function filter_allow_meta_delete_non_existent_key( $delete, $object_id, $meta_key, $meta_value, $delete_all ) {
		if ( ! empty( $meta_value ) ) {
			return $delete;
		}

		$meta_keys_to_allow = [
			'_EventOrganizerID' => true,
			'_EventVenueID' => true,
		];

		if ( ! isset( $meta_keys_to_allow[ $meta_key ] ) ) {
			return $delete;
		}

		if ( ! function_exists( 'wp_is_json_request' ) || ! wp_is_json_request() ) {
			return $delete;
		}

		global $wp;

		$current_url = home_url( $wp->request );
		$allowed_rest_url = rest_url( 'wp/v2' );

		// Only this overwrite on the Tribe Events Endpoint.
		if ( false === strpos( $current_url, $allowed_rest_url ) ) {
			return $delete;
		}

		$current_value = array_filter( get_post_meta( $object_id, $meta_key ) );

		// Let the WP method run it's course, if we have a value.
		if ( ! empty( $current_value ) ) {
			return $delete;
		}

		// If we got to this point we allow the deletion without caring about the value.
		return true;
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

		return $data;
	}

	/**
	 * Make sure we allow other plugins and customizations to filter the cost field.
	 *
	 * @since 5.7.1
	 *
	 * @param \stdClass        $post_data The post insertion/update payload.
	 * @param \WP_REST_Request $request The current insertion or update request object.
	 *
	 * @return \stdClass The post insertion/update payload.
	 */
	public function update_cost( $post_data, $request ) {
		$post_id = $request->get_param( 'id' );

		// Fetch cost data from the request (if set).
		$json = $request->get_json_params();

		$meta = Tribe__Utils__Array::get( $json, 'meta', [] );

		// If the cost is set in the submitted data, set THAT as the default else set an appropriate default for the cost.
		$cost = isset( $meta['_EventCost'] ) ? [ $meta['_EventCost'] ] : (array) tribe_get_cost( $post_id );

		// Update the cost for the event.
		\Tribe__Events__API::update_event_cost( $post_id, $cost );

		return $post_data;
	}

	/**
	 * Adds, triggering their updates, the UTC start and end dates to the post insertion or
	 * update REST payload.
	 *
	 * @since 4.9
	 *
	 * @param             \stdClass     $post_data The post insertion/update payload.
	 * @param \WP_REST_Request $request The current insertion or update request object.
	 *
	 * @return \stdClass The post insertion/update payload with an added `meta_input` entry if
	 *                   the insertion/update of UTC dates is required.
	 */
	public function add_utc_dates( $post_data, WP_REST_Request $request ) {
		$json_params = $request->get_json_params();
		$meta = Tribe__Utils__Array::get( $json_params, 'meta', [] );

		// No changes to start or end? No need to update UTC dates.
		if ( ! ( isset( $meta['_EventStartDate'] ) || isset( $meta['_EventEndDate'] ) ) ) {
			return $post_data;
		}

		$post_id         = $request->get_param( 'id' );

		$timezone_string = Tribe__Events__Timezones::get_event_timezone_string( $post_id );
		$timezone_string = Tribe__Utils__Array::get( $meta, '_EventTimezone', $timezone_string );
		$timezone        = Tribe__Timezones::build_timezone_object( $timezone_string );
		$utc             = new DateTimeZone( 'UTC' );

		$start_date      = get_post_meta( $post_id, '_EventStartDate', true );
		$start_date      = Tribe__Utils__Array::get( $meta, '_EventStartDate', $start_date );
		$end_date        = get_post_meta( $post_id, '_EventEndDate', true );
		$end_date        = Tribe__Utils__Array::get( $meta, '_EventEndDate', $end_date );
		$utc_start_date  = Tribe__Date_Utils::build_date_object( $start_date, $timezone )
											->setTimezone( $utc )
											->format( Date::DBDATETIMEFORMAT );
		$utc_end_date    = Tribe__Date_Utils::build_date_object( $end_date, $timezone )
											->setTimezone( $utc )
											->format( Date::DBDATETIMEFORMAT );

		update_post_meta( $post_id, '_EventStartDateUTC', $utc_start_date );
		update_post_meta( $post_id, '_EventEndDateUTC', $utc_end_date );
	}
}
