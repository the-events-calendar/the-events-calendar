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
use WP_REST_Request;
use WP_REST_Response;
use Tribe\Events\Models\Post_Types\Venue as Venue_Model;
use TEC\Events\REST\TEC\V1\Tags\TEC_Tag;
use TEC\Common\REST\TEC\V1\Collections\Collection;
use TEC\Common\REST\TEC\V1\Collections\PathArgumentCollection;
use TEC\Common\REST\TEC\V1\Collections\RequestBodyCollection;
use TEC\Common\REST\TEC\V1\Parameter_Types\Boolean;
use TEC\Common\REST\TEC\V1\Parameter_Types\Positive_Integer;
use TEC\Common\REST\TEC\V1\Parameter_Types\Text;
use TEC\Common\REST\TEC\V1\Parameter_Types\URI;
use TEC\Events\REST\TEC\V1\Documentation\Venue_Definition;
use TEC\Common\REST\TEC\V1\Documentation\OpenAPI_Schema;
use TEC\Common\REST\TEC\V1\Endpoints\OpenApiDocs;
use TEC\Common\REST\TEC\V1\Parameter_Types\Definition_Parameter;
use TEC\Events\REST\TEC\V1\Traits\With_Venues_ORM;

/**
 * Single venue endpoint for the TEC REST API V1.
 *
 * @since TBD
 *
 * @package TEC\Events\REST\TEC\V1\Endpoints
 */
class Venue extends Post_Entity_Endpoint implements RUD_Endpoint {
	use With_Venues_ORM;

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
	 * Reads a single venue.
	 *
	 * @since TBD
	 *
	 * @param WP_REST_Request $request The request object.
	 *
	 * @return WP_REST_Response The response object.
	 */
	public function read( WP_REST_Request $request ): WP_REST_Response {
		$venue_id = (int) $request['id'];
		$venue    = get_post( $venue_id );

		if ( ! ( $venue && $venue->post_type === $this->get_post_type() ) ) {
			return new WP_REST_Response(
				[
					'error' => __( 'Venue not found.', 'the-events-calendar' ),
				],
				404
			);
		}

		return new WP_REST_Response( tribe( Venues::class )->get_formatted_entity( $venue ), 200 );
	}

	/**
	 * Returns the arguments for the read request.
	 *
	 * @since TBD
	 *
	 * @return Collection
	 */
	public function read_args(): Collection {
		$collection = new PathArgumentCollection();

		$collection[] = new Positive_Integer(
			'id',
			fn() => __( 'Unique identifier for the venue.', 'the-events-calendar' ),
			null,
			null,
			null,
			true,
			null,
			null,
			null,
			Positive_Integer::LOCATION_PATH
		);

		return $collection;
	}

	/**
	 * Returns the OpenAPI schema for reading a venue.
	 *
	 * @since TBD
	 *
	 * @return OpenAPI_Schema
	 */
	public function read_schema(): OpenAPI_Schema {
		$schema = new OpenAPI_Schema(
			fn() => __( 'Retrieve a Venue', 'the-events-calendar' ),
			fn() => __( 'Returns a single venue', 'the-events-calendar' ),
			'getVenue',
			[ tribe( TEC_Tag::class ) ],
			$this->read_args()
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
			401,
			fn() => __( 'You do not have permission to view this venue', 'the-events-calendar' ),
		);

		$schema->add_response(
			404,
			fn() => __( 'The requested venue was not found', 'the-events-calendar' ),
		);

		return $schema;
	}

	/**
	 * Updates an existing venue.
	 *
	 * @since TBD
	 *
	 * @param WP_REST_Request $request The request object.
	 *
	 * @return WP_REST_Response The response object.
	 */
	public function update( WP_REST_Request $request ): WP_REST_Response {
		$venue_id = (int) $request['id'];
		$venue    = tribe_venues()->where( 'id', $venue_id )->first();

		if ( ! $venue ) {
			return new WP_REST_Response(
				[
					'error' => __( 'Venue not found.', 'the-events-calendar' ),
				],
				404
			);
		}

		// Build update args
		$update_args = [];

		if ( isset( $request['name'] ) ) {
			$update_args['title'] = $request['name'];
		}

		if ( isset( $request['description'] ) ) {
			$update_args['content'] = $request['description'];
		}

		if ( isset( $request['address'] ) ) {
			$update_args['address'] = $request['address'];
		}

		if ( isset( $request['city'] ) ) {
			$update_args['city'] = $request['city'];
		}

		if ( isset( $request['state'] ) || isset( $request['province'] ) ) {
			$update_args['state'] = $request['state'] ?? $request['province'];
		}

		if ( isset( $request['zip'] ) ) {
			$update_args['zip'] = $request['zip'];
		}

		if ( isset( $request['country'] ) ) {
			$update_args['country'] = $request['country'];
		}

		if ( isset( $request['phone'] ) ) {
			$update_args['phone'] = $request['phone'];
		}

		if ( isset( $request['website'] ) ) {
			$update_args['website'] = $request['website'];
		}

		if ( isset( $request['status'] ) ) {
			$update_args['post_status'] = $request['status'];
		}

		if ( isset( $request['show_map'] ) ) {
			$update_args['show_map'] = $request['show_map'];
		}

		if ( isset( $request['show_map_link'] ) ) {
			$update_args['show_map_link'] = $request['show_map_link'];
		}

		// Update using ORM
		if ( ! empty( $update_args ) ) {
			$result = tribe_venues()
				->where( 'id', $venue_id )
				->set_args( $update_args )
				->save();

			if ( ! $result ) {
				return new WP_REST_Response(
					[
						'error' => __( 'Failed to update venue.', 'the-events-calendar' ),
					],
					500
				);
			}
		}

		return new WP_REST_Response( tribe( Venues::class )->get_formatted_entity( get_post( $venue_id ) ), 200 );
	}

	/**
	 * Returns the arguments for the update request.
	 *
	 * @since TBD
	 *
	 * @return Collection
	 */
	public function update_args(): Collection {
		$collection = new RequestBodyCollection();

		$collection[] = new Positive_Integer(
			'id',
			fn() => __( 'Unique identifier for the venue.', 'the-events-calendar' ),
			null,
			null,
			null,
			true,
			null,
			null,
			null,
			Positive_Integer::LOCATION_PATH
		);

		$collection[] = new Text(
			'name',
			fn() => __( 'The name of the venue.', 'the-events-calendar' ),
		);

		$collection[] = new Text(
			'description',
			fn() => __( 'The description of the venue.', 'the-events-calendar' ),
		);

		$collection[] = new Text(
			'address',
			fn() => __( 'The street address of the venue.', 'the-events-calendar' ),
		);

		$collection[] = new Text(
			'city',
			fn() => __( 'The city of the venue.', 'the-events-calendar' ),
		);

		$collection[] = new Text(
			'state',
			fn() => __( 'The state or province of the venue.', 'the-events-calendar' ),
		);

		$collection[] = new Text(
			'province',
			fn() => __( 'The province (alias for state).', 'the-events-calendar' ),
		);

		$collection[] = new Text(
			'zip',
			fn() => __( 'The zip/postal code of the venue.', 'the-events-calendar' ),
		);

		$collection[] = new Text(
			'country',
			fn() => __( 'The country of the venue.', 'the-events-calendar' ),
		);

		$collection[] = new Text(
			'phone',
			fn() => __( 'The phone number of the venue.', 'the-events-calendar' ),
		);

		$collection[] = new URI(
			'website',
			fn() => __( 'The website URL of the venue.', 'the-events-calendar' ),
		);

		$collection[] = new Text(
			'status',
			fn() => __( 'The status of the venue.', 'the-events-calendar' ),
			'publish',
			self::ALLOWED_STATUS,
		);

		$collection[] = new Boolean(
			'show_map',
			fn() => __( 'Whether to show the map for this venue.', 'the-events-calendar' ),
		);

		$collection[] = new Boolean(
			'show_map_link',
			fn() => __( 'Whether to show the map link for this venue.', 'the-events-calendar' ),
		);

		return $collection;
	}

	/**
	 * Returns the OpenAPI schema for updating a venue.
	 *
	 * @since TBD
	 *
	 * @return OpenAPI_Schema
	 */
	public function update_schema(): OpenAPI_Schema {
		$schema = new OpenAPI_Schema(
			fn() => __( 'Update a Venue', 'the-events-calendar' ),
			fn() => __( 'Updates an existing venue', 'the-events-calendar' ),
			'updateVenue',
			[ tribe( TEC_Tag::class ) ],
			$this->update_args()
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
			fn() => __( 'You do not have permission to update this venue', 'the-events-calendar' ),
		);

		$schema->add_response(
			404,
			fn() => __( 'The requested venue was not found', 'the-events-calendar' ),
		);

		return $schema;
	}

	/**
	 * Deletes a venue.
	 *
	 * @since TBD
	 *
	 * @param WP_REST_Request $request The request object.
	 *
	 * @return WP_REST_Response The response object.
	 */
	public function delete( WP_REST_Request $request ): WP_REST_Response {
		$venue_id = (int) $request['id'];
		$venue    = get_post( $venue_id );

		if ( ! $venue || $venue->post_type !== $this->get_post_type() ) {
			return new WP_REST_Response(
				[
					'error' => __( 'Venue not found.', 'the-events-calendar' ),
				],
				404
			);
		}

		$result = wp_delete_post( $venue_id );

		if ( ! $result ) {
			return new WP_REST_Response(
				[
					'error' => __( 'The venue could not be deleted.', 'the-events-calendar' ),
				],
				500
			);
		}

		return new WP_REST_Response(
			[
				'message' => __( 'Venue deleted successfully.', 'the-events-calendar' ),
				'id'      => $venue_id,
			],
			200
		);
	}

	/**
	 * Returns the arguments for the delete request.
	 *
	 * @since TBD
	 *
	 * @return Collection
	 */
	public function delete_args(): Collection {
		$collection = new PathArgumentCollection();

		$collection[] = new Positive_Integer(
			'id',
			fn() => __( 'Unique identifier for the venue.', 'the-events-calendar' ),
			null,
			null,
			null,
			true,
			null,
			null,
			null,
			Positive_Integer::LOCATION_PATH
		);

		return $collection;
	}

	/**
	 * Returns the OpenAPI schema for deleting a venue.
	 *
	 * @since TBD
	 *
	 * @return OpenAPI_Schema
	 */
	public function delete_schema(): OpenAPI_Schema {
		$schema = new OpenAPI_Schema(
			fn() => __( 'Delete a Venue', 'the-events-calendar' ),
			fn() => __( 'Deletes an existing venue', 'the-events-calendar' ),
			'deleteVenue',
			[ tribe( TEC_Tag::class ) ],
			$this->delete_args()
		);

		$schema->add_response(
			200,
			fn() => __( 'Venue deleted successfully', 'the-events-calendar' ),
		);

		$schema->add_response(
			401,
			fn() => __( 'You do not have permission to delete this venue', 'the-events-calendar' ),
		);

		$schema->add_response(
			404,
			fn() => __( 'The requested venue was not found', 'the-events-calendar' ),
		);

		return $schema;
	}
}
