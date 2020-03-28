<?php
/**
 * Provides common View v2 utilities.
 *
 * @since   4.9.4
 * @package Tribe\Events\Views\V2\Utils
 */
namespace Tribe\Events\Views\V2\Utils;

use Tribe__Utils__Array as Arr;

/**
 * Class Utils View
 * @since   4.9.4
 * @package Tribe\Events\Views\V2\Utils
 */
class View {
	/**
	 * Reads a view data entry from the current request.
	 *
	 * @since 4.9.4
	 *
	 * @param string|array $indexes One ore more indexes to check for in the view data.
	 * @param null|mixed   $default The default value to return if the data is not found.
	 *
	 * @return mixed|null The view data, if found, or a default value.
	 */
	public static function get_data( $indexes, $default = null ) {
		$found = Arr::get_first_set(
			tribe_get_request_var( 'view_data', [] ),
			(array) $indexes,
			$default
		);

		return empty( $found ) || $default === $found ? $default : $found;
	}

	/**
	 * Based on the `permalink_structure` determines which variable the view should read `event_display_mode` for past
	 * URL management.
	 *
	 * @since 5.0.0
	 *
	 * @return string URL Query Variable Key
	 */
	public static function get_past_event_display_key() {
		$event_display_key = 'eventDisplay';

		// When dealing with "Plain Permalink" we need to move `past` into a separate url argument.
		if ( ! get_option( 'permalink_structure' ) ) {
			$event_display_key = 'tribe_event_display';
		}

		return $event_display_key;
	}

}