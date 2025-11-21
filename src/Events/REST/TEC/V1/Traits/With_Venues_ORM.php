<?php
/**
 * Trait to handle the ORM for venues.
 *
 * @since 6.15.0
 *
 * @package TEC\Events\REST\TEC\V1\Traits
 */

declare( strict_types=1 );

namespace TEC\Events\REST\TEC\V1\Traits;

use TEC\Common\Contracts\Repository_Interface;

/**
 * Trait to handle the ORM for venues.
 *
 * @since 6.15.0
 *
 * @package TEC\Events\REST\TEC\V1\Traits
 */
trait With_Venues_ORM {
	/**
	 * Returns the ORM for the endpoint.
	 *
	 * @since 6.15.0
	 * @since 6.15.12 Updated the return type to Repository_Interface.
	 *
	 * @return Repository_Interface
	 */
	public function get_orm(): Repository_Interface {
		return tribe_venues();
	}
}
