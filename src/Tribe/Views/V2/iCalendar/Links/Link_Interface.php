<?php
/**
 * The base implementation for the Views v2 query controllers.
 *
 * @package Tribe\Events\Views\V2\iCalendar
 * @since 5.12.0
 */

namespace Tribe\Events\Views\V2\iCalendar\Links;

use \Tribe\Events\Views\V2\View as View;

/**
 * Class Link_Interface
 *
 * @package Tribe\Events\Views\V2\iCalendar
 * @since 5.12.0
 */
interface Link_Interface {
	/**
	 * Adds a subscribe link object to the list of links for template consumption.
	 *
	 * @since 5.12.0
	 *
	 * @param array                       $subscribe_links The list of subscribe links.
	 * @param \Tribe\Events\Views\V2\View $view            The current View object.
	 *
	 * @return array $subscribe_links The modified list of links.
	 */
	public function filter_tec_views_v2_subscribe_links( $subscribe_links, $view );

	/**
	 * Adds a link to those displayed on the single event view.
	 *
	 * @since 5.12.0
	 *
	 * @param array<string>               $links The current list of links.
	 * @param \Tribe\Events\Views\V2\View $view  The current View object.
	 *
	 * @return array<string> $links The modified list of links.
	 */
	public function filter_tec_views_v2_single_subscribe_links( $links, $view );

	/**
	 * Getter function for the display property.
	 *
	 * @since 5.12.0
	 *
	 * @param \Tribe\Events\Views\V2\View|null $view The current View object.
	 *
	 * @return boolean
	 */
	public function is_visible( $view );

	/**
	 * Setter function for the display property.
	 *
	 * @since 5.12.0
	 *
	 * @param boolean $visible
	 */
	public function set_visibility( bool $visible );

	/**
	 * Getter function for the label property.
	 *
	 * @since 5.12.0
	 *
	 * @param \Tribe\Events\Views\V2\View $view The current View object.
	 *
	 * @return string The translated link text/label.
	 */
	public function get_label( View $view );

	/**
	 * Getter function for the single label property.
	 *
	 * @since 5.12.0
	 *
	 * @param \Tribe\Events\Views\V2\View $view The current View object.
	 *
	 * @return string The translated link text/label for the single event view.
	 */
	public function get_single_label( View $view );

	/**
	 * Getter function for the uri property.
	 *
	 * @since 5.12.0
	 *
	 * @param \Tribe\Events\Views\V2\View $view The current View object.
	 *
	 * @return string The url for the link calendar subscription "feed", or download.
	 */
	public function get_uri( $view );
}
