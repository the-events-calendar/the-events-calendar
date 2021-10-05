<?php
/**
 * Handles the generation and update of the Occurrences Provisional post ID.
 *
 * @since   TBD
 *
 * @package TEC\Custom_Tables\V1\Events\Provisional
 */

namespace TEC\Custom_Tables\V1\Events\Provisional;

/**
 * Class ID_Generator
 *
 * @since   TBD
 *
 * @package TEC\Custom_Tables\V1\Events\Provisional
 */
class ID_Generator {
	/**
	 * Save the initial value of the base, if the highest ID on the post table is close to the base retry until
	 * we set a base that's high enough.
	 *
	 * @since TBD
	 */
	public function install() {
		if ( $this->needs_change() ) {
			do {
				$this->update();
			} while ( $this->needs_change() );
		} else {
			update_option( $this->option_name(), $this->initial_base(), true );
		}
	}

	/**
	 * Remove the option from the options table.
	 *
	 * @since TBD
	 */
	public function uninstall() {
		delete_option( $this->option_name() );
	}

	/**
	 * If the highest ID on the DB is lower than the current value - threshold.
	 *
	 * @since TBD
	 * @return bool
	 */
	public function needs_change() {
		return $this->max_post_id() >= ( $this->current() - $this->threshold() );
	}

	/**
	 * Get the highest ID currently used on the database.
	 *
	 * @since TBD
	 * @return int The highest ID in use.
	 */
	public function max_post_id() {
		global $wpdb;

		return (int) $wpdb->get_var( "SELECT MAX(`ID`) FROM {$wpdb->posts}" );
	}

	/**
	 * Update the current base to current + base and save it into the options table.
	 *
	 * @since TBD
	 * @return bool
	 */
	public function update() {
		return update_option( $this->option_name(), $this->current() + $this->initial_base(), true );
	}

	/**
	 * Get the name of the option used to save the base of the provisional ID.
	 *
	 * @since TBD
	 * @return string
	 */
	public function option_name() {
		return 'tec_custom_tables_v1_provisional_post_base_provisional_id';
	}

	/**
	 * Get the current value from the options or fallback to the default value if it was not defined.
	 *
	 * @since TBD
	 * @return int
	 */
	public function current() {
		return (int) get_option( $this->option_name(), $this->initial_base() );
	}

	/**
	 * The padding we use to define if a base is not enough already as we subtract this value out of the current
	 * base in order to define if the current base should be updated or not.
	 *
	 * @since TBD
	 * @return int
	 */
	public function threshold() {
		/**
		 * Filters the threshold that will trigger the update of the provisional
		 * post ID base.
		 *
		 * @since TBD
		 *
		 * @param int $threshold The distance from the current provisional post
		 *                       base that will trigger, when reached by a real
		 *                       post ID, the update of the provisional post base.
		 */
		return (int) apply_filters( 'tec_custom_tables_v1_provisional_post_base_threshold', 50000 );
	}

	/**
	 * The initial number from where the provisional ID start.
	 *
	 * @since TBD
	 *
	 * @return int The initial provisional post base value.
	 */
	public function initial_base() {
		/**
		 * Filters the initial provisional post base value.
		 *
		 * @since TBD
		 *
		 * @param int $base The initial provisional post base value.
		 */
		return (int) apply_filters( 'tec_custom_tables_v1_provisional_post_base_initial', 10000000 );
	}
}
