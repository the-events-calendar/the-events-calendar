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
			(array) tribe_get_request_var( 'view_data', [] ),
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

	/**
	 * Cleans the View data that will be printed by the `components/data.php` template to avoid its mangling.
	 *
	 * By default, the View data is a copy of the View template variables, to avoid the mangling of the JSON data
	 * some entries of the data might require to be removed, some might require to be formatted or escaped.
	 *
	 * @since 5.1.5
	 *
	 * @param array<string,string|array> $view_data The initial View data.
	 *
	 * @return array<string,string|array> The filtered View data, some entries removed from it to avoid the data script
	 *                                    being mangled by escaping and texturizing functions running on it.
	 */
	public static function clean_data( $view_data ) {
		if ( ! is_array( $view_data ) ) {
			return $view_data;
		}

		/*
		 * Remove the JSON-LD data, it's already printed by the `components/json-ld-data.php` template. Printing a
		 * `<script>`, the JSON-LD data, inside a `<script>`, the data, will cause issues.
		 */
		$view_data = array_diff_key( $view_data, array_flip( [ 'json_ld_data' ] ) );

		// Remove objects that should not be printed on the page, keep data objects.
		$view_data = array_filter( $view_data, static function ( $value ) {
			return ! is_object( $value ) || $value instanceof \stdClass;
		} );

		return $view_data;
	}
}
