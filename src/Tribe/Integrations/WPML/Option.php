<?php
/**
 * Handles the translation of the plugin settings.
 *
 * @since   5.2.0
 *
 * @package Tribe__Events__Integrations__WPML__Option
 */


/**
 * Class Tribe__Events__Integrations__WPML__Option
 *
 * @since 5.2.0
 */
class Tribe__Events__Integrations__WPML__Option {

	/**
	 * The singleton instance of this class.
	 *
	 * @since 5.2.0
	 *
	 * @var Tribe__Events__Integrations__WPML__Option
	 */
	protected static $instance;

	/**
	 * The singleton method of this class.
	 *
	 * @since 5.2.0
	 *
	 * @return Tribe__Events__Integrations__WPML__Option The singleton instance of this class.
	 */
	public static function instance() {
		if ( empty( self::$instance ) ) {
			self::$instance = new self();
		}

		return self::$instance;
	}

	/**
	 * Translate the option value using WPML filters.
	 *
	 * @since 5.2.0
	 *
	 * @param string $option_value The value of the option to translate.
	 * @param string $default      The option default value.
	 * @param string $option_name  The name of the option to translate.
	 *
	 * @return string The translated option value.
	 *
	 * @see   https://wpml.org/wpml-hook/wpml_translate_single_string/
	 */
	public function translate( $option_value, $default, $option_name ) {
		if ( is_numeric( $option_value ) || ! is_string( $option_value ) ) {
			return $option_value;
		}

		return apply_filters(
			'wpml_translate_single_string',
			$option_value,
			'admin_texts_tribe_events_calendar_options',
			'[tribe_events_calendar_options]' . $option_name
		);
	}
}
