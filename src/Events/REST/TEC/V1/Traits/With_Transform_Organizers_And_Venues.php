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

use TEC\Events\REST\TEC\V1\Endpoints\Organizers;
use TEC\Events\REST\TEC\V1\Endpoints\Venues;

/**
 * Trait to handle the transformation of organizers and venues.
 *
 * @since 6.15.0
 * @since 6.17.5 Linked posts the current user cannot read are no longer included.
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
			// Only linked posts the current user may read are formatted; the rest are dropped.
			$entity['organizers'] = tribe( Organizers::class )->format_entity_collection( $entity['organizers']->all() );
		}

		if ( ! empty( $entity['venues'] ) ) {
			$entity['venues'] = tribe( Venues::class )->format_entity_collection( $entity['venues']->all() );
		}

		return parent::transform_entity( $entity );
	}
}
