<?php
/**
 * Class responsible for top level database transactions, regarding changes
 * to Events and their related database entries/tables.
 *
 * @since   6.0.0
 *
 * @package TEC\Events\Custom_Tables\V1\Updates
 */

namespace TEC\Events\Custom_Tables\V1\Updates;

use DateTimeZone;
use Exception;
use TEC\Events\Custom_Tables\V1\Models\Builder;
use TEC\Events\Custom_Tables\V1\Models\Event;
use TEC\Events\Custom_Tables\V1\Models\Occurrence;
use TEC\Events\Custom_Tables\V1\Tables\Occurrences;
use Tribe__Events__Main as TEC;
use Tribe__Date_Utils as Dates;

/**
 * Class Events
 *
 * @since   6.0.0
 *
 * @package TEC\Events\Custom_Tables\V1\Updates
 */
class Events {

	/**
	 * Updates an Event by post ID.
	 *
	 * @since 6.0.0
	 *
	 * @param int $post_id The Event post ID.
	 *
	 * @return bool Whether the update was correctly performed or not.
	 */
	public function update( $post_id ) {
		// Make sure to update the real thing.
		$post_id = Occurrence::normalize_id( $post_id );

		if ( TEC::POSTTYPE !== get_post_type( $post_id ) ) {
			return false;
		}

		$event_data = Event::data_from_post( $post_id );
		$upsert     = Event::upsert( [ 'post_id' ], $event_data );

		if ( $upsert === false ) {
			// At this stage the data might just be missing: it's fine.
			return false;
		}

		// Show when an event is updated versus inserted
		if ( $upsert === Builder::UPSERT_DID_INSERT ) {
			/**
			 * When we have created a new event, fire this action with the post ID.
			 *
			 * @since 6.0.0
			 *
			 * @param numeric $post_id The event post ID.
			 */
			do_action( 'tec_events_custom_tables_v1_after_insert_event', $post_id );
		} else {
			/**
			 * When we have updated an existing event, fire this action with the post ID.
			 *
			 * @since 6.0.0
			 *
			 * @param numeric $post_id The event post ID.
			 */
			do_action( 'tec_events_custom_tables_v1_after_update_event', $post_id );
		}

		$event = Event::find( $post_id, 'post_id' );

		if ( ! $event instanceof Event ) {
			do_action( 'tribe_log', 'error', 'Event fetching after insertion failed.', [
				'source'  => __CLASS__,
				'slug'    => 'fetch-after-upsert',
				'post_id' => $post_id,
			] );

			return false;
		}

		try {
			$occurrences = $event->occurrences();
			$occurrences->save_occurrences();
		} catch ( Exception $e ) {
			do_action( 'tribe_log', 'error', 'Event Occurrence update failed.', [
				'source'  => __CLASS__,
				'slug'    => 'update-occurrences',
				'post_id' => $post_id,
				'error'   => $e->getMessage(),
			] );

			return false;
		}

		return true;
	}

	/**
	 * Deletes an Event and related data from the custom tables.
	 *
	 * @since 6.0.0
	 *
	 * @param int $post_id The Event post ID.
	 *
	 * @return int|false Either the number of affected rows, or `false` to
	 *                   indicate a failure.
	 */
	public function delete( $post_id ) {
		if ( TEC::POSTTYPE !== get_post_type( $post_id ) ) {
			// Not an Event post.
			return false;
		}

		$affected = Event::where( 'post_id', (int) $post_id )->delete();
		$affected += Occurrence::where( 'post_id', $post_id )->delete();

		return $affected;
	}

	/**
	 * Rebuilds the known Events dates range setting the values of the options
	 * used to track the earliest Event start date and the latest Event end date.
	 *
	 * @since 6.0.0
	 * @since 6.0.13 Fix for "markers" being computed incorrectly, and only fetching provisional IDs.
	 *
	 * @return true To indicate the earliest and latest Event dates were updated.
	 */
	public function rebuild_known_range() {
		$earliest = $this->get_earliest_occurrence();
		$latest   = $this->get_latest_occurrence();

		if ( $earliest ) {
			tribe_update_option( 'earliest_date', $earliest->start_date_utc );
			tribe_update_option( 'earliest_date_markers', [ $earliest->provisional_id ?? $earliest->post_id ] );
		} else {
			tribe_remove_option( 'earliest_date' );
			tribe_remove_option( 'earliest_date_markers' );
		}

		if ( $latest ) {
			tribe_update_option( 'latest_date', $latest->end_date_utc );
			tribe_update_option( 'latest_date_markers', [ $latest->provisional_id ?? $latest->post_id ] );
		} else {
			tribe_remove_option( 'latest_date' );
			tribe_remove_option( 'latest_date_markers' );
		}

		return true;
	}

	/**
	 * Get the earliest "valid" occurrence in the database.
	 *
	 * @since 6.0.13
	 *
	 * @param array|string|null $stati An array of post statuses to filter the occurrences for.
	 *
	 * @return Occurrence|null
	 */
	private function get_earliest_occurrence( $stati = null ): ?Occurrence {
		global $wpdb;
		$occurrences = Occurrences::table_name( true );
		if ( empty( $stati ) ) {
			/**
			 * @see \Tribe__Events__Dates__Known_Range::rebuild_known_range() for documentation.
			 */
			$stati = apply_filters( 'tribe_events_known_range_stati', [ 'publish', 'private', 'protected' ] );
		}
		$statuses       = $wpdb->prepare( implode( ',', array_fill( 0, count( (array) $stati ), '%s' ) ), (array) $stati );
		$query          = $wpdb->prepare( "SELECT o.* FROM $occurrences o
			JOIN $wpdb->posts p ON p.ID = o.post_id
			WHERE p.post_status IN ($statuses)
				AND p.post_type = %s
			ORDER BY start_date_utc ASC
			LIMIT 1",
			TEC::POSTTYPE
		);
		$occurrence_row = $wpdb->get_row( $query, ARRAY_A );

		return ! empty( $occurrence_row ) ? new Occurrence( (array) $occurrence_row ) : null;
	}

	/**
	 * Get the latest "valid" occurrence in the database.
	 *
	 * @since 6.0.13
	 *
	 * @param array|string|null $stati An array of post statuses to filter the occurrences for.
	 *
	 * @return Occurrence|null
	 */
	private function get_latest_occurrence( $stati = null ): ?Occurrence {
		global $wpdb;
		$occurrences = Occurrences::table_name( true );
		if ( empty( $stati ) ) {
			/**
			 * @see \Tribe__Events__Dates__Known_Range::rebuild_known_range() for documentation.
			 */
			$stati = apply_filters( 'tribe_events_known_range_stati', [ 'publish', 'private', 'protected' ] );
		}
		$statuses       = $wpdb->prepare( implode( ',', array_fill( 0, count( (array) $stati ), '%s' ) ), (array) $stati );
		$query          = $wpdb->prepare( "SELECT o.* FROM $occurrences o
			JOIN $wpdb->posts p ON p.ID = o.post_id
			WHERE p.post_status IN ($statuses)
				AND p.post_type = %s
			ORDER BY end_date_utc DESC
			LIMIT 1",
			TEC::POSTTYPE
		);
		$occurrence_row = $wpdb->get_row( $query, ARRAY_A );

		return ! empty( $occurrence_row ) ? new Occurrence( (array) $occurrence_row ) : null;
	}

	/**
	 * Returns the earliest Event start date in the database.
	 *
	 * @since 6.0.0
	 *
	 * @param string|array|null $stati A post status, or a set of post statuses, to fetch
	 *                                 the earliest date for; or `null` to use the default
	 *                                 set of statuses.
	 *
	 * @return \DateTime The earliest start time object, in the site timezone.
	 */
	public function get_earliest_date( $stati = null ) {
		$occurrence = $this->get_earliest_occurrence( $stati );
		$date       = $occurrence ? $occurrence->start_date_utc : null;

		return Dates::build_date_object( $date, new DateTimeZone( 'UTC' ) );
	}

	/**
	 * Returns the latest Event start date in the database.
	 *
	 * @since 6.0.0
	 *
	 * @param string|array|null $stati A post status, or a set of post statuses, to fetch
	 *                                 the latest date for; or `null` to use the default
	 *                                 set of statuses.
	 *
	 * @return \DateTime The latest start time object, in the site timezone.
	 */
	public function get_latest_date( $stati = null ) {
		$occurrence = $this->get_latest_occurrence( $stati );
		$date       = $occurrence ? $occurrence->end_date_utc : null;

		return Dates::build_date_object( $date, new DateTimeZone( 'UTC' ) );
	}
}
