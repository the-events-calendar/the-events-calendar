<?php
/**
 * Responsible for registering handlers for changes to the Max Recurrence (recurrenceMaxMonthsAfter) option.
 *
 * @since   6.0.0
 *
 * @package TEC\Events\Custom_Tables\V1\Events
 */

namespace TEC\Events\Custom_Tables\V1\Events\Occurrences;

use tad_DI52_ServiceProvider as Service_Provider;

/**
 * Class Max_Recurrence_Provider
 *
 * This is defined in TEC because the default variable is defined in TEC, not PRO, despite being a PRO variable.
 *
 * @since   6.0.0
 *
 * @package TEC\Events\Custom_Tables\V1\Events\Occurrences
 */
class Max_Recurrence_Provider extends Service_Provider {
	/**
	 * A flag property indicating whether the Service Provide did register or not.
	 *
	 * @since 6.0.0
	 *
	 * @var bool
	 */
	private $did_register = false;

	/**
	 * Registers the filters and implementations required by the Custom Tables implementation.
	 *
	 * @since 6.0.0
	 *
	 * @return bool Whether the Provider did register or not.
	 */
	public function register() {

		if ( $this->did_register ) {
			// Let's avoid double filtering by making sure we're registering at most once.
			return true;
		}

		$this->did_register = true;

		add_filter( 'tribe_events_settings_default_fields_initializer', [ $this, 'change_default_options' ] );
		add_filter( 'tribe_settings_tab_fields', [ $this, 'change_default_settings_field' ], 99, 2 );
	}

	/**
	 * Sets our max recurrence months setting for initialized sites.
	 *
	 * @since 6.0.0
	 *
	 * @param array $defaults
	 *
	 * @return array|mixed
	 */
	public function change_default_options( $options = [] ) {
		return $this->container->make( Max_Recurrence::class )->change_default_options( $options );
	}

	/**
	 * Sets our default value for the `recurrenceMaxMonthsAfter` field on the settings page.
	 *
	 * @since 6.0.0
	 *
	 * @param array  $fields
	 * @param string $settings_tab
	 *
	 * @return array|mixed
	 */
	public function change_default_settings_field( $fields, $settings_tab ) {
		return $this->container->make( Max_Recurrence::class )->change_default_settings_field( $fields, $settings_tab );
	}
}