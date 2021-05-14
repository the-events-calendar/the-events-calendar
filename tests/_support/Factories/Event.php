<?php

namespace Tribe\Events\Test\Factories;

use Tribe__Date_Utils as Dates;
use Tribe__Events__Main as Main;
use Tribe__Timezones as Timezones;
use Tribe__Utils__Array as Arr;

class Event extends \WP_UnitTest_Factory_For_Post {

	/**
	 * Returns a fluent event instance to start building an event meta information
	 * using readable syntax.
	 *
	 * @param string $start_date The event start date in string form.
	 *
	 * @return Fluent_Event The "head" of the fluent event.
	 */
	public function starting_on( string $start_date ) {
		$fluent_event = new Fluent_Event( $start_date );
		$fluent_event->set_factory($this);

		return $fluent_event;
	}

	/**
	 * Inserts an event in the database.
	 *
	 * @param array $args      An array of values to override the default arguments.
	 *                         Keep in mind `tax_input` and `meta_input` to bake terms and custom fields in.
	 *                         Notable arguments:
	 *                         `when` - by default events will happen in 24hrs; set this to a different hour offset
	 *                         to have them happen at a different time.
	 *                         `duration` - by default events will last for 2hrs; set this to a different duration
	 *                         in seconds if required.
	 *                         `utc_offset` - by default events will happen on UTC time; set this to a different hour
	 *                         offset if required.
	 *                         `venue` - set this to a venue post ID
	 *                         `organizers` - set this to an array of organizer post IDs
	 *
	 * @return int The generated event post ID
	 */
	function create_object( $args = array() ) {
		$args['post_type']   = $this->get_post_type();
		$args['post_status'] = Arr::get( $args, 'post_status', 'publish' );
		// By default an event will happen tomorrow.
		$utc_start_time = Arr::get( $args, 'when', '+24 hours' );
		// By default an event will last 2hrs.
		$duration = Arr::get( $args, 'duration', '7200' );
		// By default an event will be on UTC time.
		$utc_offset = Arr::get( $args, 'utc_offset', 0 );
		$timezone   = Arr::get( $args, 'timezone', Timezones::build_timezone_object()->getName() );

		$utc = Timezones::build_timezone_object( 'UTC' );
		$timezone_obj = Timezones::build_timezone_object($timezone);

		$start_timestamp = is_numeric( $utc_start_time )
			? $utc_start_time
			: Dates::build_date_object( $utc_start_time, $utc )->getTimestamp();
		$end_timestamp   = $start_timestamp + $duration;

		$utc_start   = Dates::build_date_object( $start_timestamp )
		                    ->setTimezone( $utc )->format( Dates::DBDATETIMEFORMAT );
		$utc_end     = Dates::build_date_object( $end_timestamp )
		                    ->setTimezone( $utc )->format( Dates::DBDATETIMEFORMAT );

		if ( isset( $args['utc_offset'] ) ) {
			$local_start = Dates::build_date_object( $start_timestamp + $utc_offset * HOUR_IN_SECONDS )
			                    ->setTimezone( $utc )->format( Dates::DBDATETIMEFORMAT );
			$local_end   = Dates::build_date_object( $end_timestamp + $utc_offset * HOUR_IN_SECONDS )
			                    ->setTimezone( $utc )->format( Dates::DBDATETIMEFORMAT );
		} else {
			// Use the timezone to create the "local" (to the site) times.
			$local_start = Dates::build_date_object( $start_timestamp, $timezone_obj )->format( Dates::DBDATETIMEFORMAT );
			$local_end   = Dates::build_date_object( $end_timestamp, $timezone_obj )->format( Dates::DBDATETIMEFORMAT );
		}

		$meta_input = [
			'_EventStartDate'    => $local_start,
			'_EventEndDate'      => $local_end,
			'_EventStartDateUTC' => $utc_start,
			'_EventEndDateUTC'   => $utc_end,
			'_EventDuration'     => $duration,
			'_EventTimezone'     => $timezone,
			'_EventTimezoneAbbr' => Timezones::abbr( $local_start, $timezone ),
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

		$id = uniqid( 'test_event', true );
		$defaults = [
			'post_type'  => $this->get_post_type(),
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
		$next_time = $time = empty( $args['time_space'] ) ? 1 : $args['time_space'];
		for ( $n = 0; $n < $count; $n ++ ) {
			$event_args = $args;
			if ( ! empty( $next_time ) ) {
				$event_args['when'] = '+' . $next_time . ' hours';
				$next_time += $time;
			}
			$ids[] = $this->create_object( $event_args );
		}

		return $ids;
	}

	/**
	 * @return string
	 */
	protected function get_post_type() {
		return Main::POSTTYPE;
	}
}
