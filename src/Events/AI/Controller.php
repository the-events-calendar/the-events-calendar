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
		// Register MCP singleton in the container.
		$this->container->singleton( MCP::class );

		// Initialize MCP.
		$this->container->make( MCP::class )->init();
	}

	/**
	 * Unregister the controller.
	 *
	 * @since TBD
	 */
	public function unregister(): void {
		// Get MCP instance and unregister it.
		if ( $this->container->has( MCP::class ) ) {
			$this->container->make( MCP::class )->unregister();
		}
	}
}
