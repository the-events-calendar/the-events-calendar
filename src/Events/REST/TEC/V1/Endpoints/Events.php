<?php
/**
 * Archive events endpoint for the TEC REST API V1.
 *
 * @since 6.15.0
 *
 * @package TEC\Events\REST\TEC\V1\Endpoints
 */

declare( strict_types=1 );

namespace TEC\Events\REST\TEC\V1\Endpoints;

use TEC\Common\REST\TEC\V1\Abstracts\Post_Entity_Endpoint;
use TEC\Common\REST\TEC\V1\Contracts\Readable_Endpoint;
use TEC\Common\REST\TEC\V1\Contracts\Creatable_Endpoint;
use Tribe__Events__Main as Events_Main;
use Tribe__Events__Validator__Base as Event_Validator;
use Tribe\Events\Models\Post_Types\Event as Event_Model;
use TEC\Events\REST\TEC\V1\Tags\TEC_Tag;
use TEC\Common\REST\TEC\V1\Traits\Read_Archive_Response;
use TEC\Common\REST\TEC\V1\Traits\Create_Entity_Response;
use TEC\Common\REST\TEC\V1\Collections\HeadersCollection;
use TEC\Common\REST\TEC\V1\Collections\QueryArgumentCollection;
use TEC\Common\REST\TEC\V1\Collections\RequestBodyCollection;
use TEC\Common\REST\TEC\V1\Parameter_Types\Boolean;
use TEC\Common\REST\TEC\V1\Parameter_Types\Positive_Integer;
use TEC\Common\REST\TEC\V1\Parameter_Types\Date_Time;
use TEC\Common\REST\TEC\V1\Parameter_Types\Text;
use TEC\Common\REST\TEC\V1\Parameter_Types\Array_Of_Type;
use TEC\Common\REST\TEC\V1\Endpoints\OpenApiDocs;
use TEC\Common\REST\TEC\V1\Parameter_Types\URI;
use TEC\Events\REST\TEC\V1\Documentation\Event_Definition;
use TEC\Events\REST\TEC\V1\Documentation\Event_Request_Body_Definition;
use TEC\Common\REST\TEC\V1\Documentation\OpenAPI_Schema;
use TEC\Common\REST\TEC\V1\Parameter_Types\Definition_Parameter;
use TEC\Events\REST\TEC\V1\Traits\With_Events_ORM;
use TEC\Events\REST\TEC\V1\Traits\With_Transform_Organizers_And_Venues;
use TEC\Common\REST\TEC\V1\Contracts\Tag_Interface as Tag;
use InvalidArgumentException;

/**
 * Archive events endpoint for the TEC REST API V1.
 *
 * @since 6.15.0
 *
 * @package TEC\Events\REST\TEC\V1\Endpoints
 */
class Events extends Post_Entity_Endpoint implements Readable_Endpoint, Creatable_Endpoint {
	use Read_Archive_Response;
	use Create_Entity_Response;
	use With_Events_ORM;
	use With_Transform_Organizers_And_Venues;

	/**
	 * The event validator.
	 *
	 * @since 6.15.0
	 *
	 * @var Event_Validator
	 */
	protected Event_Validator $validator;

	/**
	 * Returns the model class.
	 *
	 * @since 6.15.0
	 *
	 * @return string
	 */
	public function get_model_class(): string {
		return Event_Model::class;
	}

	/**
	 * Returns the base path of the endpoint.
	 *
	 * @since 6.15.0
	 *
	 * @return string
	 */
	public function get_base_path(): string {
		return '/events';
	}

	/**
	 * Returns whether the guest can read the object.
	 *
	 * @since 6.15.0
	 *
	 * @return bool
	 */
	public function guest_can_read(): bool {
		return true;
	}

	/**
	 * Returns the post type of the endpoint.
	 *
	 * @since 6.15.0
	 *
	 * @return string
	 */
	public function get_post_type(): string {
		return Events_Main::POSTTYPE;
	}

	/**
	 * Archive_Events constructor.
	 *
	 * @since 6.15.0
	 *
	 * @param Event_Validator $validator The event validator.
	 */
	public function __construct( Event_Validator $validator ) {
		$this->validator = $validator;
	}

	/**
	 * Returns the schema for the endpoint.
	 *
	 * @since 6.15.0
	 *
	 * @return array
	 */
	public function get_schema(): array {
		return [
			'$schema' => 'http://json-schema.org/draft-04/schema#',
			'title'   => 'events',
			'type'    => 'array',
			'items'   => [
				'$ref' => tribe( OpenApiDocs::class )->get_url() . '#/components/schemas/Event',
			],
		];
	}

	/**
	 * @inheritDoc
	 */
	public function read_schema(): OpenAPI_Schema {
		$schema = new OpenAPI_Schema(
			fn() => __( 'Retrieve Events', 'the-events-calendar' ),
			fn() => __( 'Returns a list of events', 'the-events-calendar' ),
			$this->get_operation_id( 'read' ),
			$this->get_tags(),
			null,
			$this->read_params()
		);

		$headers_collection = new HeadersCollection();

		$headers_collection[] = new Positive_Integer(
			'X-WP-Total',
			fn() => __( 'The total number of events matching the request.', 'the-events-calendar' ),
			null,
			null,
			null,
			true
		);

		$headers_collection[] = new Positive_Integer(
			'X-WP-TotalPages',
			fn() => __( 'The total number of pages for the request.', 'the-events-calendar' ),
			null,
			null,
			null,
			true
		);

		$headers_collection[] = new Array_Of_Type(
			'Link',
			fn() => __(
				'RFC 5988 Link header for pagination. Contains navigation links with relationships:
				`rel="next"` for the next page (if not on last page),
				`rel="prev"` for the previous page (if not on first page).
				Header is omitted entirely if there\'s only one page',
				'the-events-calendar'
			),
			URI::class,
		);

		$response = new Array_Of_Type(
			'Event',
			null,
			Event_Definition::class,
		);

		$schema->add_response(
			200,
			fn() => __( 'Returns the list of events', 'the-events-calendar' ),
			$headers_collection,
			'application/json',
			$response,
		);

		$schema->add_response(
			400,
			fn() => __( 'A required parameter is missing or an input parameter is in the wrong format', 'the-events-calendar' ),
		);

		$schema->add_response(
			404,
			fn() => __( 'The requested page was not found', 'the-events-calendar' ),
		);

		return $schema;
	}

	/**
	 * Returns the arguments for the read request.
	 *
	 * @since 6.15.0
	 *
	 * @return QueryArgumentCollection
	 */
	public function read_params(): QueryArgumentCollection {
		$collection = new QueryArgumentCollection();

		$collection[] = new Positive_Integer(
			'page',
			fn() => __( 'The collection page number.', 'the-events-calendar' ),
			1,
			1
		);

		$collection[] = new Positive_Integer(
			'per_page',
			fn() => __( 'Maximum number of items to be returned in result set.', 'the-events-calendar' ),
			$this->get_default_posts_per_page(),
			1,
			100,
		);

		$collection[] = new Date_Time(
			'start_date',
			fn() => __( 'Limit events to those starting after the specified date.', 'the-events-calendar' ),
		);

		$collection[] = new Date_Time(
			'end_date',
			fn() => __( 'Limit events to those ending before the specified date.', 'the-events-calendar' ),
		);

		$collection[] = new Text(
			'search',
			fn() => __( 'Limit results to those matching a string.', 'the-events-calendar' ),
		);

		$collection[] = new Array_Of_Type(
			'categories',
			fn() => __( 'Limit result set to events assigned specific categories.', 'the-events-calendar' ),
			Positive_Integer::class,
		);

		$collection[] = new Array_Of_Type(
			'tags',
			fn() => __( 'Limit result set to events assigned specific tags.', 'the-events-calendar' ),
			Positive_Integer::class,
		);

		$collection[] = new Array_Of_Type(
			'venue',
			fn() => __( 'Limit result set to events assigned to specific venues.', 'the-events-calendar' ),
			Positive_Integer::class,
			null,
			null,
			fn( $value ) => $this->validator->is_venue_id_list( $value ),
		);

		$collection[] = new Array_Of_Type(
			'organizer',
			fn() => __( 'Limit result set to events assigned to specific organizers.', 'the-events-calendar' ),
			Positive_Integer::class,
			null,
			null,
			fn( $value ) => $this->validator->is_organizer_id_list( $value )
		);

		$collection[] = new Boolean(
			'featured',
			fn() => __( 'Limit result set to featured events only.', 'the-events-calendar' ),
		);

		$collection[] = new Array_Of_Type(
			'status',
			fn() => __( 'Limit result set to events with specific status.', 'the-events-calendar' ),
			Text::class,
			self::ALLOWED_STATUS,
			[ 'publish' ],
			fn( $value ) => $this->validate_status( $value )
		);

		$collection[] = new Positive_Integer(
			'post_parent',
			fn() => __( 'Limit result set to events with specific post parent.', 'the-events-calendar' ),
		);

		$collection[] = new Date_Time(
			'starts_before',
			fn() => __( 'Limit result set to events starting before the specified date.', 'the-events-calendar' ),
		);

		$collection[] = new Date_Time(
			'starts_after',
			fn() => __( 'Limit result set to events starting after the specified date.', 'the-events-calendar' ),
		);

		$collection[] = new Date_Time(
			'ends_before',
			fn() => __( 'Limit result set to events ending before the specified date.', 'the-events-calendar' ),
		);

		$collection[] = new Date_Time(
			'ends_after',
			fn() => __( 'Limit result set to events ending after the specified date.', 'the-events-calendar' ),
		);

		$collection[] = new Boolean(
			'ticketed',
			fn() => __( 'Limit result set to events with tickets.', 'the-events-calendar' ),
		);

		$collection[] = new Text(
			'orderby',
			fn() => __( 'Sort collection by event attribute.', 'the-events-calendar' ),
			'event_date',
			[ 'date', 'event_date', 'title', 'menu_order', 'modified' ],
		);

		$collection[] = new Text(
			'order',
			fn() => __( 'Order sort attribute ascending or descending.', 'the-events-calendar' ),
			'ASC',
			[ 'ASC', 'DESC' ],
		);

		/**
		 * Filters the arguments for the events read request.
		 *
		 * @since 6.15.0
		 *
		 * @param QueryArgumentCollection $collection The collection of arguments.
		 * @param Events                  $this       The events endpoint.
		 */
		return apply_filters( 'tec_events_rest_v1_events_read_params', $collection, $this );
	}

	/**
	 * Returns the arguments for the create request.
	 *
	 * @since 6.15.0
	 * @since 6.15.12 Returning a RequestBodyCollection instead of a QueryArgumentCollection
	 *
	 * @return RequestBodyCollection
	 */
	public function create_params(): RequestBodyCollection {
		$collection = new RequestBodyCollection();

		$definition = new Event_Request_Body_Definition();

		$collection[] = new Definition_Parameter( $definition );

		return $collection
			->set_description_provider( fn() => __( 'The event data to create.', 'the-events-calendar' ) )
			->set_required( true )
			->set_example( $definition->get_example() );
	}

	/**
	 * Returns the OpenAPI schema for creating an event.
	 *
	 * @since 6.15.0
	 *
	 * @return OpenAPI_Schema
	 */
	public function create_schema(): OpenAPI_Schema {
		$schema = new OpenAPI_Schema(
			fn() => __( 'Create an Event', 'the-events-calendar' ),
			fn() => __( 'Creates a new event', 'the-events-calendar' ),
			$this->get_operation_id( 'create' ),
			$this->get_tags(),
			null,
			null,
			$this->create_params(),
			true
		);

		$response = new Definition_Parameter( new Event_Definition() );

		$schema->add_response(
			201,
			fn() => __( 'Event created successfully', 'the-events-calendar' ),
			null,
			'application/json',
			$response,
		);

		$schema->add_response(
			400,
			fn() => __( 'Invalid request data', 'the-events-calendar' ),
		);

		$schema->add_response(
			401,
			fn() => __( 'You are not logged in', 'the-events-calendar' ),
		);

		$schema->add_response(
			403,
			fn() => __( 'You do not have permission to create events', 'the-events-calendar' ),
		);

		$schema->add_response(
			500,
			fn() => __( 'Failed to create the event', 'the-events-calendar' ),
		);


		return $schema;
	}

	/**
	 * Returns the tags for the endpoint.
	 *
	 * @since 6.15.0
	 *
	 * @return Tag[]
	 */
	public function get_tags(): array {
		return [ tribe( TEC_Tag::class ) ];
	}

	/**
	 * Returns the operation ID for the endpoint.
	 *
	 * @since 6.15.0
	 *
	 * @param string $operation The operation to get the operation ID for.
	 *
	 * @return string
	 *
	 * @throws InvalidArgumentException If the operation is invalid.
	 */
	public function get_operation_id( string $operation ): string {
		switch ( $operation ) {
			case 'read':
				return 'getEvents';
			case 'create':
				return 'createEvent';
		}

		throw new InvalidArgumentException( sprintf( 'Invalid operation: %s', $operation ) );
	}
}
