<?php
/**
 * Factory for generating steps for the onboarding wizard.
 *
 * @since 7.0.0
 *
 * @package TEC\Events\Admin\Onboarding\Steps
 */

namespace TEC\Events\Admin\Onboarding\Steps;

use WP_Error;
/**
 * Class Factory
 *
 * @since 7.0.0
 */
class Factory {
	/**
	 * The tab number for this step.
	 *
	 * @since 7.0.0
	 *
	 * @var int
	 */
	public static $step_number = 0;

	/**
	 * Store all errors that happen during the last creation.
	 *
	 * @since 7.0.0
	 *
	 * @var ?WP_Error
	 */
	protected ?WP_Error $error;

	/**
	 * Which arguments are valid for the step,
	 *
	 * @since 7.0.0
	 *
	 * @var array<string> $valid_arguments
	 */
	protected static array $valid_arguments = [
		'step_number' => true,
		'options'     => true,
		'settings'    => true,
		'plugins'     => true,
		'organizer'   => true,
		'venue'       => true,
	];

	/**
	 * Which arguments are valid for an option.
	 *
	 * @since 7.0.0
	 *
	 * @var array<string> $valid_option
	 */
	protected static array $valid_option = [
		'key'   => true,
		'value' => true,
	];

	/**
	 * Which arguments are valid for a setting.
	 *
	 * @since 7.0.0
	 *
	 * @var array<string> $valid_setting
	 */
	protected static array $valid_setting = [
		'plugin' => true,
		'key'    => true,
		'value'  => true,
	];

	/**
	 * Which arguments are valid for a plugin.
	 *
	 * @since 7.0.0
	 *
	 * @var array<string> $valid_plugin
	 */
	protected static array $valid_plugin = [
		'plugin'   => true,
		'required' => true,
		'version'  => true,
	];

	/**
	 * Which arguments are valid for an organizer.
	 *
	 * @since 7.0.0
	 *
	 * @var array<string> $valid_organizer
	 */
	protected static array $valid_organizer = [
		'name' => true,
		'data' => [
			'id'                => true,
			'Organizer'         => true,
			'_OrganizerPhone'   => true,
			'_OrganizerWebsite' => true,
			'_OrganizerEmail'   => true,
		],
	];

	/**
	 * Which arguments are valid for a venue.
	 *
	 * @since 7.0.0
	 *
	 * @var array<string> $valid_venue
	 */
	protected static array $valid_venue = [
		'name' => true,
		'data' => [
			'id'            => true,
			'Venue'         => true,
			'_VenueAddress' => true,
			'_VenueCity'    => true,
			'_VenueState'   => true,
			'_VenueZip'     => true,
			'_VenueCountry' => true,
			'_VenuePhone'   => true,
			'_VenueWebsite' => true,
		],
	];

	/**
	 * Required data for an option.
	 *
	 * @since 7.0.0
	 *
	 * @var array<string> $option_data
	 */
	protected static array $option_data = [
		'key'   => '',
		'value' => '',
	];

	/**
	 * Required data for a setting.
	 *
	 * @since 7.0.0
	 *
	 * @var array<string> $setting_data
	 */
	protected static array $setting_data = [
		'plugin' => '',
		'key'    => '',
		'value'  => '',
	];

	/**
	 * Required data for a plugin.
	 *
	 * @since 7.0.0
	 *
	 * @var array<string> $plugin_data
	 */
	protected static array $plugin_data = [
		'plugin'   => '',
		'required' => false,
	];

	/**
	 * Stores the step data.
	 *
	 * @since 7.0.0
	 *
	 * @var array $data
	 */
	protected array $data = [
		'step_number'  => 0,
		'options'      => [],
		'settings'     => [],
		'plugins'      => [],
		'organizer'    => [],
		'venue'        => [],
	];

	/**
	 * The steps that are available.
	 *
	 * @since 7.0.0
	 *
	 * @var array $steps
	 */
	public static array $steps = [
		'Optin',
		'Display',
		'Settings',
		'Organizer',
		'Venue',
		'Tickets',
	];

	/**
	 * Generates a new step object from an array of data.
	 *
	 * @since 7.0.0
	 *
	 * @param array $data The step data, in array format.
	 *
	 * @return self
	 */
	public static function from_array( array $data ): self {
		$step = new static();

		foreach ( $data as $key => $value ) {
			if ( ! in_array( $key, static::$valid_arguments, true ) ) {
				continue;
			}

			// Options, settings, plugins, and linked posts are arrays of arrays.
			if ( method_exists( $step, 'validate_' . $key ) && ! $step->{'validate_' . $key}( $value ) ) {
				$step->error->add( 'invalid_' . $key, 'Invalid ' . $key . ' data' );
				continue;
			}

			$step->$key( $value );
		}

		return $step;
	}

	/**
	 * Generates a new step object from a JSON string.
	 *
	 * @since 7.0.0
	 *
	 * @param string $json The step data, in JSON format.
	 *
	 * @return self
	 */
	public static function from_json( string $json ): self {
		$data = json_decode( $json, true );

		if ( ! is_array( $data ) ) {
			return new WP_Error( 'invalid_json', 'Invalid JSON data' );
		}

		return static::from_array( $data );
	}

	/**
	 * Validates a step option.
	 *
	 * @since 7.0.0
	 *
	 * @param array $option The option data to validate.
	 *
	 * @return boolean
	 */
	protected function validate_option( array $option ): bool {
		foreach ( static::$valid_option as $key ) {
			if ( ! array_key_exists( $key, $option ) ) {
				return false;
			}
		}

		return true;
	}

	/**
	 * Validates a step setting.
	 *
	 * @since 7.0.0
	 *
	 * @param array $setting The setting data to validate.
	 *
	 * @return boolean
	 */
	protected function validate_setting( array $setting ): bool {
		foreach ( static::$valid_setting as $key ) {
			if ( ! array_key_exists( $key, $setting ) ) {
				return false;
			}
		}

		return true;
	}

	/**
	 * Validates a step organizer.
	 * Note: we don't validate the data array, as it contains optional values.
	 *
	 * @since 7.0.0
	 *
	 * @param array $plugin The plugin data to validate.
	 *
	 * @return boolean
	 */
	protected function validate_organizer( array $organizer ): bool {
		foreach ( static::$valid_organizer as $key ) {
			if ( ! array_key_exists( $key, $organizer ) ) {
				return false;
			}
		}

		return true;
	}

	/**
	 * Validates a step venue.
	 * Note: we don't validate the data array, as it contains optional values.
	 *
	 * @since 7.0.0
	 *
	 * @param array $venue The venue data to validate.
	 *
	 * @return boolean
	 */
	protected function validate_venue( array $venue ): bool {
		foreach ( static::$valid_venue as $key ) {
			if ( ! array_key_exists( $key, $venue ) ) {
				return false;
			}
		}

		return true;
	}

	/**
	 * Validates a step plugin.
	 *
	 * @since 7.0.0
	 *
	 * @param array $plugin The plugin data to validate.
	 * @return boolean
	 */
	protected function validate_plugin( array $plugin ): bool {
		foreach ( static::$valid_plugin as $key ) {
			if ( ! array_key_exists( $key, $plugin ) ) {
				if (
					( $key === 'class' && array_key_exists( 'function', $plugin ) )
					|| ( $key === 'function' && array_key_exists( 'class', $plugin ) )
				) {
					// We need either class OR function to test for the plugin's existence.
					continue;
				}

				return false;
			}
		}

		return true;
	}

	/**
	 * Returns the error instance with all the problems that happened during the last validation.
	 *
	 * @since 7.0.0
	 *
	 * @return WP_Error
	 */
	public function get_error(): WP_Error {
		return $this->error;
	}
}
