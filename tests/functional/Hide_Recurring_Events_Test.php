<?php
class Tribe_Hide_Recurring_Event_Test extends Tribe__Events__Pro__WP_UnitTestCase {
	protected $test_parent_id = 0;


	/**
	 * Create a recurring event for use by all subsequent tests.
	 */
	public function setUp() {
		$start_date = date( 'Y-m-d', strtotime( '2014-05-01' ) );

		$event_args = [
			'post_type'        => Tribe__Events__Main::POSTTYPE,
			'post_title'       => __FUNCTION__,
			'post_content'     => __CLASS__ . ' ' . __FUNCTION__,
			'post_name'        => 'test-tribeHideRecurrence',
			'post_status'      => 'publish',
			'EventStartDate'   => $start_date,
			'EventEndDate'     => $start_date,
			'EventStartHour'   => 16,
			'EventEndHour'     => 17,
			'EventStartMinute' => 0,
			'EventEndMinute'   => 0,
			'recurrence' => [
				'rules' => [
					0 => [
						'type'      => 'Every Week',
						'end-type'  => 'After',
						'end'       => null,
						'end-count' => 8,
					],
				],
			],
		];

		// Create the parent then trigger the build of all child events
		$this->test_parent_id = Tribe__Events__API::createEvent($event_args);
		Tribe__Events__Pro__Main::instance()->queue_processor->process_queue( PHP_INT_MAX );
	}

	/**
	 * Ensure we get back the expected event data when querying with the
	 * tribeHideRecurrence flag set.
	 */
	public function test_hides_subsequent_recurring_events() {
		$children = get_posts(array(
			'post_type' => Tribe__Events__Main::POSTTYPE,
			'post_parent' => $this->test_parent_id,
			'fields' => 'ids',
			'posts_per_page' => 10,
			'orderby' => 'ID',
			'order' => 'ASC',
		));

		$query = new WP_Query();
		$results = $query->query(array(
			'post_type' => Tribe__Events__Main::POSTTYPE,
			'tribeHideRecurrence' => 1,
			'start_date' => '2014-05-01',
			'eventDisplay' => 'custom',
		));

		$this->assertCount( 1, $results );
		$this->assertEquals( $this->test_parent_id, reset( $results )->ID );

		$results = $query->query(array(
			'post_type' => Tribe__Events__Main::POSTTYPE,
			'tribeHideRecurrence' => 1,
			'start_date' => '2014-06-01',
			'eventDisplay' => 'custom',
			'orderby' => 'ID',
			'order' => 'ASC',
		));

		$this->assertCount(1, $results);
		$this->assertNotEmpty($children);
		$this->assertEquals($children[4], reset($results));

		$query = new WP_Query();
		$results = $query->query(array(
			'post_type' => Tribe__Events__Main::POSTTYPE,
			'tribeHideRecurrence' => 0,
			'start_date' => '2014-05-01',
			'eventDisplay' => 'custom',
		));

		$this->assertCount(8, $results);

		$option = tribe_get_option( 'hideSubsequentRecurrencesDefault', false );
		tribe_update_option( 'hideSubsequentRecurrencesDefault', TRUE );

		$query = new WP_Query();
		$results = $query->query(array(
			'post_type' => Tribe__Events__Main::POSTTYPE,
			'tribeHideRecurrence' => 0,
			'start_date' => '2014-05-01',
			'eventDisplay' => 'custom',
		));

		$this->assertCount(8, $results);

		tribe_update_option( 'hideSubsequentRecurrencesDefault', $option );
	}

	/**
	 * A WP_Query object may be passed a return fields argument set to "ids" or
	 * "id=>parent" and the hide-recurrence logic ought to be able to accommodate
	 * that.
	 */
	public function test_supports_query_fields_property() {
		$query   = new WP_Query();
		$results = $query->query( [
			'post_type'           => Tribe__Events__Main::POSTTYPE,
			'fields'              => 'ids',
			'tribeHideRecurrence' => 1,
			'start_date'          => '2014-05-01',
			'eventDisplay'        => 'custom',
		] );

		$this->markTestSkipped(
			'Tribe__Events__Pro__Recurrence_Meta::recurrence_collapse_sql() does not support this yet.'
		);
	}
}