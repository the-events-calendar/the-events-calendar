<?php
/**
 * The Front End QR Code Widget View.
 *
 * @package Tribe\Events\Views\V2\Views\Widgets
 * @since TBD
 */

namespace Tribe\Events\Views\V2\Views\Widgets;

use Tribe\Events\Views\V2\Messages;

/**
 * Class Widget_QR_Code_View
 *
 * @since   TBD
 *
 * @package Tribe\Events\Views\V2\Views\Widgets
 */
class Widget_QR_Code_View extends Widget_View {

	/**
	 * The slug for this view.
	 *
	 * @since TBD
	 *
	 * @var string
	 */
	protected static $view_slug = 'widget-events-qr-code';

	/**
	 * Sets up the user-facing messages the View will print on the frontend.
	 *
	 * @since TBD
	 *
	 * @param array $events — An array of the View events, if any.
	 *
	 * @return void
	 */
	protected function setup_messages( array $events ) {
		if ( ! empty( $events ) ) {
			return;
		}

		$keyword = $this->context->get( 'keyword', false );
		$this->messages->insert(
			Messages::TYPE_NOTICE,
			Messages::for_key( 'no_upcoming_events', trim( $keyword ) )
		);
	}

	/**
	 * Overrides the base method to return an empty array, since the widget will not use breadcrumbs.
	 *
	 * @since TBD
	 *
	 * @return array An empty array, the widget will not use breadcrumbs.
	 */
	protected function get_breadcrumbs() {
		return [];
	}
}
