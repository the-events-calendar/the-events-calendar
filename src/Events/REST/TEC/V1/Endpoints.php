<?php
/**
 * Endpoints Controller class.
 *
 * @since 6.15.0
 *
 * @package TEC\Events\REST\TEC\V1
 */

declare( strict_types=1 );

namespace TEC\Events\REST\TEC\V1;

use TEC\Common\REST\TEC\V1\Contracts\Definition_Interface;
use TEC\Common\REST\TEC\V1\Contracts\Endpoint_Interface;
use TEC\Common\REST\TEC\V1\Contracts\Tag_Interface;
use TEC\Events\REST\TEC\V1\Documentation\Event_Definition;
use TEC\Events\REST\TEC\V1\Documentation\Organizer_Definition;
use TEC\Events\REST\TEC\V1\Documentation\Venue_Definition;
use TEC\Events\REST\TEC\V1\Documentation\Event_Request_Body_Definition;
use TEC\Events\REST\TEC\V1\Documentation\Organizer_Request_Body_Definition;
use TEC\Events\REST\TEC\V1\Documentation\Venue_Request_Body_Definition;
use TEC\Events\REST\TEC\V1\Endpoints\Events;
use TEC\Events\REST\TEC\V1\Endpoints\Event;
use TEC\Events\REST\TEC\V1\Endpoints\Organizers;
use TEC\Events\REST\TEC\V1\Endpoints\Organizer;
use TEC\Events\REST\TEC\V1\Endpoints\Venues;
use TEC\Events\REST\TEC\V1\Endpoints\Venue;
use TEC\Common\REST\TEC\V1\Abstracts\Endpoints_Controller;
use TEC\Events\REST\TEC\V1\Tags\TEC_Tag;

/**
 * Endpoints Controller class.
 *
 * @since 6.15.0
 *
 * @package TEC\Events\REST\TEC\V1
 */
class Endpoints extends Endpoints_Controller {
	/**
	 * Returns the endpoints to register.
	 *
	 * @since 6.15.0
	 *
	 * @return Endpoint_Interface[]
	 */
	public function get_endpoints(): array {
		return [
			Events::class,
			Event::class,
			Organizers::class,
			Organizer::class,
			Venues::class,
			Venue::class,
		];
	}

	/**
	 * Returns the tags to register.
	 *
	 * @since 6.15.0
	 *
	 * @return Tag_Interface[]
	 */
	public function get_tags(): array {
		return [
			TEC_Tag::class,
		];
	}

	/**
	 * Returns the definitions to register.
	 *
	 * @since 6.15.0
	 *
	 * @return Definition_Interface[]
	 */
	public function get_definitions(): array {
		return [
			Event_Definition::class,
			Organizer_Definition::class,
			Venue_Definition::class,
			Event_Request_Body_Definition::class,
			Organizer_Request_Body_Definition::class,
			Venue_Request_Body_Definition::class,
		];
	}
}
