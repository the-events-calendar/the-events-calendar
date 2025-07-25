<?php
/**
 * MCP Angie Controller
 *
 * @package TEC\Events\AI\MCP
 */

namespace TEC\Events\AI\MCP;

use TEC\Common\Contracts\Provider\Controller as Controller_Contract;
use Tribe__Events__Main as Plugin;

/**
 * Class Angie
 *
 * @since TBD
 *
 * @package TEC\Events\AI\MCP
 */
class Angie extends Controller_Contract {

	/**
	 * Whether the controller is active or not.
	 *
	 * @since TBD
	 *
	 * @return bool Whether the controller is active or not.
	 */
	public function is_active(): bool {
		return current_user_can( 'use_angie' );
	}

	/**
	 * Register the controller.
	 *
	 * @since TBD
	 */
	public function do_register(): void {
		$this->register_hooks();
		$this->register_assets();
	}

	/**
	 * Unregister the controller.
	 *
	 * @since TBD
	 */
	public function unregister(): void {
		// Remove hooks if needed
	}

	/**
	 * Register hooks for the controller.
	 *
	 * @since TBD
	 *
	 * @return void
	 */
	protected function register_hooks() {
		// Add hooks here for MCP functionality
	}

	/**
	 * Register assets for the MCP server.
	 *
	 * @since TBD
	 *
	 * @return void
	 */
	protected function register_assets() {
		tec_asset(
			Plugin::instance(),
			'tec-angie-mcp-server',
			'tec-angie-mcp-server.js',
			[],
			'admin_enqueue_scripts',
			[
				'groups'       => [ 'tec-angie-mcp' ],
				'conditionals' => [ $this, 'should_enqueue_mcp_assets' ],
				'localize'     => [
					(object) [
						'name' => 'tecAngieMCP',
						'data' => [ $this, 'get_mcp_localized_data' ],
					],
				],
			]
		);
	}

	/**
	 * Check if MCP assets should be enqueued.
	 *
	 * @since TBD
	 *
	 * @return bool
	 */
	public function should_enqueue_mcp_assets() {
		return current_user_can( 'use_angie' );
	}

	/**
	 * Get localized data for MCP.
	 *
	 * @since TBD
	 *
	 * @return array
	 */
	public function get_mcp_localized_data() {
		return [
			'ajaxUrl' => admin_url( 'admin-ajax.php' ),
			'nonce'   => wp_create_nonce( 'tec-angie-mcp' ),
		];
	}
}
