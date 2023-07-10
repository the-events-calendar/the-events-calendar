<?php
/**
 * Handles Outlook iCalendar export links.
 *
 * @since 5.16.0
 *
 * @package Tribe\Events\Views\V2\iCalendar
 */

namespace Tribe\Events\Views\V2\iCalendar\Links;
use Tribe\Events\Views\V2\View;
use Tribe__Events__Main;

/**
 * Class Outlook
 *
 * @since 5.16.0
 *
 * @package Tribe\Events\Views\V2\iCalendar
 */
class Outlook_Export extends Link_Abstract {
	/**
	 * {@inheritDoc}
	 */
	public static $slug = 'outlook-ics';

	/**
	 * {@inheritDoc}
	 */
	public function register() {
		$this->label = __( 'Export Outlook .ics file', 'the-events-calendar' );
		$this->single_label = $this->label;

		add_filter( 'tec_views_v2_subscribe_link_outlook-ics_visibility', [ $this, 'filter_tec_views_v2_subscribe_link_outlook_ics_visibility'], 10, 2 );
	}

	/**
	 * Filters the is_visible() function to not display on single events.
	 *
	 * @since 5.16.0
	 *
	 * @param boolean $visible Whether to display the link.
	 * @param View    $view     The current View object.
	 *
	 * @return boolean $visible Whether to display the link.
	 */
	public function filter_tec_views_v2_subscribe_link_outlook_ics_visibility( $visible ) {
		// Don't display on single event by default.
		return ! is_single();
	}

	/**
	 * {@inheritDoc}
	 */
	public function get_uri( View $view = null ) {
		if ( null === $view || is_single( Tribe__Events__Main::POSTTYPE ) ) {
			// Try to construct it for the event single.
			return add_query_arg( [ 'outlook-ical' => 1 ], get_the_permalink() );
		}

		$template_vars = $view->get_template_vars();

		$ical = ! empty( $template_vars['ical'] ) ? $template_vars['ical'] : $view->get_ical_data();

		if ( empty( $ical->display_link ) ) {
			return '';
		}

		if ( empty( $ical->link->url ) ) {
			return '';
		}

		// Remove ical query argument and add Outlook.
		$url = remove_query_arg( 'ical', $ical->link->url );
		$url = add_query_arg( [ 'outlook-ical' => 1 ], $url );

		return $url;
	}
}
