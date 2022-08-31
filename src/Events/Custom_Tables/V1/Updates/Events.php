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
	 *
	 * @return true To indicate the earliest and latest Event dates were updated.
	 */
	public function rebuild_known_range() {
		tribe_update_option( 'earliest_date', $this->get_earliest_date()->format( Dates::DBDATETIMEFORMAT ) );
		tribe_update_option( 'latest_date', $this->get_latest_date()->format( Dates::DBDATETIMEFORMAT ) );
		$earliest = Occurrence::order_by( 'start_date_utc', 'ASC' )->first();
		$latest = Occurrence::order_by( 'end_date_utc', 'DESC' )->first();
		tribe_update_option( 'earliest_date_markers', $earliest instanceof Occurrence ? [ $earliest->provisional_id ] : [] );
		tribe_update_option( 'latest_date_markers', $latest instanceof Occurrence ? [ $latest->provisional_id ] : [] );

		return true;
	}

	/**
	 * Fetches an aggregate date value from the database.
	 *
	 * @since 6.0.0
	 * @param string            $aggregate The SQL aggregate function to use, e.g. `MIN` or `MAX`.
	 * @param string            $column    The column to use the aggregate function on.
	 * @param array|string|null $stati     An array of post statuses to return the aggregate column for.
	 *
	 * @return \DateTime|false|\Tribe\Utils\Date_I18n
	 */
	private function get_boundary_date( $aggregate, $column, $stati = null ) {
		global $wpdb;
		$occurrences = Occurrences::table_name( true );
		if ( empty( $stati ) ) {
			/**
			 * @see \Tribe__Events__Dates__Known_Range::rebuild_known_range() for documentation.
			 */
			$stati = apply_filters( 'tribe_events_known_range_stati', [ 'publish', 'private', 'protected' ] );
		}
		$statuses = $wpdb->prepare( implode( ',', array_fill( 0, count( (array) $stati ), '%s' ) ), (array) $stati );
		$date     = $wpdb->get_var( "SELECT {$aggregate}(o.{$column}) FROM $occurrences o
			JOIN $wpdb->posts p ON p.ID = o.post_id
			WHERE p.post_status IN ($statuses)"
		);

		return Dates::build_date_object( $date, new \DateTimeZone( 'UTC' ) );
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
		return $this->get_boundary_date( 'MIN', 'start_date_utc', $stati );
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
		return $this->get_boundary_date( 'MAX', 'end_date_utc', $stati );
	}
}