<?php

namespace Helper;

use Codeception\Module\WPDb;

class TribeDb extends \Codeception\Module {

	/**
	 * @var WPDb
	 */
	protected $db;

	/**
	 * @var array An array containing some pre-compiled locations data.
	 */
	protected $locations
		= [
			'new_york' => [
				'_VenueAddress	'   => '939 Lexington Ave',
				'_VenueCity	'      => 'New York',
				'_VenueCountry	'   => 'United States',
				'_VenueProvince'      => '',
				'_VenueState'         => 'NY',
				'_VenueZip'           => '10065',
				'_VenuePhone'         => '',
				'_VenueURL'           => '',
				'_VenueShowMap'       => 'true',
				'_VenueShowMapLink'   => 'true',
				'_VenueStateProvince' => 'NY',
			],
			'paris'    => [
				'_VenueAddress'       => '37 Rue de la Bûcherie',
				'_VenueCity'          => 'Paris',
				'_VenueCountry'       => 'France',
				'_VenueProvince'      => 'Paris',
				'_VenueState'         => '',
				'_VenueZip'           => '75005',
				'_VenuePhone'         => '',
				'_VenueURL'           => '',
				'_VenueShowMap'       => 'true',
				'_VenueShowMapLink'   => 'true',
				'_VenueStateProvince' => 'Paris',
			],
		];

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
	 * Inserts a venue in the database.
	 *
	 * @param array  $overrides
	 * @param string $location An options prefixed location to set for the venue (see `$locations`).
	 *
	 * @return int The venue post ID
	 */
	public function haveVenueInDatabase( array $overrides = [], $location = null ) {
		$uniqid = uniqid();
		$args = [
			'post_type'    => 'tribe_venue',
			'post_title'   => 'Venue ' . $uniqid,
			'post_name'    => 'venue-' . $uniqid,
			'post_content' => 'Venue ' . $uniqid . ' description',
			'post_excerpt' => 'Venue ' . $uniqid . ' excerpt',
			'meta_input'   => [
				'_EventShowMap'       => '1',
				'_EventShowMapLink'   => '1',
				'_VenueAddress'       => $uniqid . ' address',
				'_VenueCity'          => $uniqid . ' city',
				'_VenueCountry'       => $uniqid . ' country',
				'_VenueProvince'      => $uniqid . ' province',
				'_VenueState'         => $uniqid . ' state',
				'_VenueZip'           => $uniqid . ' zip',
				'_VenuePhone'         => $uniqid . ' phone',
				'_VenueURL'           => $uniqid . ' url',
				'_VenueStateProvince' => $uniqid . ' state_province',
			],
		];

		if ( is_string( $location ) && isset( $this->locations[ $location ] ) ) {
			$args['meta_input'] = array_merge( $args['meta_input'], $this->locations[ $location ] );
		}

		if ( isset( $overrides['meta_input'] ) ) {
			$args['meta_input'] = array_merge( $args['meta_input'], $overrides['meta_input'] );
			unset( $overrides['meta_input'] );
		}

		return $this->db->havePostInDatabase( array_merge( $args, $overrides ) );
	}

	/**
	 * Inserts an organizer in the database.
	 *
	 * @param array $overrides
	 *
	 * @return int The organizer post ID
	 */
	public function haveOrganizerInDatabase( array $overrides = [] ) {
		$uniqid = uniqid();
		$args = [
			'post_type'    => 'tribe_organizer',
			'post_title'   => 'Organizer ' . $uniqid,
			'post_name'    => 'organizer-' . $uniqid,
			'post_content' => 'Organizer ' . $uniqid . ' description',
			'post_excerpt' => 'Organizer ' . $uniqid . ' excerpt',
			'meta_input'   => [
				'_OrganizerPhone'   => $uniqid . ' phone',
				'_OrganizerWebsite' => $uniqid . ' website',
				'_OrganizerEmail'   => $uniqid . ' email',
			],
		];

		if ( isset( $overrides['meta_input'] ) ) {
			$args['meta_input'] = array_merge( $args['meta_input'], $overrides['meta_input'] );
			unset( $overrides['meta_input'] );
		}

		$args = array_merge( $args, $overrides );

		return $this->db->havePostInDatabase( $args );
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
	 *                         `venue` - shortcut to add an `_EventVenueID` to the event meta, must be a venue post ID.
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

		$tax_input = [];
		if ( isset( $overrides['categories'] ) ) {
			$tax_input['tribe_events_cat'] = (array) $overrides['categories'];
			unset( $overrides['categories'] );
		}
		if ( isset( $overrides['tags'] ) ) {
			$tax_input['post_tag'] = (array) $overrides['tags'];
			unset( $overrides['tags'] );
		}

		if ( ! empty( $tax_input ) ) {
			if ( isset( $overrides['tax_input'] ) ) {
				$overrides['tax_input'] = array_merge( $overrides['tax_input'], $tax_input );
			} else {
				$overrides['tax_input'] = $tax_input;
			}
		}

		if ( isset( $overrides['venue'] ) ) {
			$meta_input['_EventVenueID'] = $overrides['venue'];
			unset( $overrides['venue'] );
		}

		$organizers = [];
		if ( isset( $overrides['organizer'] ) ) {
			$organizers = (array) $overrides['organizer'];
			unset( $overrides['organizer'] );
		}

		unset( $overrides['when'], $overrides['duration'], $overrides['utc_offset'] );

		$id = uniqid( mt_rand( 1, 999 ), true );
		$defaults = [
			'post_type'  => 'tribe_events',
			'post_title' => "Event {$id}",
			'post_name'  => "event-{$id}",
			'meta_input' => isset( $overrides['meta_input'] ) ? array_merge( $meta_input, $overrides['meta_input'] ) : $meta_input,
		];

		unset( $overrides['meta_input'] );

		$post_id = $this->db->havePostInDatabase( array_merge( $defaults, $overrides ) );

		if ( ! empty( $organizers ) ) {
			foreach ( $organizers as $organizer_id ) {
				$this->db->havePostmetaInDatabase( $post_id, '_EventOrganizerID', $organizer_id );
			}
		}

		return $post_id;
	}

	/**
	 * Inserts many venues in the database
	 *
	 * @param       $count
	 * @param array $overrides An array of values overriding the default ones of each generated venue.
	 *
	 * @return array
	 *
	 * @see haveVenueInDatabase
	 */
	public function haveManyVenuesInDatabase( $count, array $overrides = [] ) {
		return array_map( function () use ( $overrides ) {
			return $this->haveVenueInDatabase( $overrides );
		}, range( 1, $count ) );
	}

	/**
	 * Inserts many organizers in the database
	 *
	 * @param       $count
	 * @param array $overrides An array of values overriding the default ones of each generated organizer.
	 *
	 * @return array
	 *
	 * @see haveOrganizerInDatabase
	 */
	public function haveManyOrganizersInDatabase( $count, array $overrides = [] ) {
		return array_map( function () use ( $overrides ) {
			return $this->haveOrganizerInDatabase( $overrides );
		}, range( 1, $count ) );
	}
}