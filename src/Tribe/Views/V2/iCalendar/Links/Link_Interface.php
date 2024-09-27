<?php
/**
 * The base implementation for the Views v2 query controllers.
 *
 * @since   5.12.0
 * @package Tribe\Events\Views\V2\iCalendar
 */

namespace Tribe\Events\Views\V2\iCalendar\Links;

use Tribe\Events\Views\V2\View;

/**
 * Class Link_Interface
 *
 * @since   5.12.0
 * @package Tribe\Events\Views\V2\iCalendar
 */
interface Link_Interface {
	/**
	 * Registers the objects and filters required by the provider to manage subscribe links.
	 *
	 * @since 5.12.3
	 */
	public function register();

	/**
	 * Adds a subscribe link object to the list of links for template consumption.
	 *
	 * @since 5.12.0
	 *
	 * @param array $subscribe_links The list of subscribe links.
	 *
	 * @return array The modified list of links.
	 */
	public function filter_tec_views_v2_subscribe_links( $subscribe_links );

	/**
	 * Adds a link to those displayed on the single event view.
	 *
	 * @since 5.12.0
	 *
	 * @param array<string> $links The current list of links.
	 *
	 * @return array<string> The modified list of links.
	 */
	public function filter_tec_views_v2_single_subscribe_links( $links );

	/**
	 * Getter function for the display property.
	 *
	 * @since 5.12.0
	 * @since 5.14.0 Removed unused view param.
	 *
	 * @return bool
	 */
	public function is_visible();

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
	 * @param View|null $view The current View object.
	 *
	 * @return string The translated link text/label.
	 */
	public function get_label( View $view = null );

	/**
	 * Getter function for the single label property.
	 *
	 * @since 5.12.0
	 *
	 * @param View|null $view The current View object.
	 *
	 * @return string The translated link text/label for the single event view.
	 */
	public function get_single_label( View $view = null );

	/**
	 * Getter function for the uri property.
	 *
	 * @since 5.12.0
	 *
	 * @param View|null $view The current View object.
	 *
	 * @return string The url for the link calendar subscription "feed", or download.
	 */
	public function get_uri( View $view = null );
}
