<?php
/**
 * Manages the External Calendar Embeds Feature.
 *
 * @since TBD
 *
 * @package TEC\Events\Calendar_Embeds
 */

namespace TEC\Events\Calendar_Embeds;

use TEC\Common\Contracts\Provider\Controller as Controller_Contract;
use TEC\Events\Calendar_Embeds\Admin\Page;

/**
 * Class Controller
 *
 * @since TBD

 * @package TEC\Events\Calendar_Embeds
 */
class Controller extends Controller_Contract {

	/**
	 * Registers the filters and actions hooks added by the controller.
	 *
	 * @since TBD
	 *
	 * @return void
	 */
	public function do_register(): void {
		$this->container->register( Calendar_Embeds::class );
		if ( is_admin() ) {
			$this->container->register( Page::class );
		}
	}

	/**
	 * Removes the filters and actions hooks added by the controller.
	 *
	 * @since TBD
	 *
	 * @return void
	 */
	public function unregister(): void {
		$this->container->get( Calendar_Embeds::class )->unregister();
		if ( is_admin() ) {
			$this->container->get( Page::class )->unregister();
		}
	}
}
