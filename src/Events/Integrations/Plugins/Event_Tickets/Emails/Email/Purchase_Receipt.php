<?php
/**
 * Class Purchase_Receipt.
 *
 * @since TBD
 *
 * @package TEC\Events\Integrations\Plugins\Event_Tickets\Emails
 */

namespace TEC\Events\Integrations\Plugins\Event_Tickets\Emails\Email;

use TEC\Events\Integrations\Plugins\Event_Tickets\Emails\Template;
use TEC\Tickets\Emails\Email\Purchase_Receipt as Purchase_Receipt_Email;

/**
 * Class Purchase_Receipt.
 *
 * @since TBD
 *
 * @package TEC\Events\Integrations\Plugins\Event_Tickets
 */
class Purchase_Receipt {

	/**
	 * Includes event styles in email body for Purchase Receipt emails.
	 *
	 * @since TBD
	 *
	 * @param \Tribe__Template $parent_template Event Tickets template object.
	 *
	 * @return void
	 */
	public function include_event_styles( $parent_template ): void {
		$args = $parent_template->get_local_values();

		if ( ! $args['email'] instanceof Purchase_Receipt_Email ) {
			return;
		}

		tribe( Template::class )->template( 'template-parts/header/head/tec-styles', $args, true );
	}
	
    /**
	 * Includes event title in email body for Purchase Receipt emails.
	 *
	 * @since TBD
	 *
	 * @param \Tribe__Template $parent_template Event Tickets template object.
	 *
	 * @return void
	 */
	public function include_event_title( $parent_template ) {
		$args = $parent_template->get_local_values();

		if ( ! $args['email'] instanceof Purchase_Receipt_Email ) {
			return;
		}

		tribe( Template::class )->template( 'template-parts/body/order/event-title', $args, true );
	}
}
