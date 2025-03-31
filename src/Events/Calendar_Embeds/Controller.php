<?php
/**
 * Manages the External Calendar Embeds Feature.
 *
 * @since 6.11.0
 *
 * @package TEC\Events\Calendar_Embeds
 */

namespace TEC\Events\Calendar_Embeds;

use TEC\Common\Contracts\Provider\Controller as Controller_Contract;
use TEC\Events\Calendar_Embeds\Admin\List_Page;
use TEC\Events\Calendar_Embeds\Admin\Singular_Page;
use TEC\Common\StellarWP\Assets\Config;
use Tribe__Events__Main as TEC_Plugin;

/**
 * Class Controller
 *
 * @since 6.11.0

 * @package TEC\Events\Calendar_Embeds
 */
class Controller extends Controller_Contract {

	/**
	 * Registers the filters and actions hooks added by the controller.
	 *
	 * @since 6.11.0
	 *
	 * @return void
	 */
	public function do_register(): void {
		Config::add_group_path( 'tec-events-calendar-embeds', TEC_Plugin::instance()->plugin_path, 'build/Calendar_Embeds/' );
		$this->container->register( Calendar_Embeds::class );
		$this->container->register( Frontend::class );
		if ( is_admin() ) {
			$this->container->register( List_Page::class );
			$this->container->register( Singular_Page::class );
		}
	}

	/**
	 * Removes the filters and actions hooks added by the controller.
	 *
	 * @since 6.11.0
	 *
	 * @return void
	 */
	public function unregister(): void {
		$this->container->get( Calendar_Embeds::class )->unregister();
		$this->container->get( Frontend::class )->unregister();
		if ( is_admin() ) {
			$this->container->get( List_Page::class )->unregister();
			$this->container->get( Singular_Page::class )->unregister();
		}
	}
}
