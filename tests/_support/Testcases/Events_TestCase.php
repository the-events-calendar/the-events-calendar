<?php

namespace Tribe\Events\Test\Testcases;


use Codeception\TestCase\WPTestCase;
use Tribe\Events\Test\Factories\Event;
use Tribe\Events\Test\Factories\Organizer;
use Tribe\Events\Test\Factories\Venue;
use Tribe\Test\Data;

class Events_TestCase extends WPTestCase {
	/**
	 * @var array An array of bound implementations we could replace during tests.
	 */
	protected $backups = [];

	/**
	 * @var array An associative array of backed up alias and bound implementations.
	 */
	protected $implementation_backups = [];

	function setUp() {
		parent::setUp();

		$this->factory()->event     = new Event();
		$this->factory()->venue     = new Venue();
		$this->factory()->organizer = new Organizer();

		foreach ( $this->backups as $alias ) {
			$this->implementation_backups[ $alias ] = tribe( $alias );
		}
	}

	public function tearDown() {
		foreach ( $this->implementation_backups as $alias => $value ) {
			tribe_singleton( $alias, $value );
		}
		parent::tearDown();
	}

	/**
	 * Converts event data in the REST response format to the format consumed by EA.
	 *
	 * @param array|object $event An input event data
	 *
	 * @return array The event data converted to the format read by EA.
	 */
	protected function convert_rest_event_data_to_ea_format( $event ) {
		$event = new Data( $event, false );
		$get_id = function ( $org ) {
			return $org->id;
		};

		$start_date = new \DateTime( $event['start_date'] );
		$end_date = new \DateTime( $event['end_date'] );

		$conversion_map = [
			'title'              => $event['title'],
			'description'        => $event['description'],
			'excerpt'            => $event['excerpt'],
			'start_date'         => $start_date->format( 'Y-m-d' ),
			'start_hour'         => $start_date->format( 'H' ),
			'start_minute'       => $start_date->format( 'm' ),
			'start_meridian'     => $start_date->format( 'A' ),
			'end_date'           => $end_date->format( 'Y-m-d' ),
			'end_hour'           => $end_date->format( 'H' ),
			'end_minute'         => $end_date->format( 'm' ),
			'end_meridian'       => $end_date->format( 'A' ),
			'timezone'           => $event['timezone'],
			'url'                => $event['website'],
			'all_day'            => $event['all_day'],
			'image'              => $event['image'],
			'facebook_id'        => $event['facebook_id'],
			'meetup_id'          => $event['meetup_id'],
			'uid'                => $event['uid'],
			'parent_uid'         => $event['parent_uid'],
			'recurrence'         => $event['recurrence'],
			'categories'         => ! empty( $event['categories'] ) ? array_map( $get_id, $event['categories'] ) : false,
			'tags'               => ! empty( $event['tags'] ) ? array_map( $get_id, $event['tags'] ) : false,
			'id'                 => $event['id'],
			'currency_symbol'    => ! empty( $event['cost']['currency_symbol'] ) ? $event['cost']['currency_symbol'] : false,
			'currency_position'  => ! empty( $event['cost']['currency_position'] ) ? $event['cost']['currency_position'] : false,
			'cost'               => $event['cost'],
			'show_map'           => $event['show_map'],
			'show_map_link'      => $event['show_map_link'],
			'hide_from_listings' => $event['hide_from_listings'],
			'sticky'             => $event['sticky'],
			'featured'           => $event['featured'],
			'venue'              => ! empty( $event['venue'] ) ? $event['venue']->id : false,
			'organizer'          => ! empty( $event['organizer'] ) ? array_map( $get_id, $event['organizer'] ) : false,
		];

		$event = array_filter( $conversion_map );

		return $event;
	}
}
