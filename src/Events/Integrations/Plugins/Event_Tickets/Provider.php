<?php

namespace TEC\Events\Integrations\Plugins\Event_Tickets;

use TEC\Events\Integrations\Integration_Abstract;
use TEC\Events\Integrations\Plugins\Plugin_Integration;

/**
 * Class Provider
 *
 * @since   TBD
 *
 * @package TEC\Events\Integrations\Plugins\Event_Tickets
 */
class Provider extends Integration_Abstract {
	use Plugin_Integration;

	/**
	 * @inheritDoc
	 */
	public static function get_slug(): string {
		return 'event-tickets';
	}

	/**
	 * @inheritDoc
	 */
	public function load_conditionals(): bool {
		return function_exists( 'tribe_tickets' );
	}

	/**
	 * @inheritDoc
	 */
	protected function load(): void {
		$this->load_tickets_emails_integration();
	}

	/**
	 * Loads the Tickets Emails integration.
	 *
	 * @since TBD
	 */
	public function load_tickets_emails_integration() {
		if ( ! tec_tickets_emails_is_enabled() ) {
			return;
		}

		// Loads Tickets Emails.
		$this->container->register( Emails\Provider::class );
	}
}
