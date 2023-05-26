<?php
/**
 * Class Completed_Order.
 *
 * @since TBD
 *
 * @package TEC\Events\Integrations\Plugins\Event_Tickets\Emails
 */

namespace TEC\Events\Integrations\Plugins\Event_Tickets\Emails\Email;

use TEC\Events\Integrations\Plugins\Event_Tickets\Emails\Template;
use TEC\Tickets\Emails\Email\Completed_Order as Completed_Order_Email;

/**
 * Class Completed_Order.
 *
 * @since TBD
 *
 * @package TEC\Events\Integrations\Plugins\Event_Tickets
 */
class Completed_Order {

	/**
	 * Includes event styles in email body for Completed Order emails.
	 *
	 * @since TBD
	 *
	 * @param \Tribe__Template $parent_template Event Tickets template object.
	 *
	 * @return void
	 */
	public function include_event_styles( $parent_template ): void {
		$args = $parent_template->get_local_values();

		if ( ! $args['email'] instanceof Completed_Order_Email ) {
			return;
		}

		tribe( Template::class )->template( 'template-parts/header/head/tec-styles', $args, true );
	}
	
    /**
	 * Includes event title in email body for Completed Order emails.
	 *
	 * @since TBD
	 *
	 * @param \Tribe__Template $parent_template Event Tickets template object.
	 *
	 * @return void
	 */
	public function include_event_title( $parent_template ) {
		$args = $parent_template->get_local_values();

		if ( ! $args['email'] instanceof Completed_Order_Email ) {
			return;
		}

		tribe( Template::class )->template( 'template-parts/body/order/event-title', $args, true );
	}
}
