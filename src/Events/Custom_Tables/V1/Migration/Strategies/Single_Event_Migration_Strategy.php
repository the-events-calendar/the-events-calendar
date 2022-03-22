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

use TEC\Events\Custom_Tables\V1\Migration\Migration_Exception;
use TEC\Events\Custom_Tables\V1\Migration\Reports\Event_Report;
use TEC\Events\Custom_Tables\V1\Models\Event;
use TEC\Events\Custom_Tables\V1\Models\Occurrence;
use TEC\Events\Custom_Tables\V1\Tables\Events as EventsSchema;
use TEC\Events\Custom_Tables\V1\Tables\Occurrences as OccurrencesSchema;
use TEC\Events\Custom_Tables\V1\Traits\With_Database_Transactions;
use Tribe__Events__Main as TEC;

/**
 * Class Single_Event_Migration_Strategy.
 *
 * @since   TBD
 *
 * @package TEC\Events\Custom_Tables\V1\Migration\Strategies
 */
class Single_Event_Migration_Strategy implements Strategy_Interface {
	use With_Database_Transactions;

	/**
	 * {@inheritDoc}
	 */
	public static function get_slug() {
		return 'tec-single-event-strategy';
	}

	/**
	 * Single_Event_Migration_Strategy constructor.
	 *
	 * @since TBD
	 *
	 * @param int  $post_id The post ID of the Event to migrate.
	 * @param bool $dry_run Whether the migration should actually commit information,
	 *                      or run in dry-run mode.
	 *
	 * @return Event_Report A reference to the report for the Event migration.
	 *
	 * @throws Migration_Exception If the post is not of the Event type.
	 */
	public function __construct( $post_id, $dry_run ) {
		$this->post_id = $post_id;
		if ( TEC::POSTTYPE !== get_post_type( $post_id ) ) {
			throw new Migration_Exception( 'Post is not an Event.' );
		}
		$this->dry_run = $dry_run;
	}

	/**
	 * {@inheritDoc}
	 */
	public function apply( Event_Report $event_report ) {
		if ( $this->dry_run ) {
			$this->transaction_start();
		}

		$upserted = Event::upsert( [ 'post_id' ], Event::data_from_post( $this->post_id ) );

		if ( $upserted === false ) {
			throw new Migration_Exception( 'Event model could not be upserted. Could have failed locating required data for insertion.' );
		}

		$event_model = Event::find( $this->post_id, 'post_id' );

		if ( ! $event_model instanceof Event ) {
			throw new Migration_Exception( 'Event model could not be found.' );
		}

		$event_model->occurrences()->save_occurrences();

		$occurrences = Occurrence::where('post_id','=',$this->post_id)
			->count();

		if ( $occurrences !== 1 ) {
			throw new Migration_Exception(
				sprintf(
					'Unexpected number of Occurrences found: expected 1, found %d.',
					$occurrences
				)
			);
		}

		if ( $this->dry_run ) {
			$this->transaction_rollback();
		}

		// @todo how do we determine if there are tickets?
		return $event_report->add_strategy( self::get_slug() )
		                    ->migration_success();
	}

}