<?php
/**
 * Controller class for handling the AI MCP integration feature in The Events Calendar.
 * This empty controller extends the Controller Contract for potential future
 * plugin-specific AI implementations.
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
		return false;
	}

	/**
	 * Register the controller.
	 *
	 * @since TBD
	 */
	public function do_register(): void {
		// Plugin-specific AI implementations can be added here.
	}

	/**
	 * Unregister the controller.
	 *
	 * @since TBD
	 */
	public function unregister(): void {
		// Plugin-specific cleanup can be added here.
	}
}