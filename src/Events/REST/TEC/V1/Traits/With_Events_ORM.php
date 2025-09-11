<?php
/**
 * Trait to handle the ORM for events.
 *
 * @since 6.15.0
 *
 * @package TEC\Events\REST\TEC\V1\Traits
 */

declare( strict_types=1 );

namespace TEC\Events\REST\TEC\V1\Traits;

use TEC\Common\Contracts\Repository_Interface;

/**
 * Trait to handle the ORM for events.
 *
 * @since 6.15.0
 *
 * @package TEC\Events\REST\TEC\V1\Traits
 */
trait With_Events_ORM {
	/**
	 * Returns the ORM for the endpoint.
	 *
	 * @since 6.15.0
	 *
	 * @return Repository_Interface
	 */
	public function get_orm(): Repository_Interface {
		return tribe_events();
	}
}
