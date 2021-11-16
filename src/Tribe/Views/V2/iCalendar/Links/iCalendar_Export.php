<?php
/**
 * Handles iCalendar export links.
 *
 * @since   TBD
 *
 * @package Tribe\Events\Views\V2\iCalendar
 */

namespace Tribe\Events\Views\V2\iCalendar\Links;

/**
 * Class iCal
 *
 * @since   TBD
 *
 * @package Tribe\Events\Views\V2\iCalendar
 */
class iCalendar_Export extends Abstract_Link {
	/**
	 * {@inheritDoc}
	 */
	public function add_subscribe_link( $template_vars, $view ) {
		/**
		 * Add the .ics legacy export link.
		 *
		 * This is controlled by the default iCal_Data trait.
		 *
		 * @see Tribe\Events\Views\V2\Views\Traits\iCal_Data
		 */
		$has_ical = isset( $template_vars['ical'] ) && $template_vars['ical']->display_link;

		$template_vars['subscribe_links'][ 'ics' ] = [
			'display' => $has_ical,
			'label' => __( 'Export Events', 'the-events-calendar' ),
			'uri'   => $has_ical ? $template_vars['ical']->link->url : '',
		];

		return $template_vars;
	}

	/**
	 * {@inheritDoc}
	 */
	public function add_single_subscribe_link( $links, $view) {
		// No-op, we don't add a download link - now.
		return $links;
	}
}
