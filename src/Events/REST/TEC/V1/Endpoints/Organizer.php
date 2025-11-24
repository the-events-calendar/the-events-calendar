<?php
/**
 * Single organizer endpoint for the TEC REST API V1.
 *
 * @since 6.15.0
 *
 * @package TEC\Events\REST\TEC\V1\Endpoints
 */

declare( strict_types=1 );

namespace TEC\Events\REST\TEC\V1\Endpoints;

use TEC\Common\REST\TEC\V1\Abstracts\Post_Entity_Endpoint;
use TEC\Common\REST\TEC\V1\Contracts\RUD_Endpoint;
use Tribe__Events__Main as Events_Main;
use Tribe\Events\Models\Post_Types\Organizer as Organizer_Model;
use TEC\Events\REST\TEC\V1\Tags\TEC_Tag;
use TEC\Common\REST\TEC\V1\Collections\QueryArgumentCollection;
use TEC\Common\REST\TEC\V1\Collections\PathArgumentCollection;
use TEC\Common\REST\TEC\V1\Collections\RequestBodyCollection;
use TEC\Common\REST\TEC\V1\Parameter_Types\Positive_Integer;
use TEC\Events\REST\TEC\V1\Documentation\Organizer_Definition;
use TEC\Events\REST\TEC\V1\Documentation\Organizer_Request_Body_Definition;
use TEC\Common\REST\TEC\V1\Documentation\OpenAPI_Schema;
use TEC\Common\REST\TEC\V1\Endpoints\OpenApiDocs;
use TEC\Common\REST\TEC\V1\Parameter_Types\Definition_Parameter;
use TEC\Events\REST\TEC\V1\Traits\With_Organizers_ORM;
use TEC\Common\REST\TEC\V1\Traits\Update_Entity_Response;
use TEC\Common\REST\TEC\V1\Traits\Delete_Entity_Response;
use TEC\Common\REST\TEC\V1\Traits\Read_Entity_Response;
use TEC\Common\REST\TEC\V1\Contracts\Tag_Interface as Tag;
use InvalidArgumentException;

/**
 * Single organizer endpoint for the TEC REST API V1.
 *
 * @since 6.15.0
 *
 * @package TEC\Events\REST\TEC\V1\Endpoints
 */
class Organizer extends Post_Entity_Endpoint implements RUD_Endpoint {
	use With_Organizers_ORM;
	use Read_Entity_Response;
	use Update_Entity_Response;
	use Delete_Entity_Response;

	/**
	 * Returns the base path of the endpoint.
	 *
	 * @since 6.15.0
	 *
	 * @return string
	 */
	public function get_base_path(): string {
		return '/organizers/%s';
	}

	/**
	 * Returns the path parameters of the endpoint.
	 *
	 * @since 6.15.0
	 *
	 * @return PathArgumentCollection
	 */
	public function get_path_parameters(): PathArgumentCollection {
		$collection = new PathArgumentCollection();

		$collection[] = new Positive_Integer(
			'id',
			fn() => __( 'Unique identifier for the organizer.', 'the-events-calendar' ),
		);

		return $collection;
	}

	/**
	 * Returns the model class.
	 *
	 * @since 6.15.0
	 *
	 * @return string
	 */
	public function get_model_class(): string {
		return Organizer_Model::class;
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
		return Events_Main::ORGANIZER_POST_TYPE;
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
			'title'   => 'organizer',
			'type'    => 'object',
			'$ref'    => tribe( OpenApiDocs::class )->get_url() . '#/components/schemas/Organizer',
		];
	}

	/**
	 * Returns the arguments for the read request.
	 *
	 * @since 6.15.0
	 *
	 * @return QueryArgumentCollection
	 */
	public function read_params(): QueryArgumentCollection {
		return new QueryArgumentCollection();
	}

	/**
	 * Returns the OpenAPI schema for reading an organizer.
	 *
	 * @since 6.15.0
	 *
	 * @return OpenAPI_Schema
	 */
	public function read_schema(): OpenAPI_Schema {
		$schema = new OpenAPI_Schema(
			fn() => __( 'Retrieve an Organizer', 'the-events-calendar' ),
			fn() => __( 'Returns a single organizer', 'the-events-calendar' ),
			$this->get_operation_id( 'read' ),
			$this->get_tags(),
			$this->get_path_parameters(),
		);

		$response = new Definition_Parameter( new Organizer_Definition() );

		$schema->add_response(
			200,
			fn() => __( 'Returns the organizer', 'the-events-calendar' ),
			null,
			'application/json',
			$response,
		);

		$schema->add_response(
			404,
			fn() => __( 'The requested organizer was not found', 'the-events-calendar' ),
		);

		return $schema;
	}

	/**
	 * Returns the arguments for the update request.
	 *
	 * @since 6.15.0
	 * @since 6.15.12 Returning a RequestBodyCollection instead of a QueryArgumentCollection
	 *
	 * @return RequestBodyCollection
	 */
	public function update_params(): RequestBodyCollection {
		$collection = new RequestBodyCollection();

		$definition = new Organizer_Request_Body_Definition();

		$collection->set_example( $definition->get_example() );

		$collection[] = new Definition_Parameter( $definition );

		return $collection->set_description_provider( fn() => __( 'The organizer data to update.', 'the-events-calendar' ) )->set_required( true );
	}

	/**
	 * Returns the OpenAPI schema for updating an organizer.
	 *
	 * @since 6.15.0
	 *
	 * @return OpenAPI_Schema
	 */
	public function update_schema(): OpenAPI_Schema {

		$schema = new OpenAPI_Schema(
			fn() => __( 'Update an Organizer', 'the-events-calendar' ),
			fn() => __( 'Updates an existing organizer', 'the-events-calendar' ),
			$this->get_operation_id( 'update' ),
			$this->get_tags(),
			$this->get_path_parameters(),
			null,
			$this->update_params(),
			true
		);

		$response = new Definition_Parameter( new Organizer_Definition() );

		$schema->add_response(
			200,
			fn() => __( 'Organizer updated successfully', 'the-events-calendar' ),
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
			fn() => __( 'You do not have permission to update this organizer', 'the-events-calendar' ),
		);

		$schema->add_response(
			404,
			fn() => __( 'The requested organizer was not found', 'the-events-calendar' ),
		);

		$schema->add_response(
			500,
			fn() => __( 'Failed to update the organizer', 'the-events-calendar' ),
		);

		return $schema;
	}

	/**
	 * Returns the OpenAPI schema for deleting an organizer.
	 *
	 * @since 6.15.0
	 *
	 * @return OpenAPI_Schema
	 */
	public function delete_schema(): OpenAPI_Schema {
		$schema = new OpenAPI_Schema(
			fn() => __( 'Delete an Organizer', 'the-events-calendar' ),
			fn() => __( 'Deletes an existing organizer', 'the-events-calendar' ),
			$this->get_operation_id( 'delete' ),
			$this->get_tags(),
			$this->get_path_parameters(),
			$this->delete_params(),
			null,
			true
		);

		$schema->add_response(
			200,
			fn() => __( 'Organizer deleted successfully', 'the-events-calendar' ),
		);

		$schema->add_response(
			401,
			fn() => __( 'You are not logged in', 'the-events-calendar' ),
		);

		$schema->add_response(
			403,
			fn() => __( 'You do not have permission to delete this organizer', 'the-events-calendar' ),
		);

		$schema->add_response(
			404,
			fn() => __( 'The requested organizer was not found', 'the-events-calendar' ),
		);

		$schema->add_response(
			410,
			fn() => __( 'The organizer has already been trashed', 'the-events-calendar' ),
		);

		$schema->add_response(
			500,
			fn() => __( 'Failed to delete the organizer', 'the-events-calendar' ),
		);

		$schema->add_response(
			501,
			fn() => __( 'The organizer does not support trashing. Set force=true to delete', 'the-events-calendar' ),
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
				return 'getOrganizer';
			case 'update':
				return 'updateOrganizer';
			case 'delete':
				return 'deleteOrganizer';
		}

		throw new InvalidArgumentException( sprintf( 'Invalid operation: %s', $operation ) );
	}
}
