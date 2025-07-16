<?php
/**
 * Endpoints Controller class.
 *
 * @since TBD
 *
 * @package TEC\Events\REST\TEC\V1
 */

declare( strict_types=1 );

namespace TEC\Events\REST\TEC\V1;

use TEC\Common\REST\TEC\V1\Contracts\Definition_Interface;
use TEC\Common\REST\TEC\V1\Contracts\Endpoint_Interface;
use TEC\Events\REST\TEC\V1\Documentation\Event_Definition;
use TEC\Events\REST\TEC\V1\Documentation\Organizer_Definition;
use TEC\Events\REST\TEC\V1\Documentation\Venue_Definition;
use TEC\Events\REST\TEC\V1\Endpoints\Events;
use TEC\Common\REST\TEC\V1\Abstracts\Endpoints_Controller;

/**
 * Endpoints Controller class.
 *
 * @since TBD
 *
 * @package TEC\Events\REST\TEC\V1
 */
class Endpoints extends Endpoints_Controller {
	/**
	 * Returns the endpoints to register.
	 *
	 * @since TBD
	 *
	 * @return Endpoint_Interface[]
	 */
	public function get_endpoints(): array {
		return [
			Events::class,
		];
	}

	/**
	 * Returns the definitions to register.
	 *
	 * @since TBD
	 *
	 * @return Definition_Interface[]
	 */
	public function get_definitions(): array {
		return [
			Event_Definition::class,
			Organizer_Definition::class,
			Venue_Definition::class,
		];
	}
}
