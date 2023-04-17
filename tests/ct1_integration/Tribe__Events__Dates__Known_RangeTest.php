<?php

use TEC\Events\Custom_Tables\V1\Models\Occurrence;
use Tribe\Events\Test\Traits\CT1\CT1_Fixtures;

/**
 * Test CT1 known range generation.
 */
class Tribe__Events__Dates__Known_RangeTest extends \Codeception\TestCase\WPTestCase {
	use CT1_Fixtures;

	protected function given_reset_range_options() {
		tribe_remove_option( 'earliest_date' );
		tribe_remove_option( 'earliest_date_markers' );
		tribe_remove_option( 'latest_date' );
		tribe_remove_option( 'latest_date_markers' );
	}

	/**
	 * Should provide variations in post_status, end date, start date, multiple events and no events.
	 *
	 * @return array[]
	 */
	public function range_of_occurrences_data_provider() {

		return [
			'given 0 events'                                                                           => [
				[],
				null,
				null,
				null,
				null
			],
			'given 1 event -> publish -> 2010-01-01 09:00:00 - 2010-01-01 11:00:00'                    => [
				[
					[
						'meta_input'  => [
							'_EventStartDate'    => '2010-01-01 01:00:00',
							'_EventEndDate'      => '2010-01-01 03:00:00',
							'_EventStartDateUTC' => '2010-01-01 09:00:00',
							'_EventEndDateUTC'   => '2010-01-01 11:00:00',
							'_EventDuration'     => 7200,
							'_EventTimezone'     => 'America/Los_Angeles',
						],
						'post_status' => 'publish',
					]
				],
				'2010-01-01 09:00:00',
				'2010-01-01 11:00:00',
				0,
				0
			],
			'given 2 events -> publish -> 2010-01-01 09:00:00 - 2011-01-01 11:00:00'                   => [
				[
					[
						'meta_input'  => [
							'_EventStartDate'    => '2010-01-01 01:00:00',
							'_EventEndDate'      => '2010-01-01 03:00:00',
							'_EventStartDateUTC' => '2010-01-01 09:00:00',
							'_EventEndDateUTC'   => '2010-01-01 11:00:00',
							'_EventDuration'     => 7200,
							'_EventTimezone'     => 'America/Los_Angeles',
						],
						'post_status' => 'publish',
					],
					[
						'meta_input'  => [
							'_EventStartDate'    => '2011-01-01 01:00:00',
							'_EventEndDate'      => '2011-01-01 03:00:00',
							'_EventStartDateUTC' => '2011-01-01 09:00:00',
							'_EventEndDateUTC'   => '2011-01-01 11:00:00',
							'_EventDuration'     => 7200,
							'_EventTimezone'     => 'America/Los_Angeles',
						],
						'post_status' => 'publish',
					]
				],
				'2010-01-01 09:00:00',
				'2011-01-01 11:00:00',
				0,
				0
			],
			'given 3 events -> 2 publish + 1 trash -> 2010-01-01 09:00:00 - 2011-01-01 11:00:00'       => [
				[
					[
						'meta_input'  => [
							'_EventStartDate'    => '2010-01-01 01:00:00',
							'_EventEndDate'      => '2010-01-01 03:00:00',
							'_EventStartDateUTC' => '2010-01-01 09:00:00',
							'_EventEndDateUTC'   => '2010-01-01 11:00:00',
							'_EventDuration'     => 7200,
							'_EventTimezone'     => 'America/Los_Angeles',
						],
						'post_status' => 'publish',
					],
					[
						'meta_input'  => [
							'_EventStartDate'    => '2011-01-01 01:00:00',
							'_EventEndDate'      => '2011-01-01 03:00:00',
							'_EventStartDateUTC' => '2011-01-01 09:00:00',
							'_EventEndDateUTC'   => '2011-01-01 11:00:00',
							'_EventDuration'     => 7200,
							'_EventTimezone'     => 'America/Los_Angeles',
						],
						'post_status' => 'publish',
					],
					[
						'meta_input'  => [
							'_EventStartDate'    => '2012-01-01 01:00:00',
							'_EventEndDate'      => '2012-01-01 03:00:00',
							'_EventStartDateUTC' => '2012-01-01 09:00:00',
							'_EventEndDateUTC'   => '2012-01-01 11:00:00',
							'_EventDuration'     => 7200,
							'_EventTimezone'     => 'America/Los_Angeles',
						],
						'post_status' => 'trash',
					]
				],
				'2010-01-01 09:00:00',
				'2011-01-01 11:00:00',
				0,
				1
			],
			'given 3 events -> 2 publish + 1 trash -> 2011-01-01 09:00:00 - 2012-01-01 11:00:00'       => [
				[
					[
						'meta_input'  => [
							'_EventStartDate'    => '2010-01-01 01:00:00',
							'_EventEndDate'      => '2010-01-01 03:00:00',
							'_EventStartDateUTC' => '2010-01-01 09:00:00',
							'_EventEndDateUTC'   => '2010-01-01 11:00:00',
							'_EventDuration'     => 7200,
							'_EventTimezone'     => 'America/Los_Angeles',
						],
						'post_status' => 'trash',
					],
					[
						'meta_input'  => [
							'_EventStartDate'    => '2011-01-01 01:00:00',
							'_EventEndDate'      => '2011-01-01 03:00:00',
							'_EventStartDateUTC' => '2011-01-01 09:00:00',
							'_EventEndDateUTC'   => '2011-01-01 11:00:00',
							'_EventDuration'     => 7200,
							'_EventTimezone'     => 'America/Los_Angeles',
						],
						'post_status' => 'publish',
					],
					[
						'meta_input'  => [
							'_EventStartDate'    => '2012-01-01 01:00:00',
							'_EventEndDate'      => '2012-01-01 03:00:00',
							'_EventStartDateUTC' => '2012-01-01 09:00:00',
							'_EventEndDateUTC'   => '2012-01-01 11:00:00',
							'_EventDuration'     => 7200,
							'_EventTimezone'     => 'America/Los_Angeles',
						],
						'post_status' => 'publish',
					]
				],
				'2011-01-01 09:00:00',
				'2012-01-01 11:00:00',
				1,
				0
			],
			'given 2 events -> 1 tribe_ignored + 1 trash -> 2010-01-01 09:00:00 - 2011-01-01 11:00:00' => [
				[
					[
						'meta_input'  => [
							'_EventStartDate'    => '2010-01-01 01:00:00',
							'_EventEndDate'      => '2010-01-01 03:00:00',
							'_EventStartDateUTC' => '2010-01-01 09:00:00',
							'_EventEndDateUTC'   => '2010-01-01 11:00:00',
							'_EventDuration'     => 7200,
							'_EventTimezone'     => 'America/Los_Angeles',
						],
						'post_status' => 'tribe_ignored',
					],
					[
						'meta_input'  => [
							'_EventStartDate'    => '2011-01-01 01:00:00',
							'_EventEndDate'      => '2011-01-01 03:00:00',
							'_EventStartDateUTC' => '2011-01-01 09:00:00',
							'_EventEndDateUTC'   => '2011-01-01 11:00:00',
							'_EventDuration'     => 7200,
							'_EventTimezone'     => 'America/Los_Angeles',
						],
						'post_status' => 'trash',
					],
				],
				null,
				null,
				null,
				null
			]
		];
	}

	/**
	 * Given various state of events in the database, verify the computed range is correct in CT1 context.
	 *
	 * @test
	 * @dataProvider range_of_occurrences_data_provider
	 */
	public function should_test_expected_known_range( $given_events, $expected_earliest_date, $expected_latest_date, $expected_earliest_offset, $expected_latest_offset ) {
		// Setup a known event state.
		foreach ( $given_events as $event_args ) {
			$this->given_a_migrated_single_event( $event_args );
		}

		// Clear our range.
		$this->given_reset_range_options();

		// Run a fresh rebuild.
		$known_range = new Tribe__Events__Dates__Known_Range();
		$known_range->rebuild_known_range();

		// Get our rebuilt values.
		$earliest_date         = tribe_get_option( 'earliest_date', null );
		$earliest_date_markers = tribe_get_option( 'earliest_date_markers', null );
		$latest_date           = tribe_get_option( 'latest_date', null );
		$latest_date_markers   = tribe_get_option( 'latest_date_markers', null );

		// Check expected outcome.
		$earliest_occurrence = Occurrence:: order_by( 'start_date_utc', 'ASC' )
		                                 ->offset( $expected_earliest_offset )
		                                 ->first();
		$latest_occurrence   = Occurrence:: order_by( 'end_date_utc', 'DESC' )
		                                 ->offset( $expected_latest_offset )
		                                 ->first();
		$this->assertEquals( $expected_earliest_date, $earliest_date );
		$this->assertEquals( $expected_latest_offset !== null ? [ $earliest_occurrence->post_id ] : null, $earliest_date_markers );
		$this->assertEquals( $expected_latest_date, $latest_date );
		$this->assertEquals( $expected_latest_offset !== null ? [ $latest_occurrence->post_id ] : null, $latest_date_markers );
	}
}