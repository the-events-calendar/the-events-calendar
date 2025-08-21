<?php
/**
 * Trait to handle the transformation of organizers and venues.
 *
 * @since 6.15.0
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
 * @since 6.15.0
 *
 * @package TEC\Events\REST\TEC\V1\Traits
 */
trait With_Transform_Organizers_And_Venues {
	/**
	 * Transforms the entity.
	 *
	 * @since 6.15.0
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

		return parent::transform_entity( $entity );
	}
}
