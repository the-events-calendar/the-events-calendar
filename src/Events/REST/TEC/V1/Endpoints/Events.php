<?php
/**
 * Archive events endpoint for the TEC REST API V1.
 *
 * @since TBD
 *
 * @package TEC\Events\REST\TEC\V1\Endpoints
 */

declare( strict_types=1 );

namespace TEC\Events\REST\TEC\V1\Endpoints;

use TEC\Common\REST\TEC\V1\Abstracts\Post_Entity_Endpoint;
use TEC\Common\REST\TEC\V1\Contracts\Readable_Endpoint;
use Tribe__Events__Main as Events_Main;
use Tribe__Events__Validator__Base as Event_Validator;
use WP_REST_Request;
use Tribe\Events\Models\Post_Types\Event as Event_Model;
use TEC\Events\REST\TEC\V1\Tags\TEC_Tag;
use Tribe__Repository__Interface;
use TEC\Common\REST\TEC\V1\Traits\Read_Archive_Response;
use WP_Post;

/**
 * Archive events endpoint for the TEC REST API V1.
 *
 * @since TBD
 *
 * @package TEC\Events\REST\TEC\V1\Endpoints
 */
class Events extends Post_Entity_Endpoint implements Readable_Endpoint {
	use Read_Archive_Response;

	/**
	 * The event validator.
	 *
	 * @since TBD
	 *
	 * @var Event_Validator
	 */
	protected Event_Validator $validator;

	/**
	 * Returns the model class.
	 *
	 * @since TBD
	 *
	 * @return string
	 */
	public function get_model_class(): string {
		return Event_Model::class;
	}

	/**
	 * Returns whether the guest can read the object.
	 *
	 * @since TBD
	 *
	 * @return bool
	 */
	public function guest_can_read(): bool {
		return true;
	}

	/**
	 * Returns the post type of the endpoint.
	 *
	 * @since TBD
	 *
	 * @return string
	 */
	public function get_post_type(): string {
		return Events_Main::POSTTYPE;
	}

	/**
	 * Archive_Events constructor.
	 *
	 * @since TBD
	 *
	 * @param Event_Validator $validator The event validator.
	 */
	public function __construct( Event_Validator $validator ) {
		$this->validator = $validator;
	}

	/**
	 * Returns the path for the endpoint.
	 *
	 * @since TBD
	 *
	 * @return string
	 */
	public function get_path(): string {
		return '/events';
	}

	/**
	 * Returns the schema for the endpoint.
	 *
	 * @since TBD
	 *
	 * @return array
	 */
	public function get_schema(): array {
		return [
			'$schema' => 'http://json-schema.org/draft-04/schema#',
			'title'   => 'events',
			'type'    => 'array',
			'items'   => [
				'$ref' => '#/components/schemas/Event',
			],
		];
	}

	/**
	 * Builds the events query using the ORM.
	 *
	 * @since TBD
	 *
	 * @param WP_REST_Request $request The request object.
	 *
	 * @return \Tribe__Repository__Interface The events query.
	 */
	protected function build_query( WP_REST_Request $request ) {
		/** @var Tribe__Repository__Interface $events_query */
		$events_query = tribe_events();

		if ( ! empty( $request['start_date'] ) ) {
			$events_query->where( 'starts_after', tribe_beginning_of_day( $request['start_date'] ) );
		}

		if ( ! empty( $request['end_date'] ) ) {
			$events_query->where( 'ends_before', tribe_end_of_day( $request['end_date'] ) );
		}

		if ( ! empty( $request['search'] ) ) {
			$events_query->search( $request['search'] );
		}

		if ( ! empty( $request['categories'] ) ) {
			$events_query->where( 'category', array_map( 'absint', $request['categories'] ) );
		}

		if ( ! empty( $request['tags'] ) ) {
			$events_query->where( 'tag', array_map( 'absint', $request['tags'] ) );
		}

		if ( ! empty( $request['venue'] ) ) {
			$events_query->where( 'venue', array_map( 'absint', $request['venue'] ) );
		}

		if ( ! empty( $request['organizer'] ) ) {
			$events_query->where( 'organizer', array_map( 'absint', $request['organizer'] ) );
		}

		if ( isset( $request['featured'] ) ) {
			$events_query->where( 'featured', $request['featured'] );
		}

		$events_query->where( 'post_status', 'publish' );

		if ( ! empty( $request['status'] ) ) {
			$events_query->where( 'post_status', current_user_can( $this->get_post_type_object()->cap->edit_posts ) ? $request['status'] : 'publish' );
		}

		if ( ! empty( $request['post_parent'] ) ) {
			$events_query->where( 'post_parent', $request['post_parent'] );
		}

		if ( ! empty( $request['include'] ) ) {
			$events_query->where( 'post__in', array_map( 'absint', $request['include'] ) );
		}

		if ( ! empty( $request['starts_before'] ) ) {
			$events_query->where( 'starts_before', $request['starts_before'] );
		}

		if ( ! empty( $request['starts_after'] ) ) {
			$events_query->where( 'starts_after', $request['starts_after'] );
		}

		if ( ! empty( $request['ends_before'] ) ) {
			$events_query->where( 'ends_before', $request['ends_before'] );
		}

		if ( ! empty( $request['ends_after'] ) ) {
			$events_query->where( 'ends_after', $request['ends_after'] );
		}

		if ( isset( $request['ticketed'] ) ) {
			$events_query->where( 'ticketed', (bool) $request['ticketed'] );
		}

		if ( ! empty( $request['orderby'] ) ) {
			$orderby = $request['orderby'] === 'event_date' ? 'event_date' : $request['orderby'];
			$order   = ! empty( $request['order'] ) ? $request['order'] : 'ASC';
			$events_query->order_by( $orderby, $order );
		}

		/**
		 * Filters the events query in the TEC REST API.
		 *
		 * @since TBD
		 *
		 * @param \Tribe__Repository__Interface $events_query The events query.
		 * @param WP_REST_Request               $request      The request object.
		 */
		return apply_filters( 'tec_rest_events_query', $events_query, $request );
	}

	/**
	 * Returns the OpenAPI documentation for the endpoint.
	 *
	 * @since TBD
	 *
	 * @return array
	 */
	public function get_documentation(): array {
		return [
			'get' => [
				'summary'     => __( 'Get events', 'the-events-calendar' ),
				'description' => __( 'Returns a list of events', 'the-events-calendar' ),
				'operationId' => 'getEvents',
				'tags'        => [ tribe( TEC_Tag::class )->get_name() ],
				'parameters'  => $this->get_read_documentation_params(),
				'responses'   => [
					'200' => [
						'description' => __( 'Returns the list of events', 'the-events-calendar' ),
						'headers'     => [
							'X-WP-Total'      => [
								'description' => __( 'The total number of events matching the request.', 'the-events-calendar' ),
								'schema'      => [
									'type' => 'integer',
								],
							],
							'X-WP-TotalPages' => [
								'description' => __( 'The total number of pages for the request.', 'the-events-calendar' ),
								'schema'      => [
									'type' => 'integer',
								],
							],
							'Link'            => [
								'description' => __(
									'RFC 5988 Link header for pagination. Contains navigation links with relationships:
									`rel="next"` for the next page (if not on last page),
									`rel="prev"` for the previous page (if not on first page).
									Header is omitted entirely if there\'s only one page',
									'the-events-calendar'
								),
								'schema'      => [
									'type'  => 'array',
									'items' => [
										'type'   => 'string',
										'format' => 'uri',
									],
								],
								'required'    => false,
							],
						],
						'content'     => [
							'application/json' => [
								'schema' => [
									'type'  => 'array',
									'items' => [
										'$ref' => '#/components/schemas/Event',
									],
								],
							],
						],
					],
					'400' => [
						'description' => __( 'A required parameter is missing or an input parameter is in the wrong format', 'the-events-calendar' ),
					],
					'404' => [
						'description' => __( 'The requested page was not found', 'the-events-calendar' ),
					],
				],
			],
		];
	}

	/**
	 * Returns the arguments for the read request.
	 *
	 * @since TBD
	 *
	 * @return array
	 */
	public function read_args(): array {
		return [
			'page'          => [
				'description'       => __( 'The collection page number.', 'the-events-calendar' ),
				'type'              => 'integer',
				'default'           => 1,
				'minimum'           => 1,
				'validate_callback' => [ $this->validator, 'is_positive_int' ],
			],
			'per_page'      => [
				'description'       => __( 'Maximum number of items to be returned in result set.', 'the-events-calendar' ),
				'type'              => 'integer',
				'default'           => $this->get_default_posts_per_page(),
				'minimum'           => 1,
				'maximum'           => $this->get_max_posts_per_page(),
				'validate_callback' => [ $this->validator, 'is_positive_int' ],
			],
			'start_date'    => [
				'description'       => __( 'Limit events to those starting after the specified date.', 'the-events-calendar' ),
				'type'              => 'string',
				'format'            => 'date-time',
				'validate_callback' => [ $this->validator, 'is_time' ],
			],
			'end_date'      => [
				'description'       => __( 'Limit events to those ending before the specified date.', 'the-events-calendar' ),
				'type'              => 'string',
				'format'            => 'date-time',
				'validate_callback' => [ $this->validator, 'is_time' ],
			],
			'search'        => [
				'description' => __( 'Limit results to those matching a string.', 'the-events-calendar' ),
				'type'        => 'string',
			],
			'categories'    => [
				'description'       => __( 'Limit result set to events assigned specific categories.', 'the-events-calendar' ),
				'type'              => 'array',
				'items'             => [ 'type' => 'integer' ],
				'validate_callback' => [ $this->validator, 'is_event_category' ],
			],
			'tags'          => [
				'description'       => __( 'Limit result set to events assigned specific tags.', 'the-events-calendar' ),
				'type'              => 'array',
				'items'             => [ 'type' => 'integer' ],
				'validate_callback' => [ $this->validator, 'is_post_tag' ],
			],
			'venue'         => [
				'description'       => __( 'Limit result set to events assigned to specific venues.', 'the-events-calendar' ),
				'type'              => 'array',
				'items'             => [ 'type' => 'integer' ],
				'validate_callback' => [ $this->validator, 'is_venue_id_list' ],
			],
			'organizer'     => [
				'description'       => __( 'Limit result set to events assigned to specific organizers.', 'the-events-calendar' ),
				'type'              => 'array',
				'items'             => [ 'type' => 'integer' ],
				'validate_callback' => [ $this->validator, 'is_organizer_id_list' ],
			],
			'featured'      => [
				'description' => __( 'Limit result set to featured events only.', 'the-events-calendar' ),
				'type'        => 'boolean',
			],
			'status'        => [
				'description'       => __( 'Limit result set to events with specific status.', 'the-events-calendar' ),
				'type'              => 'array',
				'items'             => [
					'type' => 'string',
					'enum' => self::ALLOWED_STATUS,
				],
				'default'           => [ 'publish' ],
				'validate_callback' => [ $this, 'validate_status' ],
				'explode'           => false,
			],
			'post_parent'   => [
				'description'       => __( 'Limit result set to events with specific post parent.', 'the-events-calendar' ),
				'type'              => 'integer',
				'validate_callback' => [ $this->validator, 'is_positive_int' ],
			],
			'include'       => [
				'description'       => __( 'Limit result set to specific IDs.', 'the-events-calendar' ),
				'type'              => 'array',
				'items'             => [ 'type' => 'integer' ],
				'validate_callback' => [ $this->validator, 'is_positive_int_list' ],
			],
			'starts_before' => [
				'description'       => __( 'Limit result set to events starting before the specified date.', 'the-events-calendar' ),
				'type'              => 'string',
				'format'            => 'date-time',
				'validate_callback' => [ $this->validator, 'is_time' ],
			],
			'starts_after'  => [
				'description'       => __( 'Limit result set to events starting after the specified date.', 'the-events-calendar' ),
				'type'              => 'string',
				'format'            => 'date-time',
				'validate_callback' => [ $this->validator, 'is_time' ],
			],
			'ends_before'   => [
				'description'       => __( 'Limit result set to events ending before the specified date.', 'the-events-calendar' ),
				'type'              => 'string',
				'format'            => 'date-time',
				'validate_callback' => [ $this->validator, 'is_time' ],
			],
			'ends_after'    => [
				'description'       => __( 'Limit result set to events ending after the specified date.', 'the-events-calendar' ),
				'type'              => 'string',
				'format'            => 'date-time',
				'validate_callback' => [ $this->validator, 'is_time' ],
			],
			'ticketed'      => [
				'description' => __( 'Limit result set to events with tickets.', 'the-events-calendar' ),
				'type'        => 'boolean',
			],
			'orderby'       => [
				'description' => __( 'Sort collection by event attribute.', 'the-events-calendar' ),
				'type'        => 'string',
				'default'     => 'event_date',
				'enum'        => [ 'date', 'event_date', 'title', 'menu_order', 'modified' ],
			],
			'order'         => [
				'description' => __( 'Order sort attribute ascending or descending.', 'the-events-calendar' ),
				'type'        => 'string',
				'default'     => 'ASC',
				'enum'        => [ 'ASC', 'DESC' ],
			],
		];
	}

	/**
	 * Transforms the entity.
	 *
	 * @since TBD
	 *
	 * @param array $entity The entity to transform.
	 *
	 * @return array
	 */
	protected function transform_entity( array $entity ): array {
		if ( ! empty( $entity['organizers'] ) ) {
			$organizers           = tribe( Organizers::class );
			$entity['organizers'] = array_map( fn ( WP_Post $organizer ) => $organizers->get_formatted_entity( $organizer ), $entity['organizers']->all() );
		}

		if ( ! empty( $entity['venues'] ) ) {
			$venues           = tribe( Venues::class );
			$entity['venues'] = array_map( fn ( WP_Post $venue ) => $venues->get_formatted_entity( $venue ), $entity['venues']->all() );
		}

		return $entity;
	}
}
