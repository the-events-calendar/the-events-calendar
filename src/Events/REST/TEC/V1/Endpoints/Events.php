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
use TEC\Common\REST\TEC\V1\Contracts\Creatable_Endpoint;
use Tribe__Events__Main as Events_Main;
use Tribe__Events__Validator__Base as Event_Validator;
use WP_REST_Request;
use Tribe\Events\Models\Post_Types\Event as Event_Model;
use TEC\Events\REST\TEC\V1\Tags\TEC_Tag;
use Tribe__Repository__Interface;
use TEC\Common\REST\TEC\V1\Traits\Read_Archive_Response;
use TEC\Common\REST\TEC\V1\Traits\Create_Entity_Response;
use WP_Post;
use TEC\Common\REST\TEC\V1\Parameter_Types\Collection;
use TEC\Common\REST\TEC\V1\Parameter_Types\Boolean;
use TEC\Common\REST\TEC\V1\Parameter_Types\Positive_Integer;
use TEC\Common\REST\TEC\V1\Parameter_Types\Date_Time;
use TEC\Common\REST\TEC\V1\Parameter_Types\Text;
use TEC\Common\REST\TEC\V1\Parameter_Types\Array_Of_Type;
use TEC\Common\REST\TEC\V1\Endpoints\OpenApiDocs;
use TEC\Common\REST\TEC\V1\Parameter_Types\URI;
use TEC\Events\REST\TEC\V1\Documentation\Event_Definition;
use TEC\Common\REST\TEC\V1\Documentation\OpenAPI_Schema;
use TEC\Common\REST\TEC\V1\Parameter_Types\Definition_Parameter;
use TEC\Events\REST\TEC\V1\Traits\With_Events_ORM;

/**
 * Archive events endpoint for the TEC REST API V1.
 *
 * @since TBD
 *
 * @package TEC\Events\REST\TEC\V1\Endpoints
 */
class Events extends Post_Entity_Endpoint implements Readable_Endpoint, Creatable_Endpoint {
	use Read_Archive_Response;
	use Create_Entity_Response;
	use With_Events_ORM;

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
	 * Returns the base path of the endpoint.
	 *
	 * @since TBD
	 *
	 * @return string
	 */
	public function get_base_path(): string {
		return '/events';
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
				'$ref' => tribe( OpenApiDocs::class )->get_url() . '#/components/schemas/Event',
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
	 * @return Tribe__Repository__Interface The events query.
	 */
	protected function build_query( WP_REST_Request $request ): Tribe__Repository__Interface {
		/** @var Tribe__Repository__Interface $events_query */
		$events_query = $this->get_orm();

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
	 * @inheritDoc
	 */
	public function read_schema(): OpenAPI_Schema {
		$schema = new OpenAPI_Schema(
			fn() => __( 'Retrieve Events', 'the-events-calendar' ),
			fn() => __( 'Returns a list of events', 'the-events-calendar' ),
			'getEvents',
			[ tribe( TEC_Tag::class ) ],
			$this->read_args()
		);

		$headers_collection = new Collection();

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
	 * @since TBD
	 *
	 * @return Collection
	 */
	public function read_args(): Collection {
		$collection = new Collection();

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

		return $collection;
	}

	/**
	 * Returns the arguments for the create request.
	 *
	 * @since TBD
	 *
	 * @return Collection
	 */
	public function create_args(): Collection {
		$collection = new Collection();

		$collection[] = new Text(
			'title',
			fn() => __( 'The title of the event.', 'the-events-calendar' ),
			null,
			null,
			null,
			null,
			true
		);

		$collection[] = new Text(
			'description',
			fn() => __( 'The description/content of the event.', 'the-events-calendar' ),
		);

		$collection[] = new Text(
			'excerpt',
			fn() => __( 'The excerpt of the event.', 'the-events-calendar' ),
		);

		$collection[] = new Date_Time(
			'start_date',
			fn() => __( 'The start date and time of the event.', 'the-events-calendar' ),
			null,
			null,
			null,
			null,
			true
		);

		$collection[] = new Date_Time(
			'end_date',
			fn() => __( 'The end date and time of the event.', 'the-events-calendar' ),
			null,
			null,
			null,
			null,
			true
		);

		$collection[] = new Boolean(
			'all_day',
			fn() => __( 'Whether the event is an all-day event.', 'the-events-calendar' ),
			false
		);

		$collection[] = new Text(
			'timezone',
			fn() => __( 'The timezone of the event.', 'the-events-calendar' ),
		);

		$collection[] = new Array_Of_Type(
			'venue',
			fn() => __( 'The venue IDs for the event.', 'the-events-calendar' ),
			Positive_Integer::class,
		);

		$collection[] = new Array_Of_Type(
			'organizer',
			fn() => __( 'The organizer IDs for the event.', 'the-events-calendar' ),
			Positive_Integer::class,
		);

		$collection[] = new Boolean(
			'featured',
			fn() => __( 'Whether the event is featured.', 'the-events-calendar' ),
			false
		);

		$collection[] = new Text(
			'status',
			fn() => __( 'The status of the event.', 'the-events-calendar' ),
			'publish',
			self::ALLOWED_STATUS,
		);

		$collection[] = new Array_Of_Type(
			'categories',
			fn() => __( 'The category IDs for the event.', 'the-events-calendar' ),
			Positive_Integer::class,
		);

		$collection[] = new Array_Of_Type(
			'tags',
			fn() => __( 'The tag IDs for the event.', 'the-events-calendar' ),
			Positive_Integer::class,
		);

		$collection[] = new URI(
			'website',
			fn() => __( 'The event website URL.', 'the-events-calendar' ),
		);

		$collection[] = new Text(
			'cost',
			fn() => __( 'The cost of the event.', 'the-events-calendar' ),
		);

		return $collection;
	}

	/**
	 * Returns the OpenAPI schema for creating an event.
	 *
	 * @since TBD
	 *
	 * @return OpenAPI_Schema
	 */
	public function create_schema(): OpenAPI_Schema {
		$schema = new OpenAPI_Schema(
			fn() => __( 'Create an Event', 'the-events-calendar' ),
			fn() => __( 'Creates a new event', 'the-events-calendar' ),
			'createEvent',
			[ tribe( TEC_Tag::class ) ],
			$this->create_args()
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
			403,
			fn() => __( 'You do not have permission to create events', 'the-events-calendar' ),
		);

		return $schema;
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
