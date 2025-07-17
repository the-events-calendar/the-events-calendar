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
use WP_REST_Response;
use WP_Error;
use WP_Post;
use Tribe\Events\Models\Post_Types\Event as Event_Model;
use TEC\Events\REST\TEC\V1\Tags\TEC_Tag;

/**
 * Archive events endpoint for the TEC REST API V1.
 *
 * @since TBD
 *
 * @package TEC\Events\REST\TEC\V1\Endpoints
 */
class Events extends Post_Entity_Endpoint implements Readable_Endpoint {
	/**
	 * The event validator.
	 *
	 * @since TBD
	 *
	 * @var Event_Validator
	 */
	protected Event_Validator $validator;

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
	 * Handles the read request for the endpoint.
	 *
	 * @since TBD
	 *
	 * @param WP_REST_Request $request The request object.
	 *
	 * @return WP_REST_Response|WP_Error The response object or WP_Error on failure.
	 */
	public function read( WP_REST_Request $request ): WP_REST_Response {
		$page     = absint( $request['page'] ?? 1 );
		$per_page = absint( $request['per_page'] ?? $this->get_default_posts_per_page() );

		// Build the event query using the ORM.
		$events_query = $this->build_events_query( $request );

		// Set pagination.
		$events_query->page( $page )->per_page( $per_page );

		$events = $this->format_post_entity_collection( $events_query->all() );
		$total  = $events_query->found();

		// Return 404 if no events found and page > 1.
		if ( empty( $events ) && $page > 1 ) {
			return new WP_REST_Response(
				[
					'code'    => 'tec_rest_events_page_not_found',
					'message' => __( 'The requested page was not found.', 'the-events-calendar' ),
				],
				404
			);
		}

		/**
		 * Filters the data that will be returned for an events archive request.
		 *
		 * @since TBD
		 *
		 * @param array           $data    The retrieved data.
		 * @param WP_REST_Request $request The original request.
		 */
		$events = apply_filters( 'tec_rest_events_archive_events', $events, $request );

		$total_pages = $per_page > 0 ? (int) ceil( $total / $per_page ) : 1;
		$current_url = $this->get_current_rest_url( $request );

		$response = new WP_REST_Response( $events );

		$response->header( 'X-WP-Total', (int) $total );
		$response->header( 'X-WP-TotalPages', (int) $total_pages );

		if ( $page < $total_pages ) {
			$response->link_header( 'next', add_query_arg( 'page', $page + 1, $current_url ) );
		}

		if ( $page > 1 ) {
			$response->link_header( 'prev', add_query_arg( 'page', $page - 1, $current_url ) );
		}

		return $response;
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
	protected function build_events_query( WP_REST_Request $request ) {
		$events_query = tribe_events();

		// Set default post status based on user capabilities.
		$cap = $this->get_post_type_object()->cap->edit_posts;
		if ( ! current_user_can( $cap ) ) {
			$events_query->where( 'post_status', 'publish' );
		}

		// Search parameter.
		if ( ! empty( $request['search'] ) ) {
			$events_query->search( $request['search'] );
		}

		// Date parameters.
		if ( ! empty( $request['start_date'] ) ) {
			$events_query->where( 'starts_after', tribe_beginning_of_day( $request['start_date'] ) );
		}

		if ( ! empty( $request['end_date'] ) ) {
			$events_query->where( 'ends_before', tribe_end_of_day( $request['end_date'] ) );
		}

		if ( ! empty( $request['status'] ) ) {
			$events_query->where( 'post_status', $request['status'] );
		}

		if ( ! empty( $request['include'] ) ) {
			$events_query->where( 'post__in', array_map( 'absint', $request['include'] ) );
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

		if ( ! empty( $request['categories'] ) ) {
			$events_query->where( 'category', array_map( 'absint', $request['categories'] ) );
		}

		if ( ! empty( $request['tags'] ) ) {
			$events_query->where( 'tag', array_map( 'absint', $request['tags'] ) );
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
	 * Gets the current REST URL for the request.
	 *
	 * @since TBD
	 *
	 * @param WP_REST_Request $request The request object.
	 *
	 * @return string The current REST URL.
	 */
	protected function get_current_rest_url( WP_REST_Request $request ): string {
		$url = rest_url( $request->get_route() );

		$params = $request->get_query_params();
		if ( ! empty( $params ) ) {
			$url = add_query_arg( $params, $url );
		}

		return $url;
	}

	/**
	 * Gets the default posts per page.
	 *
	 * @since TBD
	 *
	 * @return int The default posts per page.
	 */
	protected function get_default_posts_per_page(): int {
		/**
		 * Filters the default number of events per page.
		 *
		 * @since TBD
		 *
		 * @param int $per_page The default number of events per page.
		 */
		return apply_filters( 'tec_rest_events_default_per_page', (int) get_option( 'posts_per_page' ) );
	}

	/**
	 * Gets the maximum posts per page.
	 *
	 * @since TBD
	 *
	 * @return int The maximum posts per page.
	 */
	protected function get_max_posts_per_page(): int {
		/**
		 * Filters the maximum number of events per page.
		 *
		 * @since TBD
		 *
		 * @param int $max_per_page The maximum number of events per page.
		 */
		return apply_filters( 'tec_rest_events_max_per_page', 100 );
	}

	/**
	 * Adds properties to the events.
	 *
	 * @since TBD
	 *
	 * @param array   $formatted_post The formatted post.
	 * @param WP_Post $original_post  The original post.
	 *
	 * @return array The response with the properties added.
	 */
	protected function add_properties_to_model( array $formatted_post, WP_Post $original_post ): array {
		$properties_to_add = Event_Model::get_properties_to_add();

		$data = array_merge( (array) $formatted_post, array_intersect_key( (array) $original_post, $properties_to_add ) );

		$data['link'] = $data['permalink'];
		unset(
			$data['permalink'],
			$data['meta'],
		);

		// Reorder the links.
		$links = $data['_links'] ?? [];
		if ( ! empty( $links ) ) {
			unset( $data['_links'] );
			$data['_links'] = $links;
		}

		return $data;
	}

	/**
	 * Validates the status parameter.
	 *
	 * @since TBD
	 *
	 * @param mixed $value The value to validate.
	 *
	 * @return bool Whether the value is valid.
	 */
	public function validate_status( $value ): bool {
		$value = is_string( $value ) ? explode( ',', $value ) : $value;

		if ( ! is_array( $value ) ) {
			return false;
		}

		$invalid_statuses = array_diff( $value, self::ALLOWED_STATUS );
		if ( ! empty( $invalid_statuses ) ) {
			return false;
		}

		return true;
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
				'parameters'  => $this->get_documentation_params(),
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
	 * Returns the documentation parameters for the endpoint.
	 *
	 * @since TBD
	 *
	 * @return array
	 */
	protected function get_documentation_params(): array {
		$args   = $this->read_args();
		$params = [];

		foreach ( $args as $arg_name => $arg_schema ) {
			$param = [
				'name'        => $arg_name,
				'in'          => 'query',
				'schema'      => $arg_schema,
				'description' => $arg_schema['description'] ?? '',
				'required'    => $arg_schema['required'] ?? false,
			];

			if ( isset( $arg_schema['style'] ) ) {
				$param['style'] = $arg_schema['style'];
			}

			if ( isset( $arg_schema['explode'] ) ) {
				$param['explode'] = $arg_schema['explode'];
			}

			unset(
				$param['schema']['validate_callback'],
				$param['schema']['description'],
				$param['schema']['required'],
				$param['schema']['style'],
				$param['schema']['explode'],
			);

			$params[] = $param;
		}

		return $params;
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
			'page'       => [
				'description'       => __( 'The collection page number.', 'the-events-calendar' ),
				'type'              => 'integer',
				'default'           => 1,
				'minimum'           => 1,
				'validate_callback' => [ $this->validator, 'is_positive_int' ],
			],
			'per_page'   => [
				'description'       => __( 'Maximum number of items to be returned in result set.', 'the-events-calendar' ),
				'type'              => 'integer',
				'default'           => $this->get_default_posts_per_page(),
				'minimum'           => 1,
				'maximum'           => $this->get_max_posts_per_page(),
				'validate_callback' => [ $this->validator, 'is_positive_int' ],
			],
			'search'     => [
				'description' => __( 'Limit results to those matching a string.', 'the-events-calendar' ),
				'type'        => 'string',
			],
			'start_date' => [
				'description'       => __( 'Limit events to those starting after the specified date.', 'the-events-calendar' ),
				'type'              => 'string',
				'format'            => 'date-time',
				'validate_callback' => [ $this->validator, 'is_time' ],
			],
			'end_date'   => [
				'description'       => __( 'Limit events to those ending before the specified date.', 'the-events-calendar' ),
				'type'              => 'string',
				'format'            => 'date-time',
				'validate_callback' => [ $this->validator, 'is_time' ],
			],
			'status'     => [
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
			'include'    => [
				'description'       => __( 'Limit result set to specific IDs.', 'the-events-calendar' ),
				'type'              => 'array',
				'items'             => [ 'type' => 'integer' ],
				'validate_callback' => [ $this->validator, 'is_positive_int_list' ],
			],
			'categories' => [
				'description'       => __( 'Limit result set to events assigned specific categories.', 'the-events-calendar' ),
				'type'              => 'array',
				'items'             => [ 'type' => 'integer' ],
				'validate_callback' => [ $this->validator, 'is_event_category' ],
			],
			'tags'       => [
				'description'       => __( 'Limit result set to events assigned specific tags.', 'the-events-calendar' ),
				'type'              => 'array',
				'items'             => [ 'type' => 'integer' ],
				'validate_callback' => [ $this->validator, 'is_post_tag' ],
			],
			'venue'      => [
				'description'       => __( 'Limit result set to events assigned to specific venues.', 'the-events-calendar' ),
				'type'              => 'array',
				'items'             => [ 'type' => 'integer' ],
				'validate_callback' => [ $this->validator, 'is_venue_id_list' ],
			],
			'organizer'  => [
				'description'       => __( 'Limit result set to events assigned to specific organizers.', 'the-events-calendar' ),
				'type'              => 'array',
				'items'             => [ 'type' => 'integer' ],
				'validate_callback' => [ $this->validator, 'is_organizer_id_list' ],
			],
			'featured'   => [
				'description' => __( 'Limit result set to featured events only.', 'the-events-calendar' ),
				'type'        => 'boolean',
			],
			'orderby'    => [
				'description' => __( 'Sort collection by event attribute.', 'the-events-calendar' ),
				'type'        => 'string',
				'default'     => 'event_date',
				'enum'        => [ 'date', 'event_date', 'title', 'menu_order', 'modified' ],
			],
			'order'      => [
				'description' => __( 'Order sort attribute ascending or descending.', 'the-events-calendar' ),
				'type'        => 'string',
				'default'     => 'ASC',
				'enum'        => [ 'ASC', 'DESC' ],
			],
		];
	}
}
