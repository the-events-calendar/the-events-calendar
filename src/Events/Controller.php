<?php
/**
 * Controller class for the Events plugin.
 *
 * @since 6.15.0
 *
 * @package TEC\Events
 */

declare(strict_types=1);

namespace TEC\Events;

use TEC\Common\Contracts\Provider\Controller as Controller_Contract;
use TEC\Events\Blocks\Controller as Blocks_Controller;
use TEC\Events\Block_Templates\Controller as Block_Templates_Controller;
use TEC\Events\Integrations\Provider as Integrations_Provider;
use TEC\Events\Installer\Provider as Installer_Provider;
use TEC\Events\Site_Health\Provider as Site_Health_Provider;
use TEC\Events\Telemetry\Provider as Telemetry_Provider;
use TEC\Events\Notifications\Provider as Notifications_Provider;
use TEC\Events\QR\Controller as QR_Controller;
use TEC\Events\SEO\Controller as SEO_Controller;
use TEC\Events\SEO\Headers\Controller as SEO_Headers_Controller;
use TEC\Events\Admin\Controller as Admin_Controller;
use TEC\Events\Admin\Notice\Provider as Admin_Notice_Provider;
use TEC\Events\Admin\Settings\Provider as Admin_Settings_Provider;
use TEC\Events\Admin\Onboarding\Controller as Admin_Onboarding_Controller;
use TEC\Events\Admin\Help_Hub\Provider as Admin_Help_Hub_Provider;
use TEC\Events\Category_Colors\Controller as Category_Colors_Controller;
use TEC\Events\Calendar_Embeds\Controller as Calendar_Embeds_Controller;
use TEC\Events\Custom_Tables\V1\Provider as Custom_Tables_V1_Provider;
use TEC\Events\REST\Controller as REST_Controller;

/**
 * Class Controller
 *
 * @since 6.15.0
 *
 * @package TEC\Events
 */
class Controller extends Controller_Contract {
	/**
	 * Returns the controllers to register.
	 *
	 * @since 6.15.0
	 *
	 * @return array<array<class-string>>
	 */
	protected function get_controllers(): array {
		return [
			[ Blocks_Controller::class ],
			[ Block_Templates_Controller::class ],
			[ Integrations_Provider::class ],
			[ Installer_Provider::class ],
			[ Site_Health_Provider::class ],
			[ Telemetry_Provider::class ],
			[ Notifications_Provider::class ],
			[
				'on_action' => 'tec_qr_code_loaded',
				QR_Controller::class,
			],
			[ SEO_Controller::class ],
			[ SEO_Headers_Controller::class ],
			[ Admin_Controller::class ],
			[ Admin_Notice_Provider::class ],
			[ Admin_Settings_Provider::class ],
			[ Admin_Onboarding_Controller::class ],
			[ Admin_Help_Hub_Provider::class ],
			[ Category_Colors_Controller::class ],
			[ Calendar_Embeds_Controller::class ],
			[ REST_Controller::class ],
		];
	}

	/**
	 * Registers the filters and actions hooks added by the controller.
	 *
	 * @since
	 *
	 * @return void
	 */
	protected function do_register(): void {
		// Custom tables v1 implementation.
		if ( class_exists( Custom_Tables_V1_Provider::class ) ) {
			$this->container->register_on_action( 'tribe_common_loaded', Custom_Tables_V1_Provider::class );
		}

		foreach ( $this->get_controllers() as $controller ) {
			if ( ! is_array( $controller ) ) {
				continue;
			}

			if ( isset( $controller['on_action'] ) ) {
				$action = $controller['on_action'];
				unset( $controller['on_action'] );

				$this->container->register_on_action( $action, ...$controller );
				continue;
			}

			$this->container->register( ...$controller );
		}
	}

	/**
	 * Unregisters the filters and actions hooks added by the controller.
	 *
	 * @since 6.15.0
	 *
	 * @return void Filters and actions hooks added by the controller are be removed.
	 */
	public function unregister(): void {
		foreach ( $this->get_controllers() as $controller ) {
			if ( ! is_array( $controller ) ) {
				continue;
			}

			unset( $controller['on_action'] );
			$controller = array_values( $controller );

			if ( ! $this->container->isBound( $controller[0] ) ) {
				continue;
			}

			$controller = $this->container->get( $controller[0] );

			if ( ! $controller instanceof Controller_Contract ) {
				continue;
			}

			$controller->unregister();
		}
	}
}
