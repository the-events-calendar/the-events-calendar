<?php

namespace Tribe\Events\Tests\Factories;

use Tribe__Events__Main as Main;

class Event extends \WP_UnitTest_Factory_For_Post {

	/**
	 * Inserts an event in the database.
	 *
	 * @param array $args      An array of values to override the default arguments.
	 *                         Keep in mind `tax_input` and `meta_input` to bake terms and custom fields in.
	 *                         Notable arguments:
	 *                         `when` - by default events will happen in 24hrs; set this to a different hour offset
	 *                         to have them happen at a different time.
	 *                         `duration` - by defautl events will last for 2hrs; set this to a different duration
	 *                         in seconds if required.
	 *                         `utc_offset` - by default events will happen on UTC time; set this to a different hour
	 *                         offset if required.
	 *                         `venue` - set this to a venue post ID
	 *                         `organizers` - set this to an array of organizer post IDs
	 *
	 * @return int The generated event post ID
	 */
	function create_object( $args = array() ) {
		$args['post_type'] = Main::POSTTYPE;
		$args['post_status'] = isset( $args['post_status'] ) ? $args['post_status'] : 'publish';
		// by default an event will happen tomorrow
		$when = isset( $args['when'] ) ? $args['when'] : '+24 hours';
		// by default an event will last 2hrs
		$duration = isset( $args['duration'] ) ? $args['duration'] : '7200';
		// by default an event will be on UTC time
		$utc_offset = isset( $args['utc_offset'] ) ? $args['utc_offset'] : 0;

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

		if ( isset( $args['venue'] ) ) {
			$args['meta_input']['_EventVenueID'] = $args['venue'];
			unset( $args['venue'] );
		}

		if ( isset( $args['organizers']) || isset($args['organizer']) ) {
			$organizers = isset($args['organizers'])
				? (array)$args['organizers']
				: (array)$args['organizer'];
			unset( $args['organizers'] );
		}

		unset( $args['when'], $args['duration'], $args['utc_offset'] );

		$id = uniqid();
		$defaults = [
			'post_type'  => Main::POSTTYPE,
			'post_title' => "Event {$id}",
			'post_name'  => "event-{$id}",
			'meta_input' => isset( $args['meta_input'] ) ? array_merge( $meta_input, $args['meta_input'] ) : $meta_input,
		];

		unset( $args['meta_input'] );

		$args = array_merge( $defaults, $args );

		$id = parent::create_object( $args );

		if ( ! empty( $organizers ) ) {
			foreach ( $organizers as $organizer ) {
				add_post_meta( $id, '_EventOrganizerID', $organizer );
			}
		}

		return $id;
	}

	/**
	 * Inserts many events in the database.
	 *
	 * @param      int $count The number of events to insert.
	 * @param array    $args  An array of arguments to override the defaults (see `haveEventInDatabase`),
	 *                        `time_space` - A positive amount of hours that should separate the events; by default the events
	 *                        will happen spaced one hour from each other.
	 * @param array    $generation_definitions
	 *
	 * @return array An array of generated event post IDs.
	 */
	function create_many( $count, $args = array(), $generation_definitions = null ) {
		$ids = [];
		$time = empty( $args['time_space'] ) ? 1 : $args['time_space'];
		for ( $n = 0; $n < $count; $n ++ ) {
			$event_args = $args;
			if ( ! empty( $time ) ) {
				$event_args['when'] = '+' . $time . ' hours';
				$time += $time;
			}
			$ids[] = $this->create_object( $event_args );
		}

		return $ids;
	}
}