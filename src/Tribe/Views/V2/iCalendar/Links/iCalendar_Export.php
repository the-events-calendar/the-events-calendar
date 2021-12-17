<?php
/**
 * Handles iCalendar export links.
 *
 * @since   5.12.0
 *
 * @package Tribe\Events\Views\V2\iCalendar
 */

namespace Tribe\Events\Views\V2\iCalendar\Links;
use Tribe\Events\Views\V2\View;

/**
 * Class iCal
 *
 * @since   5.12.0
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
	public function register() {
		$this->label = __( 'Export .ics file', 'the-events-calendar' );
		$this->single_label = $this->label;
	}

	/**
	 * {@inheritDoc}
	 */
	public function is_visible( View $view = null ) {
		if ( null === $view ) {
			return false;
		}

		$template_vars = $view->get_template_vars();

		if ( ! isset( $template_vars['ical'] ) ) {
			return false;
		}

		if ( ! $template_vars['ical']->display_link ) {
			return false;
		}

		return $this->display;
	}

	/**
	 * {@inheritDoc}
	 */
	public function get_uri( View $view = null ) {
		if ( null === $view ) {
			return '';
		}

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
