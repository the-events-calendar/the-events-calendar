<?php
/**
 * Handles the generation and update of the Occurrences Provisional post ID.
 *
 * @since 6.0.0
 * @since TBD Migrated to The Events Calendar from Events Calendar Pro.
 *
 * @package TEC\Events\Custom_Tables\V1\Events\Provisional
 */

namespace TEC\Events\Custom_Tables\V1\Events\Provisional;

use TEC\Events\Custom_Tables\V1\Models\Occurrence;

/**
 * Class ID_Generator
 *
 * @since 6.0.0
 *
 * @package TEC\Events\Custom_Tables\V1\Events\Provisional
 */
class ID_Generator {
	/**
	 * Save the initial value of the base, if the highest ID on the post table is close to the base retry until
	 * we set a base that's high enough.
	 *
	 * @since 6.0.0
	 * @since 6.2.1 Removed the auto set to initial base, always find the value above the max post ID.
	 */
	public function install() {
		$this->sync_above_max_id();
	}

	/**
	 * Iterate on the base value, to find a valid provisional base number from the current max post ID in the wp_posts
	 * table.
	 *
	 * @since 6.2.1
	 */
	public function sync_above_max_id() {
		while ( $this->needs_change() ) {
			$this->update();
		}
	}

	/**
	 * Remove the option from the options table.
	 *
	 * @since 6.0.0
	 */
	public function uninstall() {
		delete_option( $this->option_name() );
	}

	/**
	 * If the highest ID on the DB is lower than the current value - threshold.
	 *
	 * @since 6.0.0
	 * @return bool
	 */
	public function needs_change() {
		return $this->max_post_id() >= ( $this->current() - $this->threshold() );
	}

	/**
	 * Get the highest ID currently used on the database.
	 *
	 * @since 6.0.0
	 * @return int The highest ID in use.
	 */
	public function max_post_id() {
		global $wpdb;

		return (int) $wpdb->get_var(
			$wpdb->prepare(
				'
			SELECT `AUTO_INCREMENT`
			FROM  INFORMATION_SCHEMA.TABLES
			WHERE TABLE_SCHEMA = DATABASE()
			  AND TABLE_NAME = %s',
				$wpdb->posts
			) 
		);
	}

	/**
	 * Update the current base to current + base and save it into the options table.
	 *
	 * @since 6.0.0
	 * @return bool
	 */
	public function update() {
		return update_option( $this->option_name(), $this->current() + $this->initial_base(), true );
	}

	/**
	 * Get the name of the option used to save the base of the provisional ID.
	 *
	 * @since 6.0.0
	 * @return string
	 */
	public function option_name() {
		return 'tec_custom_tables_v1_provisional_post_base_provisional_id';
	}

	/**
	 * Get the current value from the options or fallback to the default value if it was not defined.
	 *
	 * @since 6.0.0
	 * @since 6.2.1 The default value is now 0 instead of initial base.
	 *
	 * @return int
	 */
	public function current() {
		return (int) get_option( $this->option_name(), $this->initial_base() );
	}

	/**
	 * The padding we use to define if a base is not enough already as we subtract this value out of the current
	 * base in order to define if the current base should be updated or not.
	 *
	 * @since 6.0.0
	 * @return int
	 */
	public function threshold() {
		/**
		 * Filters the threshold that will trigger the update of the provisional
		 * post ID base.
		 *
		 * @since 6.0.0
		 *
		 * @param int $threshold The distance from the current provisional post
		 *                       base that will trigger, when reached by a real
		 *                       post ID, the update of the provisional post base.
		 */
		return (int) apply_filters( 'tec_events_pro_custom_tables_v1_provisional_post_base_threshold', 50000 );
	}

	/**
	 * The initial number from where the provisional ID start.
	 *
	 * @since 6.0.0
	 *
	 * @return int The initial provisional post base value.
	 */
	public function initial_base() {
		/**
		 * Filters the initial provisional post base value.
		 *
		 * @since 6.0.0
		 *
		 * @param int $base The initial provisional post base value.
		 */
		return (int) apply_filters( 'tec_events_pro_custom_tables_v1_provisional_post_base_initial', 10000000 );
	}

	/**
	 * Returns the provisional post ID, based on the current base, for an Occurrence.
	 *
	 * @since 6.0.0
	 *
	 * @param int|Occurrence $occurrence_id Either an Occurrence `occurrence_id`, or
	 *                                      a reference to the Occurrence object to create
	 *                                      provide the provisional ID for.
	 *
	 * @return int The provisional ID for the Occurrence.
	 */
	public function provide_id( $occurrence_id ) {
		$baseless_id = $this->unprovide_id( $occurrence_id );

		if ( ! $baseless_id ) {
			return 0;
		}

		$current = $this->current();

		return $current + $baseless_id;
	}

	/**
	 * Returns an Occurrence `occurrence_id` from the Occurrence provisional post ID.
	 *
	 * @since 6.0.0
	 *
	 * @param int $occurrence_id The Occurrence provisional post ID.
	 *
	 * @return int The Occurrence `occurrence_id`, without the provisional post base
	 */
	public function unprovide_id( $occurrence_id ) {
		if ( $occurrence_id instanceof Occurrence ) {
			$occurrence_id = $occurrence_id->occurrence_id;
		}

		if ( empty( $occurrence_id ) ) {
			return 0;
		}

		$current = $this->current();
		return $occurrence_id > $current ? $occurrence_id - $current : $occurrence_id;
	}
}
