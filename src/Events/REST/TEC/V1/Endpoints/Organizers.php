<?php
/**
 * Archive organizers endpoint for the TEC REST API V1.
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
use Tribe__Events__Validator__Base as Validator;
use WP_REST_Request;
use TEC\Common\REST\TEC\V1\Traits\Read_Archive_Response;
use Tribe\Events\Models\Post_Types\Organizer as Organizer_Model;
use TEC\Events\REST\TEC\V1\Tags\TEC_Tag;
use TEC\Common\REST\TEC\V1\Parameter_Types\Collection;
use TEC\Common\REST\TEC\V1\Parameter_Types\Positive_Integer;
use TEC\Common\REST\TEC\V1\Parameter_Types\Text;
use TEC\Common\REST\TEC\V1\Parameter_Types\Boolean;
use TEC\Common\REST\TEC\V1\Parameter_Types\Array_Of_Type;

/**
 * Archive organizers endpoint for the TEC REST API V1.
 *
 * @since TBD
 *
 * @package TEC\Events\REST\TEC\V1\Endpoints
 */
class Organizers extends Post_Entity_Endpoint implements Readable_Endpoint {
	use Read_Archive_Response;

	/**
	 * The validator.
	 *
	 * @since TBD
	 *
	 * @var Validator
	 */
	protected Validator $validator;

	/**
	 * Returns the model class.
	 *
	 * @since TBD
	 *
	 * @return string
	 */
	public function get_model_class(): string {
		return Organizer_Model::class;
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
		return Events_Main::ORGANIZER_POST_TYPE;
	}

	/**
	 * Archive_Organizers constructor.
	 *
	 * @since TBD
	 *
	 * @param Validator $validator The validator.
	 */
	public function __construct( Validator $validator ) {
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
		return '/organizers';
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
			'title'   => 'organizers',
			'type'    => 'array',
			'items'   => [
				'$ref' => '#/components/schemas/Organizer',
			],
		];
	}

	/**
	 * Builds the organizers query using the ORM.
	 *
	 * @since TBD
	 *
	 * @param WP_REST_Request $request The request object.
	 *
	 * @return \Tribe__Repository__Interface The organizers query.
	 */
	protected function build_query( WP_REST_Request $request ) {
		$organizers_query = tribe_organizers();

		if ( ! empty( $request['search'] ) ) {
			$organizers_query->search( $request['search'] );
		}

		if ( ! empty( $request['event'] ) ) {
			$organizers_query->where( 'event', $request['event'] );
		}

		if ( isset( $request['has_events'] ) ) {
			$organizers_query->where( 'has_events', (bool) $request['has_events'] );
		}

		if ( isset( $request['only_with_upcoming'] ) ) {
			$organizers_query->where( 'only_with_upcoming', (bool) $request['only_with_upcoming'] );
		}

		$organizers_query->where( 'post_status', current_user_can( $this->get_post_type_object()->cap->edit_posts ) ? $request['status'] : 'publish' );

		if ( ! empty( $request['status'] ) ) {
			$organizers_query->where( 'post_status', current_user_can( $this->get_post_type_object()->cap->edit_posts ) ? $request['status'] : 'publish' );
		}

		if ( ! empty( $request['orderby'] ) ) {
			$order = ! empty( $request['order'] ) ? $request['order'] : 'ASC';
			$organizers_query->order_by( $request['orderby'], $order );
		}

		/**
		 * Filters the organizers query in the TEC REST API.
		 *
		 * @since TBD
		 *
		 * @param \Tribe__Repository__Interface $organizers_query  The organizers query.
		 * @param WP_REST_Request               $request           The request object.
		 */
		return apply_filters( 'tec_rest_organizers_query', $organizers_query, $request );
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
				'summary'     => __( 'Get organizers', 'the-events-calendar' ),
				'description' => __( 'Returns a list of organizers', 'the-events-calendar' ),
				'operationId' => 'getOrganizers',
				'tags'        => [ tribe( TEC_Tag::class )->get_name() ],
				'parameters'  => $this->get_read_documentation_params(),
				'responses'   => [
					'200' => [
						'description' => __( 'Returns the list of organizers', 'the-events-calendar' ),
						'headers'     => [
							'X-WP-Total'      => [
								'description' => __( 'The total number of organizers matching the request.', 'the-events-calendar' ),
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
										'$ref' => '#/components/schemas/Organizer',
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
	 * @return Collection
	 */
	public function read_args(): Collection {
		$collection = new Collection();

		$collection[] = new Positive_Integer(
			'page',
			fn() => __( 'The collection page number.', 'the-events-calendar' ),
			false,
			null,
			null,
			1,
			null,
			null,
			1
		);

		$collection[] = new Positive_Integer(
			'per_page',
			fn() => __( 'Maximum number of items to be returned in result set.', 'the-events-calendar' ),
			false,
			null,
			null,
			$this->get_default_posts_per_page(),
			null,
			100,
			1
		);

		$collection[] = new Text(
			'search',
			fn() => __( 'Limit results to those matching a string.', 'the-events-calendar' ),
			false
		);

		$collection[] = new Positive_Integer(
			'event',
			fn() => __( 'Limit result set to organizers with specific event.', 'the-events-calendar' ),
			false,
		);

		$collection[] = new Boolean(
			'has_events',
			fn() => __( 'Limit result set to organizers with events.', 'the-events-calendar' ),
			false
		);

		$collection[] = new Boolean(
			'only_with_upcoming',
			fn() => __( 'Limit result set to organizers with upcoming events.', 'the-events-calendar' ),
			false
		);

		$collection[] = new Array_Of_Type(
			'status',
			fn() => __( 'Limit result set to organizers with specific status.', 'the-events-calendar' ),
			false,
			Text::class,
			null,
			[ 'publish' ],
			self::ALLOWED_STATUS,
			null,
			null,
			null,
			null,
			fn( $value ) => $this->validate_status( $value )
		);

		return $collection;
	}
}
