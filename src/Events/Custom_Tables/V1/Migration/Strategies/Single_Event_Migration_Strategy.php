<?php
/**
 * The strategy used by The Events Calendar to migrate a Single Event, an Event
 * occurring once, to the Custom Tables v1 data format and tables.
 *
 * @since   TBD
 *
 * @package TEC\Events\Custom_Tables\V1\Migration\Strategies
 */

namespace TEC\Events\Custom_Tables\V1\Migration\Strategies;

use TEC\Events\Custom_Tables\V1\Migration\Reports\Event_Report;
use TEC\Events\Custom_Tables\V1\Updates\Events;
use TEC\Events\Custom_Tables\V1\Tables\Events as EventsSchema;
use TEC\Events\Custom_Tables\V1\Tables\Occurrences as OccurrencesSchema;

/**
 * Class Single_Event_Migration_Strategy.
 *
 * @since   TBD
 *
 * @package TEC\Events\Custom_Tables\V1\Migration\Strategies
 */
class Single_Event_Migration_Strategy implements Strategy_Interface {
	/**
	 * {@inheritDoc}
	 */
	public static function get_slug() {
		return 'tec-single-event-strategy';
	}

	/**
	 * Single_Event_Migration_Strategy constructor.
	 * since TBD
	 *
	 * @param int  $post_id The post ID of the Event to migrate.
	 * @param bool $dry_run Whether the migration should actually commit information,
	 *                      or run in dry-run mode.
	 *
	 * @return Event_Report A reference to the report for the Event migration.
	 */
	public function __construct( $post_id, $dry_run ) {
		$this->post_id = $post_id;
		$this->dry_run = $dry_run;
	}

	/**
	 * {@inheritDoc}
	 */
	public function apply( Event_Report $event_report ) {
		// @todo Add dry run logic
		// @todo Review - missing anything?
		$events_repository = tribe( Events::class );
		$events_repository->update( $this->post_id );

		// @todo how do we determine if there are tickets?
		return $event_report->add_strategy( self::get_slug() );
	}

	/**
	 * {@inheritDoc}
	 */
	public function undo( Event_Report $event_report ) {
		global $wpdb;
		$events_table      = EventsSchema::table_name( true );
		$occurrences_table = OccurrencesSchema::table_name( true );

		// Delete Event and Occurrences
		$delete_events_query = $wpdb->prepare( "DELETE FROM {$events_table} WHERE post_id = %s", $this->post_id );
		$wpdb->query( $delete_events_query );
		$delete_occurrences_query = $wpdb->prepare( "DELETE FROM {$occurrences_table} WHERE post_id = %s", $this->post_id );
		$wpdb->query( $delete_occurrences_query );

		// @todo Add failure tracking
		// @todo More to delete? Metadata?
		$meta_keys = [];
		foreach ( $meta_keys as $meta_key ) {
			delete_post_meta( $this->post_id, $meta_key );
		}

		return $event_report;
	}
}