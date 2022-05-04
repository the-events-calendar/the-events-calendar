<?php
/**
 * Responsible for registering handlers for changes to the Max Recurrence (recurrenceMaxMonthsAfter) option. *
 *
 * @since   TBD
 *
 * @package TEC\Events\Custom_Tables\V1\Events
 */

namespace TEC\Events\Custom_Tables\V1\Events;

use tad_DI52_ServiceProvider as Service_Provider;

/**
 * Class Max_Recurrence_Provider
 *
 * @since   TBD
 *
 * @package TEC\Events\Custom_Tables\V1\Events
 */
class Max_Recurrence_Provider extends Service_Provider {
	/**
	 * A flag property indicating whether the Service Provide did register or not.
	 *
	 * @since TBD
	 *
	 * @var bool
	 */
	private $did_register = false;

	/**
	 * Registers the filters and implementations required by the Custom Tables implementation.
	 *
	 * @since TBD
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
	}

	/**
	 * @since TBD
	 *
	 * @param array $defaults
	 *
	 * @return array|mixed
	 */
	public function change_default_options( $defaults = [] ) {
		$defaults['recurrenceMaxMonthsAfter'] = 60;

		return $defaults;
	}
}
