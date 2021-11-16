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
class iCalendar_Export extends Link_Abstract {
	/**
	 * {@inheritDoc}
	 */
	public static $slug = 'ics';

	/**
	 * {@inheritDoc}
	 */
	public function filter_tec_views_v2_single_subscribe_links( $links, $view) {
		// No-op, we don't add a download link - now.
		return $links;
	}

	/**
	 * {@inheritDoc}
	 */
	public static function get_label( $view ) {
		return __( 'Export .ics file', 'the-events-calendar' );
	}

	/**
	 * {@inheritDoc}
	 */
	public static function is_visible(  $view  ) {
		$template_vars = $view->get_template_vars();

		if ( ! isset( $template_vars['ical'] ) ) {
			return false;
		}

		if ( ! $template_vars['ical']->display_link ) {
			return false;
		}

		return true;
	}

	/**
	 * {@inheritDoc}
	 */
	public function get_uri( $view ) {
		$template_vars = $view->get_template_vars();

		if ( ! isset( $template_vars['ical'] ) ) {
			return '';
		}

		if ( ! $template_vars['ical']->display_link ) {
			return '';
		}

		if ( ! isset( $template_vars['ical']->link->url ) ) {
			return '';
		}

		return $template_vars['ical']->link->url;
	}
}
