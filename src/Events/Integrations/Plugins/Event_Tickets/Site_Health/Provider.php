<?php
/**
 * Class that handles interfacing with Site Health.
 *
 * @since   TBD
 *
 * @package TEC\Events\Integrations\Plugins\Event_Tickets
 */

namespace TEC\Events\Integrations\Plugins\Event_Tickets\Site_Health;

use TEC\Events\Integrations\Integration_Abstract;
use TEC\Common\Integrations\Traits\Module_Integration;

/**
 * Class Provider
 *
 * @since 6.1.1
 *
 * @package TEC\Events\Integrations\Plugins\Event_Tickets\Site_Health
 */
class Provider extends Integration_Abstract {
	use Module_Integration;

	/**
	 * @inheritDoc
	 */
	public static function get_slug(): string {
		return 'event-tickets-site-health';
	}

	/**
	 * @inheritDoc
	 */
	public function load_conditionals(): bool {
		return true;
	}

	/**
	 * @inheritDoc
	 */
	protected function load(): void {
		// Register the Service Provider for Hooks.
		$this->register_hooks();
		$this->container->singleton( The_Events_Calendar_Subsection::class, The_Events_Calendar_Subsection::class );
	}

	/**
	 * Registers the provider handling all the 1st level filters and actions for this Service Provider.
	 *
	 * @since 6.1.1
	 */
	protected function register_hooks(): void {
		$hooks = new Hooks( $this->container );
		$hooks->register();

		// Allow Hooks to be removed, by having the them registered to the container.
		$this->container->singleton( Hooks::class, $hooks );
	}

}
