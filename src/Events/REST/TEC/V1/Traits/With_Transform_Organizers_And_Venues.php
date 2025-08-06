<?php
/**
 * Trait to handle the transformation of organizers and venues.
 *
 * @since TBD
 *
 * @package TEC\Events\REST\TEC\V1\Traits
 */

declare( strict_types=1 );

namespace TEC\Events\REST\TEC\V1\Traits;

use WP_Post;
use TEC\Events\REST\TEC\V1\Endpoints\Organizers;
use TEC\Events\REST\TEC\V1\Endpoints\Venues;

/**
 * Trait to handle the transformation of organizers and venues.
 *
 * @since TBD
 *
 * @package TEC\Events\REST\TEC\V1\Traits
 */
trait With_Transform_Organizers_And_Venues {
	/**
	 * Transforms the input parameters before processing.
	 *
	 * @since TBD
	 *
	 * @param array $params The input parameters to transform.
	 *
	 * @return array
	 */
	protected function transform_input_params( array $params ): array {
		// Transform venues array to use first element (TEC default behavior).
		if ( isset( $params['venues'] ) && is_array( $params['venues'] ) ) {
			// Take the first venue from the array for TEC compatibility.
			if ( ! empty( $params['venues'] ) ) {
				$params['venue'] = $params['venues'][0];
			}
			// Remove the venues array to avoid conflicts.
			unset( $params['venues'] );
		}

		return $params;
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
