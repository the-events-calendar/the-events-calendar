<?php
/**
 * Single venue endpoint for the TEC REST API V1.
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
use Tribe\Events\Models\Post_Types\Venue as Venue_Model;
use TEC\Events\REST\TEC\V1\Tags\TEC_Tag;
use TEC\Common\REST\TEC\V1\Collections\QueryArgumentCollection;
use TEC\Common\REST\TEC\V1\Collections\PathArgumentCollection;
use TEC\Common\REST\TEC\V1\Collections\RequestBodyCollection;
use TEC\Common\REST\TEC\V1\Parameter_Types\Positive_Integer;
use TEC\Events\REST\TEC\V1\Documentation\Venue_Definition;
use TEC\Events\REST\TEC\V1\Documentation\Venue_Request_Body_Definition;
use TEC\Common\REST\TEC\V1\Documentation\OpenAPI_Schema;
use TEC\Common\REST\TEC\V1\Endpoints\OpenApiDocs;
use TEC\Common\REST\TEC\V1\Parameter_Types\Definition_Parameter;
use TEC\Events\REST\TEC\V1\Traits\With_Venues_ORM;
use TEC\Common\REST\TEC\V1\Traits\Update_Entity_Response;
use TEC\Common\REST\TEC\V1\Traits\Delete_Entity_Response;
use TEC\Common\REST\TEC\V1\Traits\Read_Entity_Response;
use TEC\Common\REST\TEC\V1\Contracts\Tag_Interface as Tag;
use InvalidArgumentException;

/**
 * Single venue endpoint for the TEC REST API V1.
 *
 * @since TBD
 *
 * @package TEC\Events\REST\TEC\V1\Endpoints
 */
class Venue extends Post_Entity_Endpoint implements RUD_Endpoint {
	use With_Venues_ORM;
	use Read_Entity_Response;
	use Update_Entity_Response;
	use Delete_Entity_Response;

	/**
	 * Returns the base path of the endpoint.
	 *
	 * @since TBD
	 *
	 * @return string
	 */
	public function get_base_path(): string {
		return '/venues/%s';
	}

	/**
	 * Returns the path parameters of the endpoint.
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
		return Venue_Model::class;
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
		return Events_Main::VENUE_POST_TYPE;
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
			'title'   => 'venue',
			'type'    => 'object',
			'$ref'    => tribe( OpenApiDocs::class )->get_url() . '#/components/schemas/Venue',
		];
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
	 * Returns the OpenAPI schema for reading a venue.
	 *
	 * @since TBD
	 *
	 * @return OpenAPI_Schema
	 */
	public function read_schema(): OpenAPI_Schema {
		$collection = new PathArgumentCollection();

		$collection[] = new Positive_Integer(
			'id',
			fn() => __( 'Unique identifier for the venue.', 'the-events-calendar' ),
		);

		$schema = new OpenAPI_Schema(
			fn() => __( 'Retrieve a Venue', 'the-events-calendar' ),
			fn() => __( 'Returns a single venue', 'the-events-calendar' ),
			$this->get_operation_id( 'read' ),
			$this->get_tags(),
			$collection
		);

		$response = new Definition_Parameter( new Venue_Definition() );

		$schema->add_response(
			200,
			fn() => __( 'Returns the venue', 'the-events-calendar' ),
			null,
			'application/json',
			$response,
		);

		$schema->add_response(
			404,
			fn() => __( 'The requested venue was not found', 'the-events-calendar' ),
		);

		return $schema;
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
	 * Returns the OpenAPI schema for updating a venue.
	 *
	 * @since TBD
	 *
	 * @return OpenAPI_Schema
	 */
	public function update_schema(): OpenAPI_Schema {
		$path_collection = new PathArgumentCollection();

		$path_collection[] = new Positive_Integer(
			'id',
			fn() => __( 'Unique identifier for the venue.', 'the-events-calendar' ),
		);

		$collection = new RequestBodyCollection();

		$definition = new Venue_Request_Body_Definition();

		$collection->set_example( $definition->get_example() );

		$collection[] = new Definition_Parameter( $definition );

		$schema = new OpenAPI_Schema(
			fn() => __( 'Update a Venue', 'the-events-calendar' ),
			fn() => __( 'Updates an existing venue', 'the-events-calendar' ),
			$this->get_operation_id( 'update' ),
			$this->get_tags(),
			$path_collection,
			null,
			$collection->set_description_provider( fn() => __( 'The venue data to update.', 'the-events-calendar' ) )->set_required( true ),
			true
		);

		$response = new Definition_Parameter( new Venue_Definition() );

		$schema->add_response(
			200,
			fn() => __( 'Venue updated successfully', 'the-events-calendar' ),
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
			fn() => __( 'You do not have permission to update this venue', 'the-events-calendar' ),
		);

		$schema->add_response(
			404,
			fn() => __( 'The requested venue was not found', 'the-events-calendar' ),
		);

		return $schema;
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
	 * Returns the OpenAPI schema for deleting a venue.
	 *
	 * @since TBD
	 *
	 * @return OpenAPI_Schema
	 */
	public function delete_schema(): OpenAPI_Schema {
		$collection = new PathArgumentCollection();

		$collection[] = new Positive_Integer(
			'id',
			fn() => __( 'Unique identifier for the venue.', 'the-events-calendar' ),
		);

		$schema = new OpenAPI_Schema(
			fn() => __( 'Delete a Venue', 'the-events-calendar' ),
			fn() => __( 'Deletes an existing venue', 'the-events-calendar' ),
			$this->get_operation_id( 'delete' ),
			$this->get_tags(),
			$collection,
			null,
			null,
			true
		);

		$schema->add_response(
			200,
			fn() => __( 'Venue deleted successfully', 'the-events-calendar' ),
		);

		$schema->add_response(
			401,
			fn() => __( 'You are not logged in', 'the-events-calendar' ),
		);

		$schema->add_response(
			403,
			fn() => __( 'You do not have permission to delete this venue', 'the-events-calendar' ),
		);

		$schema->add_response(
			404,
			fn() => __( 'The requested venue was not found', 'the-events-calendar' ),
		);

		return $schema;
	}

	/**
	 * Returns the tags for the endpoint.
	 *
	 * @since TBD
	 *
	 * @return Tag[]
	 */
	public function get_tags(): array {
		return [ tribe( TEC_Tag::class ) ];
	}

	/**
	 * Returns the operation ID for the endpoint.
	 *
	 * @since TBD
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
				return 'getVenue';
			case 'update':
				return 'updateVenue';
			case 'delete':
				return 'deleteVenue';
		}

		throw new InvalidArgumentException( sprintf( 'Invalid operation: %s', $operation ) );
	}
}
