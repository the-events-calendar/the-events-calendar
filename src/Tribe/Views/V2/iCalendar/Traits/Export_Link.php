<?php
/**
 * Handles common traits for export links.
 *
 * @since   TBD
 *
 * @package Tribe\Events\Views\V2\iCalendar
 */

namespace Tribe\Events\Views\V2\iCalendar\Traits;

use Tribe\Events\Views\V2\View;
use Tribe__Events__Main;

/**
 * Trait Export_Link
 *
 * @since   TBD
 *
 * @package Tribe\Events\Views\V2\iCalendar
 */
trait Export_Link {

	/**
	 * The query argument slug for the download link.
	 *
	 * @since TBD
	 *
	 * @var string
	 */
	private static $query_arg = 'ical';

	/**
	 * Adding our filter hooks.
	 *
	 * @since TBD
	 */
	public function filters() {
		// Filters the subscribe link based on the final class slug.
		$slug = static::get_slug();
		add_filter( "tec_views_v2_subscribe_link_{$slug}_visibility", [ $this, 'filter_tec_views_v2_subscribe_link_visibility' ], 10, 2 );
	}

	/**
	 * Filters the is_visible() function to not display export (download) links on single events.
	 *
	 * @since 5.14.0
	 * @since TBD Moved to trait from export classes.
	 *
	 * @param boolean       $visible     Whether to display the link.
	 * @param Link_Abstract $link_object The current link object.
	 *
	 * @return boolean $visible Whether to display the link.
	 */
	public function filter_tec_views_v2_subscribe_link_visibility( $visible, $link_object ) {
		// $slug is defined in the class that uses this trait.
		if ( $link_object::get_slug() !== static::get_slug() ) {
			return $visible;
		}

		// Don't display on single event by default.
		$visible = ! is_single();

		/**
		 * Allows filtering of the visibility of all export links.
		 *
		 * @since TBD
		 *
		 * @param boolean $visible Whether to display the link.
		 * @param Link_Abstract    $link_object     The current link object.
		 */
		$visible = apply_filters( 'tec_events_export_link_visibility', $visible, $this );

		$slug = static::get_slug();

		/**
		 * Allows filtering of the visibility of a specific export link.
		 *
		 * @since TBD
		 *
		 * @param boolean       $visible     Whether to display the link.
		 * @param Link_Abstract $link_object The current link object.
		 */
		return apply_filters( "tec_events_{$slug}_export_link_visibility", $visible, $this );
	}

	/**
	 * Getter function for the uri property.
	 *
	 * @since TBD
	 *
	 * @param View|null $view The current View object.
	 *
	 * @return string The url for the link calendar subscription "feed", or download.
	 */
	public function get_uri( View $view = null ) {
		$is_single = null === $view || is_single( Tribe__Events__Main::POSTTYPE );
		$slug      = static::get_slug();

		if ( $is_single ) {
			// Try to construct it for the event single.
			$url = add_query_arg( [ static::$query_arg => 1 ], get_the_permalink() );

			/**
			 * Allows filtering of the URL for all single export links.
			 *
			 * @since TBD
			 *
			 * @param string        $url      The URL for the link.
			 * @param View          $view     The view object, if available.
			 * @param Link_Abstract $link_obj The link object the url is for.
			 */
			$url = apply_filters( 'tec_events_export_link_url_single', $url, $view, $this );

			/**
			 * Allows filtering of the URL for a specific single export link.
			 *
			 * @since TBD
			 *
			 * @param string        $url      The URL for the link.
			 * @param View          $view     The view object, if available.
			 * @param Link_Abstract $link_obj The link object the url is for.
			 */
			return apply_filters( "tec_events_{$slug}_export_link_url_single", $url, $view, $this );
		}

		$template_vars = $view->get_template_vars();

		$ical = ! empty( $template_vars['ical'] ) ? $template_vars['ical'] : $view->get_ical_data();

		if ( empty( $ical->display_link ) ) {
			return '';
		}

		if ( empty( $ical->link->url ) ) {
			return '';
		}

		$url = $ical->link->url;

		if ( 'ical' !== static::$query_arg ) {
			// Remove ical query argument and add Outlook.
			$url = remove_query_arg( 'ical', $url );
			$url = add_query_arg( [ static::$query_arg => 1 ], $url );
		}

		/**
		 * Allows filtering of the URL for the view export links.
		 *
		 * @since TBD
		 *
		 * @param string         $url       The URL for the link.
		 * @param Outlook_Export $link_obj  The link object the url is for.
		 */
		$url = apply_filters( 'tec_events_export_link_url', $url, $this );

		/**
		 * Allows filtering of the URL for a specific view export link.
		 *
		 * @since TBD
		 *
		 * @param string         $url       The URL for the link.
		 * @param Outlook_Export $link_obj  The link object the url is for.
		 */
		return apply_filters( "tec_events_{$slug}_export_link_url", $url, $this );
	}
}
