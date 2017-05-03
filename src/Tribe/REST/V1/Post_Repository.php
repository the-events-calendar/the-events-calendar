<?php


class Tribe__Events__REST__V1__Post_Repository implements Tribe__Events__REST__Interfaces__Post_Repository {

	/**
	 * A post type to get data request handler map.
	 *
	 * @var array
	 */
	protected $types_get_map = array();

	/**
	 * @var Tribe__REST__Messages_Interface
	 */
	protected $messages;

	public function __construct( Tribe__REST__Messages_Interface $messages = null ) {
		$this->types_get_map = array(
			Tribe__Events__Main::POSTTYPE            => array( $this, 'get_event_data' ),
			Tribe__Events__Main::VENUE_POST_TYPE     => array( $this, 'get_venue_data' ),
			Tribe__Events__Main::ORGANIZER_POST_TYPE => array( $this, 'get_organizer_data' ),
		);

		$this->messages = $messages ? $messages : tribe( 'tec.rest-v1.messages' );
	}

	/**
	 * Retrieves an array representation of the post.
	 *
	 * @param int $id The post ID.
	 *
	 * @return array An array representation of the post.
	 */
	public function get_data( $id ) {
		$post = get_post( $id );

		if ( empty( $post ) ) {
			return array();
		}

		if ( ! isset( $this->types_get_map[ $post->post_type ] ) ) {
			return (array) $post;
		}

		return call_user_func( $this->types_get_map[ $post->post_type ], $id );
	}

	/**
	 * Returns an array representation of an event.
	 *
	 * @param int $event_id An event post ID.
	 *
	 * @return array|WP_Error Either the array representation of an event or an error object.
	 */
	public function get_event_data( $event_id ) {
		$event = get_post( $event_id );

		if ( empty( $event ) || ! tribe_is_event( $event ) ) {
			return new WP_Error( 'event-not-found', $this->messages->get_message( 'event-not-found' ) );
		}

		$meta = array_map( 'reset', get_post_custom( $event_id ) );

		$venue = $this->get_venue_data( $event_id );
		$organizer = $this->get_organizer_data( $event_id );

		$data = array(
			'id'                     => $event_id,
			'global_id'              => false,
			'global_id_lineage'      => array(),
			'id'                     => $event_id,
			'author'                 => $event->post_author,
			'date'                   => $event->post_date,
			'date_utc'               => $event->post_date_gmt,
			'modified'               => $event->post_modified,
			'modified_utc'           => $event->post_modified_gmt,
			'url'                    => get_the_permalink( $event_id ),
			'rest_url'               => tribe_events_rest_url( 'events/' . $event_id ),
			'title'                  => trim( apply_filters( 'the_title', $event->post_title ) ),
			'description'            => trim( apply_filters( 'the_content', $event->post_content ) ),
			'excerpt'                => trim( apply_filters( 'the_excerpt', $event->post_excerpt ) ),
			'image'                  => $this->get_featured_image( $event_id ),
			'all_day'                => isset( $meta['_EventAllDay'] ) ? tribe_is_truthy( $meta['_EventAllDay'] ) : false,
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
			'cost'                   => tribe_get_cost( $event_id, true ),
			'cost_details'           => array(
				'currency_symbol'   => isset( $meta['_EventCurrencySymbol'] ) ? $meta['_EventCurrencySymbol'] : '',
				'currency_position' => isset( $meta['_EventCurrencyPosition'] ) ? $meta['_EventCurrencyPosition'] : '',
				'values'            => $this->get_cost_values( $event_id ),
			),
			'website'                => isset( $meta['_EventURL'] ) ? esc_html( $meta['_EventURL'] ) : get_the_permalink( $event_id ),
			'show_map'               => isset( $meta['_EventShowMap'] ) ? (bool) $meta['_EventShowMap'] : true,
			'show_map_link'          => isset( $meta['_EventShowMapLink'] ) ? (bool) $meta['_EventShowMapLink'] : true,
			'hide_from_listings'     => isset( $meta['_EventHideFromUpcoming'] ) && $meta['_EventHideFromUpcoming'] === 'yes' ? true : false,
			'sticky'                 => $event->menu_order == - 1 ? true : false,
			'featured'               => isset( $meta['_tribe_featured'] ) && $meta['_tribe_featured'] == 1 ? true : false,
			'categories'             => $this->get_categories( $event_id ),
			'tags'                   => $this->get_tags( $event_id ),
			'venue'                  => is_wp_error( $venue ) ? array() : $venue,
			'organizer'              => is_wp_error( $organizer ) ? array() : $organizer,
		);

		// Add the Global ID fields
		$data = $this->add_global_id_fields( $data, $event_id );

		/**
		 * Filters the data that will be returnedf for a single event.
		 *
		 * @param array   $data  The data that will be returned in the response.
		 * @param WP_Post $event The requested event.
		 */
		$data = apply_filters( 'tribe_rest_event_data', $data, $event );

		return $data;
	}

	/**
	 * Returns an array representation of an event venue.
	 *
	 * @param int $event_or_venue_id An event or venue post ID.
	 *
	 * @return array|WP_Error Either the array representation of a venue or an error object.
	 */
	public function get_venue_data( $event_or_venue_id ) {
		if ( tribe_is_event( $event_or_venue_id ) ) {
			$venue = get_post( tribe_get_venue_id( $event_or_venue_id ) );
			if ( empty( $venue ) ) {
				return new WP_Error( 'event-no-venue', $this->messages->get_message( 'event-no-venue' ) );
			}
		} elseif ( tribe_is_venue( $event_or_venue_id ) ) {
			$venue = get_post( $event_or_venue_id );
		} else {
			return new WP_Error( 'venue-not-found', $this->messages->get_message( 'venue-not-found' ) );
		}

		$meta = array_map( 'reset', get_post_custom( $venue->ID ) );

		$data = array(
			'id'                => $venue->ID,
			'author'            => $venue->post_author,
			'date'              => $venue->post_date,
			'date_utc'          => $venue->post_date_gmt,
			'modified'          => $venue->post_modified,
			'modified_utc'      => $venue->post_modified_gmt,
			'url'               => get_the_permalink( $venue->ID ),
			'venue'             => trim( apply_filters( 'the_title', $venue->post_title ) ),
			'description'       => trim( apply_filters( 'the_content', $venue->post_content ) ),
			'excerpt'           => trim( apply_filters( 'the_excerpt', $venue->post_excerpt ) ),
			'image'             => $this->get_featured_image( $venue->ID ),
			'show_map'          => isset( $meta['_VenueShowMap'] ) ? (bool) $meta['_VenueShowMap'] : true,
			'show_map_link'     => isset( $meta['_VenueShowMapLink'] ) ? (bool) $meta['_VenueShowMapLink'] : true,
			'address'           => isset( $meta['_VenueAddress'] ) ? $meta['_VenueAddress'] : '',
			'city'              => isset( $meta['_VenueCity'] ) ? $meta['_VenueCity'] : '',
			'country'           => isset( $meta['_VenueCountry'] ) ? $meta['_VenueCountry'] : '',
			'province'          => isset( $meta['_VenueProvince'] ) ? $meta['_VenueProvince'] : '',
			'state'             => isset( $meta['_VenueState'] ) ? $meta['_VenueState'] : '',
			'zip'               => isset( $meta['_VenueZip'] ) ? $meta['_VenueZip'] : '',
			'phone'             => isset( $meta['_VenuePhone'] ) ? $meta['_VenuePhone'] : '',
			'website'           => isset( $meta['_VenueURL'] ) ? $meta['_VenueURL'] : '',
			'stateprovince'     => isset( $meta['_VenueStateProvince'] ) ? $meta['_VenueStateProvince'] : '',
		);

		// Add the Global ID fields
		$data = $this->add_global_id_fields( $data, $venue->ID );

		/**
		 * Filters the data that will be returned for a single venue.
		 *
		 * @param array   $data  The data that will be returned in the response.
		 * @param WP_Post $event The requested venue.
		 */
		$data = apply_filters( 'tribe_rest_venue_data', array_filter( $data ), $venue );

		/**
		 * Filters the data that will be returned for an event venue.
		 *
		 * @param array   $data  The data that will be returned in the response.
		 * @param WP_Post $event The requested event.
		 */
		$data = apply_filters( 'tribe_rest_venue_data', array_filter( $data ), get_post( $event_or_venue_id ) );

		return array_filter( $data );
	}

	/**
	 * Returns an array representation of an event organizer(s).
	 *
	 * @param int $event_or_organizer_id An event or organizer post ID.
	 *
	 * @return array|WP_Error Either an the array representation of an orgnanizer, an
	 *                        arrya of array representations of an event organizer or
	 *                        an error object.
	 */
	public function get_organizer_data( $event_or_organizer_id ) {
		if ( tribe_is_event( $event_or_organizer_id ) ) {
			$organizers = tribe_get_organizer_ids( $event_or_organizer_id );
			if ( empty( $organizers ) ) {
				return new WP_Error( 'event-no-organizer', $this->messages->get_message( 'event-no-organizer' ) );
			}
			// serializing happens...
			if ( is_array( reset( $organizers ) ) ) {
				$organizers = reset( $organizers );
			}
			$single = false;
		} elseif ( tribe_is_organizer( $event_or_organizer_id ) ) {
			$organizers = array( get_post( $event_or_organizer_id ) );
			$single = true;
		} else {
			return new WP_Error( 'organizer-not-found', $this->messages->get_message( 'organizer-not-found' ) );
		}

		$data = array();

		foreach ( $organizers as $organizer_id ) {
			$organizer = get_post( $organizer_id );

			if ( empty( $organizer ) ) {
				continue;
			}

			$meta = array_map( 'reset', get_post_custom( $organizer->ID ) );

			$this_data = array(
				'id'                => $organizer->ID,
				'author'            => $organizer->post_author,
				'date'              => $organizer->post_date,
				'date_utc'          => $organizer->post_date_gmt,
				'modified'          => $organizer->post_modified,
				'modified_utc'      => $organizer->post_modified_gmt,
				'url'               => get_the_permalink( $organizer->ID ),
				'organizer'         => trim( apply_filters( 'the_title', $organizer->post_title ) ),
				'description'       => trim( apply_filters( 'the_content', $organizer->post_content ) ),
				'excerpt'           => trim( apply_filters( 'the_excerpt', $organizer->post_excerpt ) ),
				'image'             => $this->get_featured_image( $organizer->ID ),
				'phone'             => isset( $meta['_OrganizerPhone'] ) ? $meta['_OrganizerPhone'] : '',
				'website'           => isset( $meta['_OrganizerWebsite'] ) ? $meta['_OrganizerWebsite'] : '',
				'email'             => isset( $meta['_OrganizerEmail'] ) ? $meta['_OrganizerEmail'] : '',
			);

			// Add the Global ID fields
			$this_data = $this->add_global_id_fields( $this_data, $organizer->ID );

			/**
			 * Filters the data that will be returnedf for a single organizer.
			 *
			 * @param array   $data  The data that will be returned in the response.
			 * @param WP_Post $event The requested organizer.
			 */
			$this_data = apply_filters( 'tribe_rest_organizer_data', array_filter( $this_data ), $organizer );

			$data[] = $this_data;
		}

		/**
		 * Filters the data that will be returnedf for all the organizers of an event.
		 *
		 * @param array   $data            The data that will be returned in the response; this is
		 *                                 an array of organizer data arrays.
		 * @param WP_Post $event           The requested event.
		 */
		$data = apply_filters( 'tribe_rest_event_organizer_data', array_filter( $data ), get_post( $event_or_organizer_id ) );

		$data = array_filter( $data );

		return $single ? reset( $data ) : $data;
	}

	/**
	 * Adds the Global ID fields to a set of rest data
	 *
	 * @param array  $data  Rest Array of data
	 * @param int    $id    Post ID
	 *
	 * @return array
	 */
	protected function add_global_id_fields( $data, $post_id ) {
		$global_id = new Tribe__Utils__Global_ID;
		$global_id->type( 'url' );
		$global_id->origin( home_url() );

		$lineage = get_post_meta( $post_id, Tribe__Events__Aggregator__Event::$global_id_lineage_key );

		$data['global_id'] = $global_id->generate( array( 'id' => $post_id ) );
		$data['global_id_lineage'] = array_merge( (array) $data['global_id'], (array) $lineage );

		return $data;
	}

	/**
	 * @param string $date A date string in a format `strtotime` can parse.
	 *
	 * @return array
	 */
	protected function get_date_details( $date ) {
		$time = strtotime( $date );
		return array(
			'year'    => date( 'Y', $time ),
			'month'   => date( 'm', $time ),
			'day'     => date( 'd', $time ),
			'hour'    => date( 'H', $time ),
			'minutes' => date( 'i', $time ),
			'seconds' => date( 's', $time ),
		);
	}

	protected function get_categories( $event_id ) {
		$data = $this->get_terms( $event_id, Tribe__Events__Main::TAXONOMY );

		/**
		 * Filters the data that will be returned for an event categories.
		 *
		 * @param array $data The data that will be returned in the response.
		 * @param WP_Post $event The requested event.
		 */
		$data = apply_filters( 'tribe_rest_event_categories_data', $data, get_post( $event_id ) );

		return array_filter( $data );
	}

	protected function get_tags( $event_id ) {
		$data = $this->get_terms($event_id, 'post_tag');

		/**
		 * Filters the data that will be returned for an event tags.
		 *
		 * @param array $data The data that will be returned in the response.
		 * @param WP_Post $event The requsted event.
		 */
		$data = apply_filters( 'tribe_rest_event_tags_data', $data, get_post( $event_id ) );

		return array_filter( $data );
	}

	protected function get_terms( $event_id, $taxonomy) {
		$terms = wp_get_post_terms( $event_id, $taxonomy );

		if ( empty( $terms ) ) {
			return array();
		}

		$data = array();
		foreach ( $terms as $term ) {
			$term_data = (array) $term;
			$term_data['id'] = $term_data['term_id'];
			$term_data['url'] = get_term_link( $term, $taxonomy );
			unset( $term_data['term_id'], $term_data['term_taxonomy_id'], $term_data['term_group'], $term_data['filter'] );

			/**
			 * Filters the data that will be returned for an event taxonomy term.
			 *
			 * @param array                $term_data The data that will be returned in the response for the taxonomy term.
			 * @param array|object|WP_Term $term      The term original object.
			 * @param string               $taxonomy  The term taxonomy
			 * @param WP_Post              $event     The requsted event.
			 */
			$data[] = apply_filters( 'tribe_rest_event_taxonomy_term_data', $term_data, $term, $taxonomy, get_post( $event_id ) );
		}

		return $data;
	}

	/**
	 * Returns an ASC array of event costs.
	 *
	 * @param int|WP_Post $event_id The event post or the post ID.
	 *
	 * @return array
	 */
	protected function get_cost_values( $event_id ) {
		$cost_couples = tribe( 'tec.cost-utils' )->get_event_costs( $event_id );

		global $wp_locale;
		$cost_values = array();
		foreach ( $cost_couples as $key => $value ) {
			$value = str_replace( $wp_locale->number_format['decimal_point'], '.', '' . $value );
			$value = str_replace( $wp_locale->number_format['thousands_sep'], '', $value );
			if ( is_numeric( $value ) ) {
				$cost_values[] = $value;
			} else {
				$cost_values[] = $key;
			}
		}

		sort( $cost_values, SORT_NUMERIC );

		return $cost_values;
	}

	protected function get_featured_image($id) {
		$thumbnail_id = get_post_thumbnail_id($id);

		if ( empty( $thumbnail_id ) ) {
			return false;
		}

		$full_url = get_the_post_thumbnail_url($id, 'full');
		$file = get_attached_file($thumbnail_id);

		$data = array(
			'url' => $full_url,
			'id' => $thumbnail_id,
			'extension' => pathinfo($file, PATHINFO_EXTENSION),
		);

		$metadata = wp_get_attachment_metadata( $thumbnail_id );

		if (
			false !== $metadata
			&& isset( $metadata['image_meta'] )
			&& isset( $metadata['file'] )
			&& isset( $metadata['sizes'] )
		) {
			unset( $metadata['image_meta'], $metadata['file'] );

			foreach ( $metadata['sizes'] as $size => &$meta ) {
				$size_image_src = wp_get_attachment_image_src( $thumbnail_id, $size );
				$meta['url'] = ! empty( $size_image_src[0] ) ? $size_image_src[0] : '';
				unset( $meta['file'] );
			}

			$data = array_filter( array_merge( $data, $metadata ) );
		}

		/**
		 * Filters the data that will returned for an event featured image if set.
		 *
		 * @param array   $data  The event featured image array representation.
		 * @param WP_Post $event The requested event.
		 */
		return apply_filters( 'tribe_rest_event_featured_image', $data, get_post( $id ) );
	}
}