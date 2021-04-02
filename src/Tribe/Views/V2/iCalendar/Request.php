<?php
/**
 * Models an HTTP request for an iCalendar export.
 *
 * @since   TBD
 *
 * @package Tribe\Events\Views\V2\iCalendar
 */

namespace Tribe\Events\Views\V2\iCalendar;

use Tribe\Events\Views\V2\View;
use Tribe__Context as Context;
use Tribe__Events__iCal as iCal;

/**
 * Class Request
 *
 * @since   TBD
 *
 * @package Tribe\Events\Views\V2\iCalendar
 */
class Request {
	/**
	 * A reference to the base implementation of the iCalendar exports handler.
	 *
	 * @since TBD
	 *
	 * @var iCal
	 */
	protected $ical;

	/**
	 * A reference the context used for the request.
	 *
	 * @since TBD
	 *
	 * @var Context
	 */
	protected $context;

	/**
	 * Request constructor.
	 *
	 * @since TBD
	 *
	 * @param Context|null $context Which context was used to prepare this request for iCal.
	 * @param iCal|null    $ical    Either a reference to an explicit instance of the base
	 *                              iCalendar exports handler, or `null` to use the one provided
	 *                              by the `tribe` Service Locator.
	 */
	public function __construct( Context $context = null, iCal $ical = null ) {
		$this->ical    = $ical ?: tribe( 'tec.iCal' );
		$this->context = $context ?: tribe_context();
	}

	/**
	 * Returns the ordered list of event post IDs that match the current
	 * iCalendar export request.
	 *
	 * @since TBD
	 *
	 * @return array<int> A list of event post IDs that match the current
	 *                    iCalendar export request.
	 */
	public function get_event_ids() {
		$view = View::make( $this->context->get( 'view', 'default' ), $this->context );

		// @todo @bordoni test this method for each View. w/ and w/o date set, with some filtering args.
		$event_ids = $view->get_ical_ids( $this->ical->feed_posts_per_page() );

		return $event_ids;
	}
}
