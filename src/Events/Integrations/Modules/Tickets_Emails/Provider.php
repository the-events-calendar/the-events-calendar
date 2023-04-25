<?php
namespace TEC\Events\Integrations\Modules\Tickets_Emails;

use TEC\Events\Integrations\Integration_Abstract;
use TEC\Common\Integrations\Traits\Module_Integration;

/**
 * Class Provider
 *
 * @since TBD
 *
 * @package TEC\Events\Integrations\Modules\Tickets_Emails
 */
class Provider extends Integration_Abstract {
	use Module_Integration;

	/**
	 * @inheritDoc
	 */
	public static function get_slug(): string {
		return 'event-tickets-emails';
	}

	/**
	 * @inheritDoc
	 */
	public function load_conditionals(): bool {
		if ( ! function_exists( 'tec_tickets_emails_is_enabled' ) ) {
			return false;
		}

		if ( ! tec_tickets_emails_is_enabled() ) {
			return false;
		}

		return true;
	}

	/**
	 * @inheritDoc
	 */
	protected function load(): void {
		// Register the Service Provider for Hooks.
		$this->register_hooks();

		$this->container->singleton( Emails::class, Emails::class );

		$this->container->singleton( Template::class, Template::class );
	}

	/**
	 * Registers the provider handling all the 1st level filters and actions for this Service Provider.
	 *
	 * @since TBD
	 */
	protected function register_hooks(): void {
		$hooks = new Hooks( $this->container );
		$hooks->register();

		// Allow Hooks to be removed, by having the them registered to the container.
		$this->container->singleton( Hooks::class, $hooks );
	}

}
