<?php
/**
 * Responsible for defining the Max Recurrence (recurrenceMaxMonthsAfter) option, which is referenced in several
 * locations.
 *
 * @since   6.0.0
 *
 * @package TEC\Events\Custom_Tables\V1\Events
 */

namespace TEC\Events\Custom_Tables\V1\Events\Occurrences;


/**
 * Class Max_Recurrence
 *
 * This is defined in TEC because the default variable is defined in TEC, not PRO, despite being a PRO variable.
 *
 * @since   6.0.0
 *
 * @package TEC\Events\Custom_Tables\V1\Events\Occurrences
 */
class Max_Recurrence {
	/**
	 * @since 6.0.0
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
	 * @since 6.0.0
	 *
	 * @param array  $fields     The fields to be displayed on the "defaults" settings page.
	 * @param string $deprecated Unused.
	 *
	 * @return array|mixed
	 */
	public function change_default_settings_field( $fields, $deprecated = null ) { // phpcs:ignore Generic.CodeAnalysis.UnusedFunctionParameter.FoundAfterLastUsed
		if ( isset( $fields['recurrenceMaxMonthsAfter']['default'] ) ) {
			$fields['recurrenceMaxMonthsAfter']['default'] = self::get_recurrence_max_months_default();
		}

		return $fields;
	}

	/**
	 * A way to see what the base default max recurrence months value is set to. Useful for areas that do not leverage
	 * a default from initialization.
	 *
	 * @since 6.0.0
	 *
	 * @return int
	 */
	public static function get_recurrence_max_months_default() {
		return 60;
	}
}
