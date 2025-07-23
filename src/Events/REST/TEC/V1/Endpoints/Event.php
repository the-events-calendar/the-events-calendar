<?php
/**
 * Single event endpoint for the TEC REST API V1.
 *
 * @since TBD
 *
 * @package TEC\Events\REST\TEC\V1\Endpoints
 */

declare( strict_types=1 );

namespace TEC\Events\REST\TEC\V1\Endpoints;

use TEC\Common\REST\TEC\V1\Abstracts\Post_Entity_Endpoint;
use TEC\Common\REST\TEC\V1\Contracts\RUD_Endpoint;
use Tribe__Events__Main as Events_Main;
use Tribe__Events__Validator__Base as Event_Validator;
use WP_REST_Request;
use WP_REST_Response;
use Tribe\Events\Models\Post_Types\Event as Event_Model;
use TEC\Events\REST\TEC\V1\Tags\TEC_Tag;
use TEC\Common\REST\TEC\V1\Collections\Collection;
use TEC\Common\REST\TEC\V1\Collections\QueryArgumentCollection;
use TEC\Common\REST\TEC\V1\Collections\PathArgumentCollection;
use TEC\Common\REST\TEC\V1\Collections\RequestBodyCollection;
use TEC\Common\REST\TEC\V1\Parameter_Types\Boolean;
use TEC\Common\REST\TEC\V1\Parameter_Types\Positive_Integer;
use TEC\Common\REST\TEC\V1\Parameter_Types\Date_Time;
use TEC\Common\REST\TEC\V1\Parameter_Types\Text;
use TEC\Common\REST\TEC\V1\Parameter_Types\Array_Of_Type;
use TEC\Common\REST\TEC\V1\Parameter_Types\URI;
use TEC\Events\REST\TEC\V1\Documentation\Event_Definition;
use TEC\Common\REST\TEC\V1\Documentation\OpenAPI_Schema;
use TEC\Common\REST\TEC\V1\Endpoints\OpenApiDocs;
use TEC\Common\REST\TEC\V1\Parameter_Types\Definition_Parameter;
use TEC\Events\REST\TEC\V1\Traits\With_Events_ORM;

/**
 * Single event endpoint for the TEC REST API V1.
 *
 * @since TBD
 *
 * @package TEC\Events\REST\TEC\V1\Endpoints
 */
class Event extends Post_Entity_Endpoint implements RUD_Endpoint {
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
	 * Event constructor.
	 *
	 * @since TBD
	 *
	 * @param Event_Validator $validator The event validator.
	 */
	public function __construct( Event_Validator $validator ) {
		$this->validator = $validator;
	}

	/**
	 * Returns the base path for the endpoint.
	 *
	 * @since TBD
	 *
	 * @return string
	 */
	public function get_base_path(): string {
		return '/events/%s';
	}

	/**
	 * Returns the path parameters for the endpoint.
	 *
	 * @since TBD
	 *
	 * @return array
	 */
	public function get_path_parameters(): array {
		return [
			'id' => [
				'type' => 'integer',
			],
		];
	}

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
	 * Returns the schema for the endpoint.
	 *
	 * @since TBD
	 *
	 * @return array
	 */
	public function get_schema(): array {
		return [
			'$schema' => 'http://json-schema.org/draft-04/schema#',
			'title'   => 'event',
			'type'    => 'object',
			'$ref'    => tribe( OpenApiDocs::class )->get_url() . '#/components/schemas/Event',
		];
	}

	/**
	 * Reads a single event.
	 *
	 * @since TBD
	 *
	 * @param WP_REST_Request $request The request object.
	 *
	 * @return WP_REST_Response The response object.
	 */
	public function read( WP_REST_Request $request ): WP_REST_Response {
		$event_id = (int) $request['id'];
		$event    = get_post( $event_id );

		if ( ! ( $event && $event->post_type === $this->get_post_type() ) ) {
			return new WP_REST_Response(
				[
					'error' => __( 'Event not found.', 'the-events-calendar' ),
				],
				404
			);
		}

		return new WP_REST_Response( tribe( Events::class )->get_formatted_entity( $event ), 200 );
	}

	/**
	 * Returns the arguments for the read request.
	 *
	 * @since TBD
	 *
	 * @return QueryArgumentCollection
	 */
	public function read_args(): QueryArgumentCollection {
		return new QueryArgumentCollection();
	}

	/**
	 * Returns the OpenAPI schema for reading an event.
	 *
	 * @since TBD
	 *
	 * @return OpenAPI_Schema
	 */
	public function read_schema(): OpenAPI_Schema {
		$collection = new PathArgumentCollection();

		$collection[] = new Positive_Integer(
			'id',
			fn() => __( 'Unique identifier for the event.', 'the-events-calendar' ),
		);

		$schema = new OpenAPI_Schema(
			fn() => __( 'Retrieve an Event', 'the-events-calendar' ),
			fn() => __( 'Returns a single event', 'the-events-calendar' ),
			'getEvent',
			[ tribe( TEC_Tag::class ) ],
			$collection
		);

		$response = new Definition_Parameter( new Event_Definition() );

		$schema->add_response(
			200,
			fn() => __( 'Returns the event', 'the-events-calendar' ),
			null,
			'application/json',
			$response,
		);

		$schema->add_response(
			401,
			fn() => __( 'You do not have permission to view this event', 'the-events-calendar' ),
		);

		$schema->add_response(
			404,
			fn() => __( 'The requested event was not found', 'the-events-calendar' ),
		);

		return $schema;
	}

	/**
	 * Updates an existing event.
	 *
	 * @since TBD
	 *
	 * @param WP_REST_Request $request The request object.
	 *
	 * @return WP_REST_Response The response object.
	 */
	public function update( WP_REST_Request $request ): WP_REST_Response {
		$event_id = (int) $request['id'];
		$event    = tribe_events()->where( 'id', $event_id )->first();

		if ( ! $event ) {
			return new WP_REST_Response(
				[
					'error' => __( 'Event not found.', 'the-events-calendar' ),
				],
				404
			);
		}

		$update_args = [];

		if ( isset( $request['title'] ) ) {
			$update_args['title'] = $request['title'];
		}

		if ( isset( $request['description'] ) ) {
			$update_args['content'] = $request['description'];
		}

		if ( isset( $request['excerpt'] ) ) {
			$update_args['excerpt'] = $request['excerpt'];
		}

		if ( isset( $request['start_date'] ) ) {
			$update_args['start_date'] = $request['start_date'];
		}

		if ( isset( $request['end_date'] ) ) {
			$update_args['end_date'] = $request['end_date'];
		}

		if ( isset( $request['all_day'] ) ) {
			$update_args['all_day'] = $request['all_day'];
		}

		if ( isset( $request['timezone'] ) ) {
			$update_args['timezone'] = $request['timezone'];
		}

		if ( isset( $request['venue'] ) ) {
			$update_args['venue'] = $request['venue'];
		}

		if ( isset( $request['organizer'] ) ) {
			$update_args['organizer'] = $request['organizer'];
		}

		if ( isset( $request['featured'] ) ) {
			$update_args['featured'] = $request['featured'];
		}

		if ( isset( $request['status'] ) ) {
			$update_args['post_status'] = $request['status'];
		}

		if ( isset( $request['website'] ) ) {
			$update_args['website'] = $request['website'];
		}

		if ( isset( $request['cost'] ) ) {
			$update_args['cost'] = $request['cost'];
		}

		if ( isset( $request['categories'] ) ) {
			$update_args['categories'] = $request['categories'];
		}

		if ( isset( $request['tags'] ) ) {
			$update_args['tags'] = $request['tags'];
		}

		if ( ! empty( $update_args ) ) {
			$result = tribe_events()
				->where( 'id', $event_id )
				->set_args( $update_args )
				->save();

			if ( ! $result ) {
				return new WP_REST_Response(
					[
						'error' => __( 'Failed to update event.', 'the-events-calendar' ),
					],
					500
				);
			}
		}

		return new WP_REST_Response( tribe( Events::class )->get_formatted_entity( get_post( $event_id ) ), 200 );
	}


	/**
	 * Returns the arguments for the update request.
	 *
	 * @since TBD
	 *
	 * @return QueryArgumentCollection
	 */
	public function update_args(): QueryArgumentCollection {
		return new QueryArgumentCollection();
	}

	/**
	 * Returns the OpenAPI schema for updating an event.
	 *
	 * @since TBD
	 *
	 * @return OpenAPI_Schema
	 */
	public function update_schema(): OpenAPI_Schema {
		$path_collection = new PathArgumentCollection();

		$path_collection[] = new Positive_Integer(
			'id',
			fn() => __( 'Unique identifier for the event.', 'the-events-calendar' ),
		);

		$definition = new Event_Definition();

		$collection = new RequestBodyCollection();

		$collection[] = new Definition_Parameter( $definition );

		$schema = new OpenAPI_Schema(
			fn() => __( 'Update an Event', 'the-events-calendar' ),
			fn() => __( 'Updates an existing event', 'the-events-calendar' ),
			'updateEvent',
			[ tribe( TEC_Tag::class ) ],
			$path_collection,
			null,
			$collection->set_description_provider( fn() => __( 'The event data to update.', 'the-events-calendar' ) )->set_required( true )->set_example( $definition->get_example() )
		);

		$response = new Definition_Parameter( $definition );

		$schema->add_response(
			200,
			fn() => __( 'Event updated successfully', 'the-events-calendar' ),
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
			fn() => __( 'You do not have permission to update this event', 'the-events-calendar' ),
		);

		$schema->add_response(
			404,
			fn() => __( 'The requested event was not found', 'the-events-calendar' ),
		);

		return $schema;
	}

	/**
	 * Deletes an event.
	 *
	 * @since TBD
	 *
	 * @param WP_REST_Request $request The request object.
	 *
	 * @return WP_REST_Response The response object.
	 */
	public function delete( WP_REST_Request $request ): WP_REST_Response {
		$event_id = (int) $request['id'];
		$event    = get_post( $event_id );

		if ( ! $event || $event->post_type !== $this->get_post_type() ) {
			return new WP_REST_Response(
				[
					'error' => __( 'Event not found.', 'the-events-calendar' ),
				],
				404
			);
		}

		$result = wp_delete_post( $event_id );

		if ( ! $result ) {
			return new WP_REST_Response(
				[
					'error' => __( 'The event could not be deleted.', 'the-events-calendar' ),
				],
				500
			);
		}

		return new WP_REST_Response(
			[
				'message' => __( 'Event deleted successfully.', 'the-events-calendar' ),
				'id'      => $event_id,
			],
			200
		);
	}


	/**
	 * Returns the arguments for the delete request.
	 *
	 * @since TBD
	 *
	 * @return QueryArgumentCollection
	 */
	public function delete_args(): QueryArgumentCollection {
		return new QueryArgumentCollection();
	}

	/**
	 * Returns the OpenAPI schema for deleting an event.
	 *
	 * @since TBD
	 *
	 * @return OpenAPI_Schema
	 */
	public function delete_schema(): OpenAPI_Schema {
		$collection = new PathArgumentCollection();

		$collection[] = new Positive_Integer(
			'id',
			fn() => __( 'Unique identifier for the event.', 'the-events-calendar' ),
		);

		$schema = new OpenAPI_Schema(
			fn() => __( 'Delete an Event', 'the-events-calendar' ),
			fn() => __( 'Deletes an existing event', 'the-events-calendar' ),
			'deleteEvent',
			[ tribe( TEC_Tag::class ) ],
			$collection
		);

		$schema->add_response(
			200,
			fn() => __( 'Event deleted successfully', 'the-events-calendar' ),
		);

		$schema->add_response(
			401,
			fn() => __( 'You do not have permission to delete this event', 'the-events-calendar' ),
		);

		$schema->add_response(
			404,
			fn() => __( 'The requested event was not found', 'the-events-calendar' ),
		);

		return $schema;
	}
}
