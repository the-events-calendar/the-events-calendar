<?php

namespace TEC\Events\Custom_Tables\V1;

use TEC\Events\Custom_Tables\V1\Activation;
use TEC\Events\Custom_Tables\V1\Migration\State;
use TEC\Events\Custom_Tables\V1\Tables\Events;
use TEC\Events\Custom_Tables\V1\Tables\Occurrences;
use Tribe__Events__Main;

/**
 * Class to do some inspection on the migration and database state of the Custom Tables.
 *
 * @package TEC\Events\Custom_Tables\V1
 */
class Health_Check {

	/**
	 * @since 6.0.9
	 *
	 * @var State The state object.
	 */
	protected $migration_state;

	/**
	 * Construct the Migration Health Check object.
	 *
	 * @since 6.0.9
	 *
	 * @param State $state The migration state instance.
	 */
	public function __construct( State $state ) {
		$this->migration_state = $state;
	}

	/**
	 * Checks if something is missing or malformed in the data.
	 *
	 * @since 6.0.9
	 *
	 * @return bool Checks if something is missing or malformed in the data.
	 */
	public function is_event_data_healthy(): bool {
		global $wpdb;
		if ( $this->migration_state->is_migrated() ) {
			$event_table = Events::table_name();
			$event_count = $wpdb->get_var( "SELECT COUNT(*) FROM $event_table" );
			$query       = $wpdb->prepare( "SELECT COUNT(*) FROM $wpdb->posts WHERE post_type= %s", Tribe__Events__Main::POSTTYPE );
			$posts_count = $wpdb->get_var( $query );

			if ( ! is_numeric( $posts_count ) || ! is_numeric( $event_count ) ) {
				return false; // Something went wrong.
			}

			// We have posts but no events?
			return ( $posts_count > 0 && $event_count > 0 ) || ( $posts_count == 0 && $event_count == 0 );
		}

		return true;
	}

	/**
	 * Checks if something is missing or malformed in the data.
	 *
	 * @since 6.0.9
	 *
	 * @return bool Checks if something is missing or malformed in the data.
	 */
	public function is_occurrence_data_healthy(): bool {
		global $wpdb;
		if ( $this->migration_state->is_migrated() ) {
			$occurrences_table = Occurrences::table_name();
			$occurrences_count = $wpdb->get_var( "SELECT COUNT(*) FROM $occurrences_table" );
			$query             = $wpdb->prepare( "SELECT COUNT(*) FROM $wpdb->posts WHERE post_type= %s", Tribe__Events__Main::POSTTYPE );
			$posts_count       = $wpdb->get_var( $query );

			if ( ! is_numeric( $posts_count ) || ! is_numeric( $occurrences_count ) ) {
				return false; // Something went wrong.
			}

			// We have posts but no occurrences?
			return ( $posts_count > 0 && $occurrences_count > 0 ) || ( $posts_count == 0 && $occurrences_count == 0 );
		}

		return true;
	}

	/**
	 *  Whether the table should exist but doesn't. It considers activation state.
	 *
	 * @since 6.0.9
	 *
	 * @return bool Whether the table should exist but doesn't. It considers activation state.
	 */
	public function is_event_table_missing(): bool {
		global $wpdb;

		// No activation / schema attempt ran? We wouldn't have the table yet.
		if ( tec_timed_option()->get( Activation::ACTIVATION_TRANSIENT ) === null ) {
			return false;
		}

		$table = Events::table_name();

		// Just query the table - if it doesn't exist it will not return a numeric value.
		// It SHOULD exist here.
		$count = $wpdb->get_var( "SELECT COUNT(*) FROM $table" );

		return ! is_numeric( $count );
	}

	/**
	 *  Whether the table should exist but doesn't. It considers activation state.
	 *
	 * @since 6.0.9
	 *
	 * @return bool Whether the table should exist but doesn't. It considers activation state.
	 */
	public function is_occurrence_table_missing(): bool {
		global $wpdb;

		// No activation / schema attempt ran? We wouldn't have the table yet.
		if ( tec_timed_option()->get( Activation::ACTIVATION_TRANSIENT ) === null ) {
			return false;
		}

		$table = Occurrences::table_name();

		// Just query the table - if it doesn't exist it will not return a numeric value.
		// It SHOULD exist here.
		$count = $wpdb->get_var( "SELECT COUNT(*) FROM $table" );

		return ! is_numeric( $count );
	}
}
