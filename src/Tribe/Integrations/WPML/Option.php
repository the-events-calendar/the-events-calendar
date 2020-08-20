<?php


/**
 * Class Tribe__Events__Integrations__WPML__Option
 *
 * Handles translation of settings.
 */
class Tribe__Events__Integrations__WPML__Option {

	/**
	 * @var Tribe__Events__Integrations__WPML__Option
	 */
	protected static $instance;

	/**
	 * @return Tribe__Events__Integrations__WPML__Option
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
	 * @see https://wpml.org/wpml-hook/wpml_translate_single_string/
	 *
	 * @param string $option
	 * @param string $default
	 * @param string $option_name
	 *
	 * @return string
	 */
	public function translate( $option, $default, $option_name ) {
		if ( is_string( $option ) ) {
			$option = apply_filters(
				'wpml_translate_single_string',
				$option,
				'admin_texts_tribe_events_calendar_options',
				'[tribe_events_calendar_options]' . $option_name
			);
		}

		return $option;
	}

}
