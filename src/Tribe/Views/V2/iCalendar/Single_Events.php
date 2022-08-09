<?php
/**
 * Handles (optionally) converting iCalendar export links to subscribe links.
 *
 * @since   4.6.0
 *
 * @package Tribe\Events\Views\V2\iCalendar
 */

namespace Tribe\Events\Views\V2\iCalendar;

use Tribe\Events\Views\V2\View;

/**
 * Class Single_Events
 *
 * @since   5.16.0
 *
 * @package Tribe\Events\Views\V2\iCalendar
 */
class Single_Events {
	/**
	 * Stores the template class used.
	 *
	 * @since 5.16.0
	 *
	 * @var Template
	 */
	protected $template;

	/**
	 * iCalendar_Handler Modification constructor.
	 *
	 * @since 5.16.0
	 *
	 * @param Template $template An instance of the plugin template handler.
	 */
	public function __construct( Template $template ) {
		$this->get_template( $template );
	}

	/**
	 * Gets the template instance used to render single events iCalendar templates.
	 *
	 * @since 5.16.0
	 *
	 * @param Template $template An instance of the plugin template handler.
	 *
	 * @return Template An instance of the plugin template handler.
	 */
	public function get_template( $template ) {
		if ( empty( $this->template ) ) {
			$this->template = $template;
		}

		return $this->template;
	}

	/**
	 * Replace (overwrite) the default single event links with subscription links.
	 *
	 * @see   `tribe_events_ical_single_event_links` filter.
	 *
	 * @since 5.16.0
	 *
	 * @param string $calendar_links The link content.
	 *
	 * @return string The altered link content.
	 */
	public function single_event_links( $calendar_links, $subscribe_links ) {
		// Clear links.
		$calendar_links = '';

		/**
		 * Allows each link type to add itself to the links on the Event Single views.
		 *
		 * @since 5.12.0
		 * @deprecated 5.16.0 - Single events use the Subscribe Dropdown.
		 *
		 * @param array<string|string> $subscribe_links The array of link objects.
		 * @param View|null            $view            The current View implementation.
		 */
		apply_filters_deprecated( 'tec_views_v2_single_subscribe_links', [ [], null ], '5.16.0', '', 'Single event subscribe links use the subscribe dropdown, there is no replacement for this filter.' );

		if ( 1 === count( $subscribe_links ) ) {
			// If we only have one link in the list, show a "button".
			$item = array_shift( $subscribe_links );
			$calendar_links .= $this->template->template( 'components/subscribe-links/single', [ 'item' => $item ], false );
		} else {
			// If we have multiple links in the list, show a "dropdown".
			$calendar_links .= $this->template->template( 'components/subscribe-links/single-event-list', [ 'items' => $subscribe_links ], false );
		}

		return $calendar_links;
	}
}
