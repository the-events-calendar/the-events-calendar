<?php
/**
 * Single organizer endpoint for the TEC REST API V1.
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

/**
 * Single organizer endpoint for the TEC REST API V1.
 *
 * @since TBD
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
	 * @since TBD
	 *
	 * @return string
	 */
	public function get_base_path(): string {
		return '/organizers/%s';
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
	 * Returns the schema for the endpoint.
	 *
	 * @since TBD
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
	 * @since TBD
	 *
	 * @return QueryArgumentCollection
	 */
	public function read_args(): QueryArgumentCollection {
		return new QueryArgumentCollection();
	}

	/**
	 * Returns the OpenAPI schema for reading an organizer.
	 *
	 * @since TBD
	 *
	 * @return OpenAPI_Schema
	 */
	public function read_schema(): OpenAPI_Schema {
		$collection = new PathArgumentCollection();

		$collection[] = new Positive_Integer(
			'id',
			fn() => __( 'Unique identifier for the organizer.', 'the-events-calendar' ),
		);

		$schema = new OpenAPI_Schema(
			fn() => __( 'Retrieve an Organizer', 'the-events-calendar' ),
			fn() => __( 'Returns a single organizer', 'the-events-calendar' ),
			'getOrganizer',
			[ tribe( TEC_Tag::class ) ],
			$collection
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
	 * @since TBD
	 *
	 * @return QueryArgumentCollection
	 */
	public function update_args(): QueryArgumentCollection {
		return new QueryArgumentCollection();
	}

	/**
	 * Returns the OpenAPI schema for updating an organizer.
	 *
	 * @since TBD
	 *
	 * @return OpenAPI_Schema
	 */
	public function update_schema(): OpenAPI_Schema {
		$path_collection = new PathArgumentCollection();

		$path_collection[] = new Positive_Integer(
			'id',
			fn() => __( 'Unique identifier for the organizer.', 'the-events-calendar' ),
		);

		$collection = new RequestBodyCollection();

		$definition = new Organizer_Request_Body_Definition();

		$collection->set_example( $definition->get_example() );

		$collection[] = new Definition_Parameter( $definition );

		$schema = new OpenAPI_Schema(
			fn() => __( 'Update an Organizer', 'the-events-calendar' ),
			fn() => __( 'Updates an existing organizer', 'the-events-calendar' ),
			'updateOrganizer',
			[ tribe( TEC_Tag::class ) ],
			$path_collection,
			null,
			$collection->set_description_provider( fn() => __( 'The organizer data to update.', 'the-events-calendar' ) )->set_required( true ),
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
	 * Returns the OpenAPI schema for deleting an organizer.
	 *
	 * @since TBD
	 *
	 * @return OpenAPI_Schema
	 */
	public function delete_schema(): OpenAPI_Schema {
		$collection = new PathArgumentCollection();

		$collection[] = new Positive_Integer(
			'id',
			fn() => __( 'Unique identifier for the organizer.', 'the-events-calendar' ),
		);

		$schema = new OpenAPI_Schema(
			fn() => __( 'Delete an Organizer', 'the-events-calendar' ),
			fn() => __( 'Deletes an existing organizer', 'the-events-calendar' ),
			'deleteOrganizer',
			[ tribe( TEC_Tag::class ) ],
			$collection,
			null,
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

		return $schema;
	}
}
