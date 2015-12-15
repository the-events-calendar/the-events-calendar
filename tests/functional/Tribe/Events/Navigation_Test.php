<?php
namespace Tribe\Events;

class Navigation_Test extends \Tribe__Events__WP_UnitTestCase {
	public function setUp() {
		// before
		parent::setUp();
		// your set up methods here
	}

	public function tearDown() {
		// your tear down methods here
		// then
		parent::tearDown();
	}

	public function test_closest_event() {
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
}
