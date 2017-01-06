<?php

namespace Helper;

use Codeception\Module\WPDb;

class TribeDb extends \Codeception\Module {

	/**
	 * @var WPDb
	 */
	protected $db;

	public function _initialize() {
		$this->db = $this->getModule( 'WPDb' );
	}

	/**
	 * Gets an option from the tribe option database row.
	 *
	 * @param       string $key
	 * @param mixed        $default
	 *
	 * @return mixed
	 */
	public function getTribeOptionFromDatabase( $key, $default = '' ) {
		$options = $this->db->grabOptionFromDatabase( 'tribe_events_calendar_options' );
		if ( empty( $options ) ) {
			return $default;
		}

		return isset( $options[ $key ] ) ? $options[ $key ] : $default;
	}

	/**
	 * Sets an option in the tribe option row.
	 *
	 * @param string       $key
	 * @param string|array $value
	 */
	public function setTribeOption( $key, $value ) {
		$option_name = 'tribe_events_calendar_options';
		$options = $this->db->grabOptionFromDatabase( $option_name );
		if ( empty( $options ) ) {
			$this->db->haveOptionInDatabase( $option_name, [ $key => $value ] );
		} else {
			$this->db->haveOptionInDatabase( $option_name, array_merge( $options, [ $key => $value ] ) );
		}
	}

	/**
	 * Inserts an event in the database.
	 *
	 * @param array $overrides An array of values to override the default arguments.
	 *                         Keep in mind `tax_input` and `meta_input` to bake terms and custom fields in.
	 *                         Notable arguments:
	 *                         `when` - by default events will happen in 24hrs; set this to a different hour offset
	 *                         to have them happen at a different time in the format `+4 hours`
	 *                         `duration` - by defautl events will last for 2hrs; set this to a different duration
	 *                         in seconds if required.
	 *                         `utc_offset` - by default events will happen on UTC time; set this to a different hour
	 *                         offset if required.
	 *
	 * @return int The generated event post ID
	 */
	public function haveEventInDatabase( array $overrides = [] ) {
		// by default an event will happen tomorrow
		$when = isset( $overrides['when'] ) ? $overrides['when'] : '+24 hours';
		// by default an event will last 2hrs
		$duration = isset( $overrides['duration'] ) ? $overrides['duration'] : '7200';
		// by default an event will be on UTC time
		$utc_offset = isset( $overrides['utc_offset'] ) ? $overrides['utc_offset'] : 0;

		$start_time = strtotime( $when );
		$end_time = strtotime( $when ) + $duration;

		$start = date( 'Y-m-d H:i:s', $start_time );
		$utc_start = date( 'Y-m-d H:i:s', $start_time + $utc_offset * 60 );
		$end = date( 'Y-m-d H:i:s', $end_time );
		$utc_end = date( 'Y-m-d H:i:s', $end_time + $utc_offset * 60 );

		$meta_input = [
			'_EventStartDate'    => $utc_start,
			'_EventEndDate'      => $utc_end,
			'_EventStartDateUTC' => $start,
			'_EventEndDateUTC'   => $end,
			'_EventDuration'     => $duration,
		];

		unset( $overrides['when'], $overrides['duration'], $overrides['utc_offset'] );

		$id = uniqid();
		$defaults = [
			'post_type'  => 'tribe_events',
			'post_title' => "Event {$id}",
			'post_name'  => "event-{$id}",
			'meta_input' => isset( $overrides['meta_input'] ) ? array_merge( $meta_input, $overrides['meta_input'] ) : $meta_input,
		];

		unset( $overrides['meta_input'] );

		return $this->db->havePostInDatabase( array_merge( $defaults, $overrides ) );
	}

	/**
	 * Inserts many events in the database.
	 *
	 * @param      int $count      The number of events to insert.
	 * @param array    $overrides  An array of arguments to override the defaults (see `haveEventInDatabase`)
	 * @param int      $time_space A positive amount of hours that should separate the events; by default the events
	 *                             will happen spaced one hour from each other.
	 *
	 * @return array An array of generated event post IDs.
	 */
	public function haveManyEventsInDatabase( $count, array $overrides = [], $time_space = null ) {
		$ids = [];
		$time = empty( $time_space ) ? 1 : $time_space;
		for ( $n = 0; $n < $count; $n ++ ) {
			$event_overrides = $overrides;
			if ( ! empty( $time_space ) ) {
				$event_overrides['when'] = '+' . $time . ' hours';
				$time += $time_space;
				$event_overrides['post_title'] = "Event {$n}";
				$event_overrides['post_name'] = "event-{$n}";
			}
			$ids[] = $this->haveEventInDatabase( $event_overrides );
		}

		return $ids;
	}
}