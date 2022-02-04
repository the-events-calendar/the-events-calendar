<?php
namespace Tribe\Events;

class Navigation_Test extends \Codeception\TestCase\WPTestCase {
	private $post_example_settings;

	public function setUp() {
		// before
		parent::setUp();
		// your set up methods here
		$this->post_example_settings = array(
			'post_author'           => 3,
			'post_title'            => 'Test event',
			'post_content'          => 'This is event content!',
			'post_status'           => 'publish',
			'EventAllDay'           => false,
			'EventHideFromUpcoming' => true,
			'EventOrganizerID'      => 5,
			'EventVenueID'          => 8,
			'EventShowMapLink'      => true,
			'EventShowMap'          => true,
			'EventStartDate'        => '2012-01-01',
			'EventEndDate'          => '2012-01-03',
			'EventStartHour'        => '01',
			'EventStartMinute'      => '15',
			'EventStartMeridian'    => 'am',
			'EventEndHour'          => '03',
			'EventEndMinute'        => '25',
			'EventEndMeridian'      => 'pm',
		);
	}

	public function tearDown() {
		// your tear down methods here
		// then
		parent::tearDown();
	}

	/**
	 * Test a linear closest event list
	 *
	 * The order should be:
	 *
	 *   ID  EventStartDate
	 *   1   2015-12-01 15:00:00
	 *   2   2015-12-02 15:00:00
	 *   3   2015-12-02 15:00:00
	 *   4   2015-12-03 15:00:00
	 */
	public function test_closest_event_linear() {
		$this->markTestSkipped( 'LegacyViewsDeprecatedTest' );

		$main = \Tribe__Events__Main::instance();
		$settings = $this->post_example_settings;
		unset( $settings['EventHideFromUpcoming'] );

		$settings['EventStartDate'] = date( 'Y-m-d', strtotime( '+1 day' ) );
		$settings['EventEndDate'] = date( 'Y-m-d', strtotime( '+1 day' ) );

		$post_id = tribe_create_event( $settings );
		$post_1 = tribe_get_events( array( 'p' => $post_id ) )[0];

		$settings['post_title'] = 'Test event 2';
		$settings['EventStartDate'] = date( 'Y-m-d', strtotime( '+2 days' ) );
		$settings['EventEndDate'] = date( 'Y-m-d', strtotime( '+2 days' ) );

		$post_id = tribe_create_event( $settings );
		$post_2 = tribe_get_events( array( 'p' => $post_id ) )[0];

		$settings['post_title'] = 'Test event 3';

		$post_id = tribe_create_event( $settings );
		$post_3 = tribe_get_events( array( 'p' => $post_id ) )[0];

		$settings['post_title'] = 'Test event 4';
		$settings['EventStartDate'] = date( 'Y-m-d', strtotime( '+3 days' ) );
		$settings['EventEndDate'] = date( 'Y-m-d', strtotime( '+3 days' ) );

		$post_id = tribe_create_event( $settings );
		$post_4 = tribe_get_events( array( 'p' => $post_id ) )[0];

		$this->assertEquals( null, $main->get_closest_event( $post_1, 'previous' ), "Post 1's previous post should be null" );
		$this->assertEquals( $post_2->ID, $main->get_closest_event( $post_1, 'next' )->ID, "Post 1's next post should be Post 2" );

		$this->assertEquals( $post_1->ID, $main->get_closest_event( $post_2, 'previous' )->ID, "Post 2's previous post should be Post 1" );
		$this->assertEquals( $post_3->ID, $main->get_closest_event( $post_2, 'next' )->ID, "Post 2's next post should be Post 3" );

		$this->assertEquals( $post_2->ID, $main->get_closest_event( $post_3, 'previous' )->ID, "Post 3's previous post should be Post 2" );
		$this->assertEquals( $post_4->ID, $main->get_closest_event( $post_3, 'next' )->ID, "Post 3's next post should be Post 4" );

		$this->assertEquals( $post_3->ID, $main->get_closest_event( $post_4, 'previous' )->ID, "Post 4's previous post should be Post 3" );
		$this->assertEquals( null, $main->get_closest_event( $post_4, 'next' ), "Post 4's next post should be null" );
	}

	/**
	 * Test a non-linear closest event list
	 *
	 * The order should be:
	 *
	 *   ID  EventStartDate
	 *   2   2015-12-01 12:00:00
	 *   1   2015-12-02 15:00:00
	 *   3   2015-12-02 15:00:00
	 *   4   2015-12-02 15:00:00
	 *   5   2015-12-03 16:00:00
	 */
	public function test_closest_event_non_linear() {
		$this->markTestSkipped( 'LegacyViewsDeprecatedTest' );

		$main     = \Tribe__Events__Main::instance();
		$settings = $this->post_example_settings;
		unset( $settings['EventHideFromUpcoming'] );

		$post_1 = tribe_events()
			->set_args( [
				'title'      => 'Event 1',
				'start_date' => '+2 days 15:00:00',
				'duration'   => HOUR_IN_SECONDS,
				'status'     => 'publish',
			] )
			->create();
		$post_2 = tribe_events()
			->set_args( [
				'title'      => 'Event 2',
				'start_date' => '+1 days 12:00:00',
				'duration'   => HOUR_IN_SECONDS,
				'status'     => 'publish',
			] )
			->create();
		$post_3 = tribe_events()
			->set_args( [
				'title'      => 'Event 3',
				'start_date' => '+2 days 15:00:00',
				'duration'   => HOUR_IN_SECONDS,
				'status'     => 'publish',
			] )
			->create();
		$post_4 = tribe_events()
			->set_args( [
				'title'      => 'Event 4',
				'start_date' => '+2 days 15:00:00',
				'duration'   => HOUR_IN_SECONDS,
				'status'     => 'publish',
			] )
			->create();
		$post_5 = tribe_events()
			->set_args( [
				'title'      => 'Event 5',
				'start_date' => '+3 days 16:00:00',
				'duration'   => HOUR_IN_SECONDS,
				'status'     => 'publish',
			] )
			->create();

		$this->assertEquals( $post_2->ID, $main->get_closest_event( $post_1, 'previous' )->ID, "Post 1's previous post should be Post 2" );
		$this->assertEquals( $post_3->ID, $main->get_closest_event( $post_1, 'next' )->ID, "Post 1's next post should be Post 3" );

		$this->assertEquals( null, $main->get_closest_event( $post_2, 'previous' ), "Post 2's previous post should be null" );
		$this->assertEquals( $post_1->ID, $main->get_closest_event( $post_2, 'next' )->ID, "Post 2's next post should be Post 1" );

		$this->assertEquals( $post_1->ID, $main->get_closest_event( $post_3, 'previous' )->ID, "Post 3's previous post should be Post 1" );
		$this->assertEquals( $post_4->ID, $main->get_closest_event( $post_3, 'next' )->ID, "Post 3's next post should be Post 4" );

		$this->assertEquals( $post_3->ID, $main->get_closest_event( $post_4, 'previous' )->ID, "Post 4's previous post should be Post 3" );
		$this->assertEquals( $post_5->ID, $main->get_closest_event( $post_4, 'next' )->ID, "Post 4's next post should be Post 5" );

		$this->assertEquals( $post_4->ID, $main->get_closest_event( $post_5, 'previous' )->ID, "Post 5's previous post should be Post 4" );
		$this->assertEquals( null, $main->get_closest_event( $post_5, 'next' ), "Post 5's next post should be null" );
	}
}
