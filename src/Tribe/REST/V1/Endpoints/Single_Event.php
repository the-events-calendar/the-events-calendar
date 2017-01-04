<?php


class Tribe__Events__REST__V1__Endpoints__Single_Event extends Tribe__Events__REST__V1__Endpoints__Base implements
	Tribe__REST__Endpoints__Endpoint_Interface {

	/**
	 * @var Tribe__REST__Main
	 */
	protected $main;

	/**
	 * @var WP_REST_Request
	 */
	protected $serving;

	/**
	 * Tribe__Events__REST__V1__Endpoints__Single_Event constructor.
	 *
	 * @param Tribe__REST__Main $main
	 */
	public function __construct( Tribe__REST__Messages_Interface $messages, Tribe__REST__Main $main ) {
		parent::__construct( $messages );
		$this->main = $main;
	}

	/**
	 * @param WP_REST_Request $request
	 *
	 * @return WP_REST_Response|WP_Error An array containing the data on success or a WP_Error instance on failure.
	 */
	public function get( WP_REST_Request $request ) {
		$this->serving = $request;

		$id = $request['id'];

		if ( empty( $id ) ) {
			$message = $this->messages->get_message( 'missing-event-id' );

			return new WP_Error( 'missing-event-id', $message, array( 'status' => 400 ) );
		}

		if ( ! tribe_is_event( $id ) ) {
			$message = $this->messages->get_message( 'event-not-found' );

			return new WP_Error( 'event-not-found', $message, array( 'status' => 404 ) );
		}

		$event = get_post( $id );

		if ( ! ( 'publish' === $event->post_status || current_user_can( 'edit_posts', $id ) ) ) {
			$message = $this->messages->get_message( 'event-not-accessible' );

			return new WP_Error( 'event-not-accessible', $message, array( 'status' => 403 ) );
		}

		$meta = array_map( 'reset', get_post_custom( $id ) );

		$data = array(
			'ID'                     => $id,
			'author'                 => $event->post_author,
			'date'                   => $event->post_date,
			'date_utc'               => $event->post_date_gmt,
			'modified'               => $event->post_modified,
			'modified_utc'           => $event->post_modified_gmt,
			'link'                   => get_the_permalink( $id ),
			'rest_url'               => $this->get_rest_url( $id ),
			'title'                  => trim( apply_filters( 'the_title', $event->post_title ) ),
			'description'            => trim( apply_filters( 'the_content', $event->post_content ) ),
			'excerpt'                => trim( apply_filters( 'the_excerpt', $event->post_excerpt ) ),
			'featured_image'         => get_the_post_thumbnail_url( $id, 'full' ),
			'featured_image_details' => $this->get_featured_image_details( $id ),
			'start_date'             => $meta['_EventStartDate'],
			'start_date_details'     => $this->get_date_details( $meta['_EventStartDate'] ),
			'end_date'               => $meta['_EventEndDate'],
			'end_date_details'       => $this->get_date_details( $meta['_EventEndDate'] ),
			'utc_start_date'         => $meta['_EventStartDateUTC'],
			'utc_start_date_details' => $this->get_date_details( $meta['_EventStartDateUTC'] ),
			'utc_end_date'           => $meta['_EventEndDateUTC'],
			'utc_end_date_details'   => $this->get_date_details( $meta['_EventEndDateUTC'] ),
			'timezone'               => isset( $meta['_EventTimezone'] ) ? $meta['_EventTimezone'] : '',
			'timezone_abbr'          => isset( $meta['_EventTimezoneAbbr'] ) ? $meta['_EventTimezoneAbbr'] : '',
			'cost'                   => tribe_get_cost( $id ),
			'cost_details'           => array(
				'currency_symbol'   => isset( $meta['_EventCurrencySymbol'] ) ? $meta['_EventCurrencySymbol'] : '',
				'currency_position' => isset( $meta['_EventCurrencyPosition'] ) ? $meta['_EventCurrencyPosition'] : '',
				'cost'              => isset( $meta['_EventCost'] ) ? $meta['_EventCost'] : '',
			),
			'website'                => isset( $meta['_EventURL'] ) ? esc_html( $meta['_EventURL'] ) : '',
			'show_map'               => isset( $meta['_EventShowMap'] ) ? $meta['_EventShowMap'] : '0',
			'show_map_link'          => isset( $meta['_EventShowMapLink'] ) ? $meta['_EventShowMapLink'] : '0',
			'categories'             => $this->get_categories(),
			'tags'                   => $this->get_tags(),
			'venue'                  => $this->get_venue( $id ),
			'organizer'              => $this->get_organizer( $id ),
		);

		/**
		 * Filters the data that will be returnedf for a single event.
		 *
		 * @param array           $data    The data that will be returned in the response.
		 * @param WP_Post         $event   The requested event.
		 * @param WP_REST_Request $request The original request object.
		 */
		$data = apply_filters( 'tribe_rest_event_data', $data, $event, $request );

		return new WP_REST_Response( $data );
	}

	/**
	 * @param string $date A date string in a format `strtotime` can parse.
	 *
	 * @return array
	 */
	protected function get_date_details( $date ) {
		return array(
			'year'    => date( 'Y', strtotime( $date ) ),
			'month'   => date( 'm', strtotime( $date ) ),
			'day'     => date( 'd', strtotime( $date ) ),
			'hour'    => date( 'H', strtotime( $date ) ),
			'minutes' => date( 'i', strtotime( $date ) ),
			'seconds' => date( 's', strtotime( $date ) ),
		);
	}

	protected function get_rest_url( $id ) {
		return $this->main->get_url( '/events/' . $id );
	}

	protected function get_venue( $id ) {
		$venue = get_post( tribe_get_venue_id( $id ) );

		if ( empty( $venue ) ) {
			return array();
		}

		$meta = array_map( 'reset', get_post_custom( $venue->ID ) );

		$data = array(
			'ID'                     => $venue->ID,
			'author'                 => $venue->post_author,
			'date'                   => $venue->post_date,
			'date_utc'               => $venue->post_date_gmt,
			'modified'               => $venue->post_modified,
			'modified_utc'           => $venue->post_modified_gmt,
			'link'                   => get_the_permalink( $venue->ID ),
			'title'                  => trim( apply_filters( 'the_title', $venue->post_title ) ),
			'description'            => trim( apply_filters( 'the_content', $venue->post_content ) ),
			'excerpt'                => trim( apply_filters( 'the_excerpt', $venue->post_excerpt ) ),
			'featured_image'         => get_the_post_thumbnail_url( $venue->ID, 'full' ),
			'featured_image_details' => $this->get_featured_image_details( $venue->ID ),
			'show_map'               => isset( $meta['_EventShowMap'] ) ? $meta['_EventShowMap'] : '0',
			'show_map_link'          => isset( $meta['_EventShowMapLink'] ) ? $meta['_EventShowMapLink'] : '0',
			'address'                => isset( $meta['_VenueAddress	'] ) ? $meta[''] : '',
			'city'                   => isset( $meta['_VenueCity'] ) ? $meta['_VenueCity'] : '',
			'country'                => isset( $meta['_VenueCountry'] ) ? $meta['_VenueCountry'] : '',
			'province'               => isset( $meta['_VenueProvince'] ) ? $meta['_VenueProvince'] : '',
			'state'                  => isset( $meta['_VenueState'] ) ? $meta['_VenueState'] : '',
			'zip'                    => isset( $meta['_VenueZip'] ) ? $meta['_VenueZip'] : '',
			'phone'                  => isset( $meta['_VenuePhone'] ) ? $meta['_VenuePhone'] : '',
			'website'                => isset( $meta['_VenueURL'] ) ? $meta['_VenueURL'] : '',
			'state_province'         => isset( $meta['_VenueStateProvince'] ) ? $meta['_VenueStateProvince'] : '',
		);

		/**
		 * Filters the data that will be returnedf for a single venue.
		 *
		 * @param array           $data    The data that will be returned in the response.
		 * @param WP_Post         $event   The requested venue.
		 * @param WP_REST_Request $request The original request object.
		 */
		$data = apply_filters( 'tribe_rest_venue_data', array_filter( $data ), $venue, $this->serving );

		/**
		 * Filters the data that will be returnedf for an event venue.
		 *
		 * @param array           $data    The data that will be returned in the response.
		 * @param WP_Post         $event   The requested event.
		 * @param WP_REST_Request $request The original request object.
		 */
		$data = apply_filters( 'tribe_rest_event_venue_data', array_filter( $data ), get_post( $id ), $this->serving );

		return array_filter( $data );
	}

	protected function get_organizer( $id ) {
		$organizers = tribe_get_organizer_ids( $id );

		if ( empty( $organizers ) ) {
			return array();
		}

		$data = array();

		foreach ( $organizers as $organizer_id ) {
			$organizer = get_post( $organizer_id );
			if ( empty( $organizer ) ) {
				continue;
			}
			$this_data = array(
				'ID'                     => $organizer->ID,
				'author'                 => $organizer->post_author,
				'date'                   => $organizer->post_date,
				'date_utc'               => $organizer->post_date_gmt,
				'modified'               => $organizer->post_modified,
				'modified_utc'           => $organizer->post_modified_gmt,
				'link'                   => get_the_permalink( $organizer->ID ),
				'title'                  => trim( apply_filters( 'the_title', $organizer->post_title ) ),
				'description'            => trim( apply_filters( 'the_content', $organizer->post_content ) ),
				'excerpt'                => trim( apply_filters( 'the_excerpt', $organizer->post_excerpt ) ),
				'featured_image'         => get_the_post_thumbnail_url( $organizer->ID, 'full' ),
				'featured_image_details' => $this->get_featured_image_details( $organizer->ID ),
				'phone'                  => isset( $meta['_OrganizerPhone'] ) ? $meta['_OrganizerPhone'] : '',
				'website'                => isset( $meta['_OrganizerWebsite'] ) ? $meta['_OrganizerWebsite'] : '',
				'email'                  => isset( $meta['_OrganizerEmail'] ) ? $meta['_OrganizerEmail'] : '',
			);


			/**
			 * Filters the data that will be returnedf for a single organizer.
			 *
			 * @param array           $data    The data that will be returned in the response.
			 * @param WP_Post         $event   The requested organizer.
			 * @param WP_REST_Request $request The original request object.
			 */
			$this_data = apply_filters( 'tribe_rest_organizer_data', array_filter( $this_data ), $organizer, $this->serving );

			$data[] = $this_data;
		}

		/**
		 * Filters the data that will be returnedf for all the organizers of an event.
		 *
		 * @param array           $data    The data that will be returned in the response; this is
		 *                                 an array of organizer data arrays.
		 * @param WP_Post         $event   The requested event.
		 * @param WP_REST_Request $request The original request object.
		 */
		$data = apply_filters( 'tribe_rest_event_organizer_data', array_filter( $data ), get_post( $id ), $this->serving );

		return array_filter( $data );
	}

	protected function get_categories() {
		return array();
	}

	protected function get_tags() {
		return array();
	}

	/**
	 * @param int $id The event post ID.
	 */
	protected function get_featured_image_details( $id ) {
		$thumbnail_id = get_post_thumbnail_id( $id );

		if ( empty( $thumbnail_id ) ) {
			return array();
		}

		$metadata = wp_get_attachment_metadata( $thumbnail_id );
		$data = array( 'ID' => $thumbnail_id );

		if ( false !== $metadata
		     && isset( $metadata['image_meta'] )
		     && isset( $metadata['file'] )
		     && isset( $metadata['sizes'] )
		) {
			unset( $metadata['image_meta'], $metadata['file'] );
			$metadata['url'] = wp_get_attachment_image_src( $thumbnail_id, 'full' )[0];

			foreach ( $metadata['sizes'] as $size => &$meta ) {
				$meta['url'] = wp_get_attachment_image_src( $thumbnail_id, $size )[0];
				unset( $meta['file'] );
			}

			$data = array_filter( array_merge( $data, $metadata ) );
		}

		return $data;
	}
}