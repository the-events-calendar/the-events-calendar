<?php

namespace Tribe\Events;

use Tribe\Events\Test\Testcases\Events_TestCase;
use Tribe__Events__Amalgamator as Amalgamator;

/**
 * Test Amalgamator
 *
 * @group   core
 *
 * @package AmalgamatorTest
 */
class AmalgamatorTest extends Events_TestCase {

	public function post_ids() {
		return [
			[
				[ 45, 4, 6 ],
				[],
				4,
				[ 6, 45 ],
			],
			[
				[ 45, 4, 6 ],
				[ 6 ],
				6,
				[ 4, 45 ],
			],
			[
				[ 45, 4, 6 ],
				6,
				6,
				[ 4, 45 ],
			],
			[
				[],
				[],
				'',
				null,
			],
			[
				[ 45 ],
				[],
				'',
				null,
			],
			[
				[ '45', '4', '6' ],
				4,
				4,
				[ 6, 45 ],
			],
			[
				[ 'no-id', 4, 6 ],
				4,
				4,
				[ 0, 6 ],
			],
			[
				[ 'no-id', 4, 6, 'badid' ],
				4,
				4,
				[ 0, 0, 6 ],
			],
			[
				[ 67, 4, 6, 45 ],
				5,
				4,
				[ 6, 45, 67 ],
			],
		];
	}

	/**
	 * @test
	 * @dataProvider post_ids
	 */
	public function should_handle_amalgamate_venue_ids( $venue_ids, $filter_keep, $expected_venue_id, $expected_venue_ids ) {
		$amalgamator = $this->construct( Amalgamator::class, [], [
			'keep'                  => '',
			'venue_ids'             => '',
			'run_amalgamate_venues' => function ( $venue_ids, $keep = [] ) {
				if ( empty( $keep ) ) {
					$keep = array_shift( $venue_ids );
				}

				$this->keep      = $keep;
				$this->venue_ids = array_values( $venue_ids );
			},
		] );

		add_filter( 'tribe_amalgamate_venues_keep_venue', static function ( $keep, $venue_ids ) use ( $filter_keep ) {
			return $filter_keep;
		}, 10, 2 );

		$amalgamator->amalgamate_venues( $venue_ids );

		$this->assertEquals( $expected_venue_id, $this->keep );
		$this->assertEquals( $expected_venue_ids, $this->venue_ids );
	}

	/**
	 * @test
	 * @dataProvider post_ids
	 */
	public function should_handle_amalgamate_organizer_ids( $organizer_ids, $filter_keep, $expected_organizer_id, $expected_organizer_ids ) {
		$amalgamator = $this->construct( Amalgamator::class, [], [
			'keep'                  => '',
			'organizer_ids'             => '',
			'run_amalgamate_organizers' => function ( $organizer_ids, $keep = [] ) {
				if ( empty( $keep ) ) {
					$keep = array_shift( $organizer_ids );
				}

				$this->keep      = $keep;
				$this->organizer_ids = array_values( $organizer_ids );
			},
		] );

		add_filter( 'tribe_amalgamate_organizers_keep_organizer', static function ( $keep, $organizer_ids ) use ( $filter_keep ) {
			return $filter_keep;
		}, 10, 2 );

		$amalgamator->amalgamate_organizers( $organizer_ids );

		$this->assertEquals( $expected_organizer_id, $this->keep );
		$this->assertEquals( $expected_organizer_ids, $this->organizer_ids );
	}
}
