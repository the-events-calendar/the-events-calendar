<?php


class Tribe__Events__REST__V1__Post_Repository implements Tribe__Events__REST__Interfaces__Post_Repository {

	/**
	 * A post type to get data request handler map.
	 *
	 * @var array
	 */
	protected $types_get_map = [];

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
	 * @param int    $id      The post ID.
	 * @param string $context Context of data.
	 *
	 * @return array An array representation of the post.
	 *
	 * @since 4.6 Added $context param
	 */
	public function get_data( $id, $context = '' ) {
		$post = get_post( $id );

		if ( empty( $post ) ) {
			return array();
		}

		if ( ! isset( $this->types_get_map[ $post->post_type ] ) ) {
			return (array) $post;
		}

		return call_user_func( $this->types_get_map[ $post->post_type ], $id, $context );
	}

	/**
	 * Returns an array representation of an event.
	 *
	 * @param int    $event_id An event post ID.
	 * @param string $context  Context of data.
	 *
	 * @return array|WP_Error Either the array representation of an event or an error object.
	 *
	 * @since 4.6 Added $context param
	 */
	public function get_event_data( $event_id, $context = '' ) {
		if ( $event_id instanceof WP_Post ) {
			$event_id = $event_id->ID;
		}

		/**
		 * Action executed before the Event data is pulled before deliver the Event via REST API.
		 *
		 * @param $event_id int The ID of the event
		 *
		 * @since 4.9.4
		 */
		do_action( 'tribe_rest_before_event_data', $event_id );

		/** @var Tribe__Cache $cache */
		$cache     = tribe( 'cache' );
		$cache_key = 'rest_get_event_data_' . get_current_user_id() . '_' . $event_id . '_' . $context;

		$data = $cache->get( $cache_key, 'save_post' );

		if ( is_array( $data ) ) {
			return $data;
		}

		$event = get_post( $event_id );

		if ( empty( $event ) || ! tribe_is_event( $event ) ) {
			return new WP_Error( 'event-not-found', $this->messages->get_message( 'event-not-found' ) );
		}

		$meta = array_map( function ( $item ) {
			return reset( $item );
		}, get_post_custom( $event_id ) );

		$venue     = $this->get_venue_data( $event_id, $context );
		$organizer = $this->get_organizer_data( $event_id, $context );

		$data = array(
			'id'                     => $event_id,
			'global_id'              => false,
			'global_id_lineage'      => array(),
			'author'                 => $event->post_author,
			'status'                 => $event->post_status,
			'date'                   => $event->post_date,
			'date_utc'               => $event->post_date_gmt,
			'modified'               => $event->post_modified,
			'modified_utc'           => $event->post_modified_gmt,
			'url'                    => get_the_permalink( $event_id ),
			'rest_url'               => tribe_events_rest_url( 'events/' . $event_id ),
			'title'                  => trim( apply_filters( 'the_title', $event->post_title, $event_id ) ),
			'description'            => trim( apply_filters( 'the_content', $event->post_content ) ),
			'excerpt'                => trim( apply_filters( 'the_excerpt', $event->post_excerpt ) ),
			'slug'                   => $event->post_name,
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
				'currency_code'     => isset( $meta['_EventCurrencyCode'] ) ? $meta['_EventCurrencyCode'] : '',
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

		/**
		 * Filters the list of contexts that should trigger the attachment of the JSON LD information to the event
		 * REST representation.
		 *
		 * @since 4.6
		 *
		 * @param array $json_ld_contexts An array of contexts.
		 */
		$json_ld_contexts = apply_filters( 'tribe_rest_event_json_ld_data_contexts', array( 'single' ) );

		if ( in_array( $context, $json_ld_contexts, true ) ) {
			$json_ld_data = tribe( 'tec.json-ld.event' )->get_data( $event );

			if ( $json_ld_data ) {
				$data['json_ld'] = $json_ld_data[ $event->ID ];
			}
		}

		// Add the Global ID fields
		$data = $this->add_global_id_fields( $data, $event_id );

		/**
		 * Filters the data that will be returnedf for a single event.
		 *
		 * @param array   $data  The data that will be returned in the response.
		 * @param WP_Post $event The requested event.
		 */
		$data = apply_filters( 'tribe_rest_event_data', $data, $event );

		$cache->set( $cache_key, $data, Tribe__Cache::NON_PERSISTENT, 'save_post' );

		return $data;
	}

	/**
	 * Returns an array representation of an event venue.
	 *
	 * @param int    $event_or_venue_id An event or venue post ID.
	 * @param string $context           Context of data.
	 *
	 * @return array|WP_Error Either the array representation of a venue or an error object.
	 *
	 * @since 4.6 Added $context param
	 */
	public function get_venue_data( $event_or_venue_id, $context = '' ) {
		if ( $event_or_venue_id instanceof WP_Post ) {
			$event_or_venue_id = tribe_get_venue_id( $event_or_venue_id->ID );
		}

		/** @var Tribe__Cache $cache */
		$cache     = tribe( 'cache' );
		$cache_key = 'rest_get_venue_data_' . get_current_user_id() . '_' . $event_or_venue_id . '_' . $context;

		$data = $cache->get( $cache_key, 'save_post' );

		if ( is_array( $data ) ) {
			return $data;
		}

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

		$meta = array_map( function ( $item ) {
			return reset( $item );
		}, get_post_custom( $venue->ID ) );

		$data = array(
			'id'            => $venue->ID,
			'author'        => $venue->post_author,
			'status'        => $venue->post_status,
			'date'          => $venue->post_date,
			'date_utc'      => $venue->post_date_gmt,
			'modified'      => $venue->post_modified,
			'modified_utc'  => $venue->post_modified_gmt,
			'url'           => get_the_permalink( $venue->ID ),
			'venue'         => trim( apply_filters( 'the_title', $venue->post_title, $venue->ID ) ),
			'description'   => trim( apply_filters( 'the_content', $venue->post_content ) ),
			'excerpt'       => trim( apply_filters( 'the_excerpt', $venue->post_excerpt ) ),
			'slug'          => $venue->post_name,
			'image'         => $this->get_featured_image( $venue->ID ),
			'address'       => isset( $meta['_VenueAddress'] ) ? $meta['_VenueAddress'] : '',
			'city'          => isset( $meta['_VenueCity'] ) ? $meta['_VenueCity'] : '',
			'country'       => isset( $meta['_VenueCountry'] ) ? $meta['_VenueCountry'] : '',
			'province'      => isset( $meta['_VenueProvince'] ) ? $meta['_VenueProvince'] : '',
			'state'         => isset( $meta['_VenueState'] ) ? $meta['_VenueState'] : '',
			'zip'           => isset( $meta['_VenueZip'] ) ? $meta['_VenueZip'] : '',
			'phone'         => isset( $meta['_VenuePhone'] ) ? $meta['_VenuePhone'] : '',
			'website'       => isset( $meta['_VenueURL'] ) ? $meta['_VenueURL'] : '',
			'stateprovince' => isset( $meta['_VenueStateProvince'] ) ? $meta['_VenueStateProvince'] : '',
		);

		// Add geo coordinates (if any)
		$geo = tribe_get_coordinates( $venue->ID );

		if ( ! empty( $geo['lat'] ) && ! empty( $geo['lng'] ) ) {
			$data['geo_lat'] = $geo['lat'];
			$data['geo_lng'] = $geo['lng'];
		}

		/**
		 * Filters the list of contexts that should trigger the attachment of the JSON LD information to the venue
		 * REST representation.
		 *
		 * @since 4.6
		 *
		 * @param array $json_ld_contexts An array of contexts.
		 */
		$json_ld_contexts = apply_filters( 'tribe_rest_venue_json_ld_data_contexts', array( 'single' ) );

		if ( in_array( $context, $json_ld_contexts, true ) ) {
			$json_ld_data = tribe( 'tec.json-ld.venue' )->get_data( $venue );

			if ( $json_ld_data ) {
				$data['json_ld'] = $json_ld_data[ $venue->ID ];
			}
		}

		$data = array_filter( $data );

		$data['show_map']      = isset( $meta['_VenueShowMap'] ) ? tribe_is_truthy( $meta['_VenueShowMap'] ) : true;
		$data['show_map_link'] = isset( $meta['_VenueShowMapLink'] ) ? tribe_is_truthy( $meta['_VenueShowMapLink'] ) : true;

		// Add the Global ID fields
		$data = $this->add_global_id_fields( $data, $venue->ID );

		$event = null;

		if ( tribe_is_event( $event_or_venue_id ) ) {
			$event = get_post( $event_or_venue_id );
		}

		/**
		 * Filters the data that will be returned for a single venue.
		 *
		 * @param array        $data  The data that will be returned in the response.
		 * @param WP_Post      $venue The requested venue.
		 * @param WP_Post|null $event The requested event, if event ID was used.
		 */
		$data = apply_filters( 'tribe_rest_venue_data', $data, $venue, $event );

		$cache->set( $cache_key, $data, Tribe__Cache::NON_PERSISTENT, 'save_post' );

		return $data;
	}

	protected function get_featured_image( $id ) {
		$thumbnail_id = get_post_thumbnail_id( $id );

		if ( empty( $thumbnail_id ) ) {
			return false;
		}

		$full_url = get_the_post_thumbnail_url( $id, 'full' );
		$file = get_attached_file( $thumbnail_id );

		$data = array(
			'url'       => $full_url,
			'id'        => $thumbnail_id,
			'extension' => pathinfo( $file, PATHINFO_EXTENSION ),
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

	/**
	 * Adds the Global ID fields to a set of rest data
	 *
	 * @param array $data Rest Array of data
	 * @param int   $id   Post ID
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
	 * Returns an array representation of an event organizer(s).
	 *
	 * @param int    $event_or_organizer_id An event or organizer post ID.
	 * @param string $context               Context of data.
	 *
	 * @return array|WP_Error Either an the array representation of an orgnanizer, an
	 *                        arrya of array representations of an event organizer or
	 *                        an error object.
	 *
	 * @since 4.6 Added $context param
	 */
	public function get_organizer_data( $event_or_organizer_id, $context = '' ) {
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

		/** @var Tribe__Cache $cache */
		$cache = tribe( 'cache' );

		foreach ( $organizers as $organizer_id ) {
			if ( is_object( $organizer_id ) ) {
				$organizer = $organizer_id;

				$organizer_id = $organizer->ID;
			}

			$cache_key = 'rest_get_organizer_data_' . get_current_user_id() . '_' . $organizer_id . '_' . $context;

			$this_data = $cache->get( $cache_key, 'save_post' );

			if ( is_array( $this_data ) ) {
				$data[] = $this_data;

				continue;
			}

			$organizer = get_post( $organizer_id );

			if ( empty( $organizer ) ) {
				continue;
			}

			$meta = array_map( function ( $item ) {
				return reset( $item );
			}, get_post_custom( $organizer->ID ) );

			$this_data = array(
				'id'           => $organizer->ID,
				'author'       => $organizer->post_author,
				'status'       => $organizer->post_status,
				'date'         => $organizer->post_date,
				'date_utc'     => $organizer->post_date_gmt,
				'modified'     => $organizer->post_modified,
				'modified_utc' => $organizer->post_modified_gmt,
				'url'          => get_the_permalink( $organizer->ID ),
				'organizer'    => trim( apply_filters( 'the_title', $organizer->post_title, $organizer->ID ) ),
				'description'  => trim( apply_filters( 'the_content', $organizer->post_content ) ),
				'excerpt'      => trim( apply_filters( 'the_excerpt', $organizer->post_excerpt ) ),
				'slug'         => $organizer->post_name,
				'image'        => $this->get_featured_image( $organizer->ID ),
				'phone'        => isset( $meta['_OrganizerPhone'] ) ? $meta['_OrganizerPhone'] : '',
				'website'      => isset( $meta['_OrganizerWebsite'] ) ? $meta['_OrganizerWebsite'] : '',
				'email'        => isset( $meta['_OrganizerEmail'] ) ? $meta['_OrganizerEmail'] : '',
			);

			/**
			 * Filters the list of contexts that should trigger the attachment of the JSON LD information to the organizer
			 * REST representation.
			 *
			 * @since 4.6
			 *
			 * @param array $json_ld_contexts An array of contexts.
			 */
			$json_ld_contexts = apply_filters( 'tribe_rest_organizer_json_ld_data_contexts', array( 'single' ) );

			if ( in_array( $context, $json_ld_contexts, true ) ) {
				$json_ld_data = tribe( 'tec.json-ld.organizer' )->get_data( $organizer );

				if ( $json_ld_data ) {
					$this_data['json_ld'] = $json_ld_data[ $organizer->ID ];
				}
			}

			// Add the Global ID fields
			$this_data = $this->add_global_id_fields( $this_data, $organizer->ID );

			/**
			 * Filters the data that will be returnedf for a single organizer.
			 *
			 * @param array   $data  The data that will be returned in the response.
			 * @param WP_Post $event The requested organizer.
			 */
			$this_data = apply_filters( 'tribe_rest_organizer_data', array_filter( $this_data ), $organizer );

			$cache->set( $cache_key, $this_data, Tribe__Cache::NON_PERSISTENT, 'save_post' );

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

	/**
	 * Returns an ASC array of event costs.
	 *
	 * @param int|WP_Post $event_id The event post or the post ID.
	 *
	 * @return array
	 */
	protected function get_cost_values( $event_id ) {
		/** @var Tribe__Cost_Utils $cost_utils */
		$cost_utils   = tribe( 'tec.cost-utils' );
		$cost_couples = $cost_utils->get_event_costs( $event_id );

		$cost_values = array();
		foreach ( $cost_couples as $key => $value ) {
			list( $decimal_sep, $thousands_sep ) = $cost_utils->parse_separators( $value );

			$value = str_replace( $thousands_sep, '', $value );
			$value = str_replace( $decimal_sep, '.', '' . $value );

			if ( is_numeric( $value ) ) {
				$cost_values[] = $value;
			} else {
				$cost_values[] = $key;
			}
		}

		sort( $cost_values, SORT_NUMERIC );

		return $cost_values;
	}

	/**
	 * Returns the categories assigned to the specified event.
	 *
	 * @since 4.6
	 *
	 * @param int $event_id
	 *
	 * @return array
	 */
	protected function get_categories( $event_id ) {
		$data = $this->get_terms( $event_id, Tribe__Events__Main::TAXONOMY );

		/**
		 * Filters the data that will be returned for an event categories.
		 *
		 * @param array   $data  The data that will be returned in the response.
		 * @param WP_Post $event The requested event.
		 */
		$data = apply_filters( 'tribe_rest_event_categories_data', $data, get_post( $event_id ) );

		return array_filter( $data );
	}

	/**
	 * Returns the terms associated with an event.
	 *
	 * @since 4.6
	 *
	 * @param int $event_id An event post ID.
	 * @param string $taxonomy
	 *
	 * @return array
	 */
	public function get_terms( $event_id, $taxonomy ) {
		$terms = wp_get_post_terms( $event_id, $taxonomy );

		if ( empty( $terms ) || is_wp_error( $terms ) ) {
			return array();
		}

		$terms_data = $this->prepare_terms_data( $terms, $taxonomy );

		$data = array();
		foreach ( $terms_data as $term_data ) {
			$term_id = $term_data['id'];

			$term    = get_term( $term_id, $taxonomy );
			/**
			 * Filters the data that will be returned for an event taxonomy term.
			 *
			 * @param array                $term_data The data that will be returned in the response for the taxonomy term.
			 * @param array|object|WP_Term $term      The term original object.
			 * @param string               $taxonomy  The term taxonomy
			 * @param WP_Post              $event     The requested event.
			 *
			 * @since 4.5
			 */
			$data[] = apply_filters( 'tribe_rest_event_taxonomy_term_data', $term_data, $term, $taxonomy, get_post( $event_id ) );
		}

		return $data;
	}

	/**
	 * Returns the tags assigned to the specified event.
	 *
	 * @since 4.6
	 *
	 * @param int $event_id
	 *
	 * @return array
	 */
	protected function get_tags( $event_id ) {
		$data = $this->get_terms( $event_id, 'post_tag' );

		/**
		 * Filters the data that will be returned for an event tags.
		 *
		 * @param array   $data  The data that will be returned in the response.
		 * @param WP_Post $event The requsted event.
		 */
		$data = apply_filters( 'tribe_rest_event_tags_data', $data, get_post( $event_id ) );

		return array_filter( $data );
	}

	/**
	 * Returns an array of prepared array representations of a taxonomy term.
	 *
	 * @since 4.6
	 *
	 * @param array $terms_data An array of term objects.
	 * @param string $taxonomy The taxonomy of the term objects.
	 *
	 * @return array|\WP_Error Either the array representation of taxonomy terms or an error object.
	 */
	public function prepare_terms_data( array $terms_data, $taxonomy ) {
		$data = array();
		foreach ( $terms_data as $term_data ) {
			if ( empty( $term_data ) ) {
				continue;
			}

			$namespace_map = array(
				Tribe__Events__Main::TAXONOMY => 'categories',
				'post_tag'                    => 'tags',
			);

			$namespace = Tribe__Utils__Array::get( $namespace_map, $taxonomy, reset( $namespace_map ) );

			$data[] = $this->prepare_term_data( $term_data, $taxonomy, $namespace );
		}

		return $data;
	}

	/**
	 * Prepares a single term data for the response.
	 *
	 * @since 4.6
	 *
	 * @param array  $term_data
	 * @param string $taxonomy
	 * @param string $namespace
	 *
	 * @return array
	 */
	public function prepare_term_data( $term_data, $taxonomy, $namespace ) {
		if ( empty( $term_data ) ) {
			return array();
		}

		$term_data = (array) $term_data;

		if ( empty( $term_data['id'] ) && empty( $term_data['term_id'] ) ) {
			return array();
		}

		$rename_map = array(
			'link' => 'url',
			'term_id' => 'id',
		);

		foreach ( $rename_map as $old => $new ) {
			if ( ! isset( $term_data[ $old ] ) || isset( $term_data[ $new ] ) ) {
				continue;
			}
			$term_data[ $new ] = $term_data[ $old ];
			unset( $term_data[ $old ] );
		}

		unset( $term_data['_links'] );

		$term_id = $term_data['id'];

		$term_data['urls'] = array(
			'self'       => tribe_events_rest_url( "{$namespace}/{$term_id}" ),
			'collection' => tribe_events_rest_url( $namespace ),
		);

		if ( ! empty( $term_data['parent'] ) ) {
			$term_data['urls']['up'] = tribe_events_rest_url( "{$namespace}/{$term_data['parent']}" );
		}

		/**
		 * Filters the data that will be returned for a taxonomy term.
		 *
		 * @param array                $term_data The data that will be returned in the response for the taxonomy term.
		 * @param string               $taxonomy  The term taxonomy
		 *
		 * @since 4.6
		 */
		$data = apply_filters( 'tribe_rest_taxonomy_term_data', $term_data, $taxonomy );

		return $data;
	}
}
