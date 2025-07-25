<?php
/**
 * Controller class for handling the AI MCP integration feature.
 * This class acts as the main entry point for managing the lifecycle of
 * AI MCP tools, including registering dependencies, adding filters, and
 * unregistering actions when necessary.
 *
 * @since TBD
 *
 * @package TEC\Events\AI
 */

namespace TEC\Events\AI;

use TEC\Common\Contracts\Provider\Controller as Controller_Contract;

/**
 * Class Controller
 *
 * @since TBD
 *
 * @package TEC\Events\AI
 */
class Controller extends Controller_Contract {

	/**
	 * Whether the controller is active or not.
	 *
	 * @since TBD
	 *
	 * @return bool Whether the controller is active or not.
	 */
	public function is_active(): bool {
		// Check if MCP support is enabled in settings.
		$mcp_support = tribe_get_option( 'tec_mcp_support', false );

		// TODO: Remove this once we have a proper way to enable/disable MCP support.
		$mcp_support = true;

		return $mcp_support;
	}

	/**
	 * Register the controller.
	 *
	 * @since TBD
	 */
	public function do_register(): void {
		// Register AI_Service singleton in the container.
		$this->container->singleton( MCP\AI_Service::class );
		$this->container->register( MCP\Angie::class );

		// Initialize AI_Service.
		$this->container->make( MCP\AI_Service::class )->init();
	}

	/**
	 * Unregister the controller.
	 *
	 * @since TBD
	 */
	public function unregister(): void {
		// Get AI_Service instance and unregister it.
		if ( $this->container->has( MCP\AI_Service::class ) ) {
			$this->container->make( MCP\AI_Service::class )->unregister();
		}
	}
}
