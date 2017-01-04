<?php


class Tribe__Events__REST__V1__Endpoints__Single_Event extends Tribe__Events__REST__V1__Endpoints__Base implements
	Tribe__REST__Endpoints__Endpoint_Interface {

	/**
	 * @var Tribe__REST__Main
	 */
	protected $main;

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
		$id = $request['id'];

		if ( empty( $id ) ) {
			$message = $this->messages->get_message( 'missing-event-id' );

			return new WP_Error( 'missing-event-id', $message, array( 'status' => 400 ) );
		}

		if ( ! tribe_is_event( $id ) ) {
			$message = $this->messages->get_message( 'event-not-found' );

			return new WP_Error( 'event-not-found', $message, array( 'status' => 404 ) );
		}

		if ( ! current_user_can( 'read', $id ) ) {
			$message = $this->messages->get_message( 'event-not-accessible' );

			return new WP_Error( 'event-not-accessible', $message, array( 'status' => 403 ) );
		}

		$event = get_post( $id );
		$meta = get_post_custom( $id );

		$data = array(
			'ID'                     => $id,
			'link'                   => get_the_permalink( $id ),
			'rest_url'               => $this->get_rest_url( $id ),
			'title'                  => apply_filters( 'the_title', $event->post_title ),
			'description'            => apply_filters( 'the_content', $event->post_content ),
			'excerpt'                => apply_filters( 'the_excerpt', $event->post_excerpt ),
			'featured_image'         => get_the_post_thumbnail_url( $id, 'full' ),
			'start_date'             => $meta['_EventStartDate'],
			'start_date_details'     => $this->get_date_details( $meta['_EventStartDate'] ),
			'end_date'               => $meta['_EventEndDate'],
			'end_date_details'       => $this->get_date_details( $meta['_EventEndDate'] ),
			'utc_start_date'         => $meta['_EventStartDateUTC'],
			'utc_start_date_details' => $this->get_date_details( $meta['_EventStartDateUTC'] ),
			'utc_end_date'           => $meta['_EventEndDateUTC'],
			'utc_end_date_details'   => $this->get_date_details( $meta['_EventEndDateUTC'] ),
			'timezone'               => $meta['_EventTimezone'],
			'timezone_abbr'          => $meta['_EventTimezoneAbbr'],
			'cost'                   => tribe_get_cost( $id ),
			'cost_details'           => array(
				'currency_symbol'          => $meta['_EventCurrencySymbol'],
				'currency_symbol_position' => $meta['_EventCurrencyPosition'],
				'cost'                     => $meta['_EventCost'],
			),
			'website'                => esc_html( $meta['_EventURL'] ),
			'show_map'               => $meta['_EventShowMap'],
			'show_map_link'          => $meta['_EventShowMapLink'],
			'categories'             => $this->get_categories(),
			'tags'                   => $this->get_tags(),
			'venue'                  => $this->get_venue(),
			'organizer'              => $this->get_organizer(),
		);

		return new WP_REST_Response( $data );
	}

	/**
	 * @param string $date A date string in a format `strtotime` can parse.
	 *
	 * @return array
	 */
	protected function get_date_details( $date ) {
		return array(
			'year'   => date( 'Y', strtotime( $date ) ),
			'month'  => date( 'm', strtotime( $date ) ),
			'day'    => date( 'd', strtotime( $date ) ),
			'hour'   => date( 'H', strtotime( $date ) ),
			'minute' => date( 'i', strtotime( $date ) ),
			'second' => date( 's', strtotime( $date ) ),
		);
	}

	protected function get_rest_url( $id ) {
		return $this->main->get_url( '/events/' . $id );
	}

	protected function get_venue() {
		return array();
	}

	protected function get_organizer() {
		return array();
	}

	protected function get_categories() {
	}

	protected function get_tags() {
	}
}