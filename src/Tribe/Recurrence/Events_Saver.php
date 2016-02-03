<?php


class Tribe__Events__Pro__Recurrence__Events_Saver {

	/**
	 * @var int
	 */
	protected $event_id;
	/**
	 * @var bool|int
	 */
	protected $updated;

	/**
	 * @var Tribe__Events__Pro__Recurrence__Exclusions
	 */
	protected $exclusions;

	/**
	 * Tribe__Events__Pro__Recurrence__Events_Saver constructor.
	 *
	 * @param int                                             $event_id The post ID of the event being saved
	 * @param bool|int                                        $updated  The meta_id of the post meta containing the
	 *                                                                  event recurrence meta information.
	 * @param Tribe__Events__Pro__Recurrence__Exclusions|null $exclusions
	 */
	public function __construct( $event_id, $updated, Tribe__Events__Pro__Recurrence__Exclusions $exclusions = null ) {
		$this->event_id        = $event_id;
		$this->updated         = $updated;
		$event_timezone_string = $this->get_event_timezone_string( $event_id );
		$this->exclusions = $exclusions ?
			$exclusions :
			Tribe__Events__Pro__Recurrence__Exclusions::instance( $event_timezone_string );
	}

	/**
	 * Do the actual work of saving a recurring series of events
	 *
	 * @return void
	 */
	public function save_events() {
		$existing_instances = Tribe__Events__Pro__Recurrence__Children_Events::instance()->get_ids( $this->event_id );

		$recurrences = Tribe__Events__Pro__Recurrence__Meta::get_recurrence_for_event( $this->event_id );

		$to_create             = array();
		$exclusions            = array();
		$to_update             = array();
		$to_delete             = array();
		$possible_next_pending = array();
		$earliest_date         = strtotime( Tribe__Events__Pro__Recurrence__Meta::$scheduler->get_earliest_date() );
		$latest_date           = strtotime( Tribe__Events__Pro__Recurrence__Meta::$scheduler->get_latest_date() );

		foreach ( $recurrences['rules'] as &$recurrence ) {
			if ( ! $recurrence ) {
				continue;
			}
			$recurrence->setMinDate( $earliest_date );
			$recurrence->setMaxDate( $latest_date );
			$to_create = array_merge( $to_create, $recurrence->getDates() );

			if ( $recurrence->constrainedByMaxDate() !== false ) {
				$possible_next_pending[] = $recurrence->constrainedByMaxDate();
			}
		}

		$to_create = tribe_array_unique( $to_create );

		// find days we should exclude
		foreach ( $recurrences['exclusions'] as &$recurrence ) {
			if ( ! $recurrence ) {
				continue;
			}

			$recurrence->setMinDate( $earliest_date );
			$recurrence->setMaxDate( $latest_date );
			$exclusions = array_merge( $exclusions, $recurrence->getDates() );
		}

		// make sure we don't create excluded dates
		$exclusions = tribe_array_unique( $exclusions );
		$to_create  = $this->exclusions->remove_exclusions( $to_create, $exclusions );

		if ( $possible_next_pending ) {
			update_post_meta(
				$this->event_id, '_EventNextPendingRecurrence', date(
				Tribe__Events__Pro__Date_Series_Rules__Rules_Interface::DATE_FORMAT, min( $possible_next_pending )
			)
			);
		}

		foreach ( $existing_instances as $instance ) {
			$start_date = strtotime( get_post_meta( $instance, '_EventStartDate', true ) . '+00:00' );
			$end_date   = strtotime( get_post_meta( $instance, '_EventEndDate', true ) . '+00:00' );
			$duration   = $end_date - $start_date;

			$existing_date_duration = array(
				'timestamp' => $start_date, 'duration' => $duration,
			);

			$found              = array_search( $existing_date_duration, $to_create );
			$should_be_excluded = in_array( $existing_date_duration, $exclusions );

			if ( $found === false || false !== $should_be_excluded ) {
				$to_delete[ $instance ] = $existing_date_duration;
			} else {
				$to_update[ $instance ] = $to_create[ $found ];
				unset( $to_create[ $found ] ); // so we don't re-add it
			}
		}

		// Store the list of instances to create/update/delete etc for future processing
		$queue = new Tribe__Events__Pro__Recurrence__Queue( $this->event_id );
		$queue->update( $to_create, $to_update, $to_delete, $exclusions );

		// ...but don't wait around, process a small initial batch right away
		Tribe__Events__Pro__Main::instance()->queue_processor->process_batch( $this->event_id );
	}

	/**
	 * Gets the timezone string associated with an event.
	 *
	 * Will fall back to WP timezone string and to system timezone string in this order.
	 *
	 * @param $event_id
	 *
	 * @return mixed|string|void
	 */
	protected function get_event_timezone_string( $event_id ) {
		$event_timezone_string = get_post_meta( $event_id, '_EventTimezone', true );
		if ( empty( $event_timezone_string ) ) {
			$event_timezone_string = get_option( 'timezone_string', false );
			if ( empty( $event_timezone_string ) ) {
				$event_timezone_string = date_default_timezone_get();

				return $event_timezone_string;
			}

			return $event_timezone_string;
		}

		return $event_timezone_string;
	}//end saveEvents
}