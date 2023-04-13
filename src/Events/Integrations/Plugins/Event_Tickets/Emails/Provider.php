<?php
namespace TEC\Events\Integrations\Plugins\Event_Tickets\Emails;

/**
 * Class Provider
 *
 * @since   TBD
 *
 * @package TEC\Events\Integrations\Plugins\Event_Tickets
 */
class Provider extends \tad_DI52_ServiceProvider {
	/**
	 * Register the provider singletons.
	 *
	 * @since TBD
	 */
	public function register() {
		// Register the Service Provider for Hooks.
		$this->register_hooks();

		// Register the service provider itself on the container.
		$this->container->singleton( static::class, $this );

		$emails = new Emails( $this->container );
		// Allow Hooks to be removed, by having the them registered to the container.
		$this->container->singleton( Emails::class, $emails );

		$this->container->singleton( Template::class, Template::class );
	}

	/**
	 * Registers the provider handling all the 1st level filters and actions for this Service Provider.
	 *
	 * @since TBD
	 */
	protected function register_hooks() {
		$hooks = new Hooks( $this->container );
		$hooks->register();

		// Allow Hooks to be removed, by having the them registered to the container.
		$this->container->singleton( Hooks::class, $hooks );
	}
}
