<?php
/**
 * Responsible for defining the Max Recurrence (recurrenceMaxMonthsAfter) option, which is referenced in several
 * locations.
 *
 * @since   TBD
 *
 * @package TEC\Events\Custom_Tables\V1\Events
 */

namespace TEC\Events\Custom_Tables\V1\Events\Occurrences;


/**
 * Class Max_Recurrence
 *
 * This is defined in TEC because the default variable is defined in TEC, not PRO, despite being a PRO variable.
 *
 * @since   TBD
 *
 * @package TEC\Events\Custom_Tables\V1\Events\Occurrences
 */
class Max_Recurrence {
	/**
	 * @since TBD
	 *
	 * @param array $defaults
	 *
	 * @return array|mixed
	 */
	public function change_default_options( $defaults = [] ) {
		$defaults['recurrenceMaxMonthsAfter'] = self::get_recurrence_max_months_default();

		return $defaults;
	}

	/**
	 * Sets our default value for the `recurrenceMaxMonthsAfter` field on the settings page.
	 *
	 * @since TBD
	 *
	 * @param array  $fields
	 * @param string $settings_tab
	 *
	 * @return array|mixed
	 */
	public function change_default_settings_field( $fields, $settings_tab ) {
		if ( $settings_tab !== 'general' ) {

			return $fields;
		}
		if ( isset( $fields['recurrenceMaxMonthsAfter']['default'] ) ) {
			$fields['recurrenceMaxMonthsAfter']['default'] = self::get_recurrence_max_months_default();
		}

		return $fields;
	}

	/**
	 * A way to see what the base default max recurrence months value is set to. Useful for areas that do not leverage
	 * a default from initialization.
	 *
	 * @since TBD
	 *
	 * @return int
	 */
	public static function get_recurrence_max_months_default() {
		return 60;
	}
}
