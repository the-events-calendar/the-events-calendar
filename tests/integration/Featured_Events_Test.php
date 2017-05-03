<?php
namespace Tribe\Events;

use Tribe__Events__Featured_Events,
    Tribe__Events__Main,
    \Codeception\TestCase\WPTestCase;

class Featured_Events_Test extends \Codeception\TestCase\WPTestCase {
	protected $posts         = [];
	protected $featured_id   = 0;
	protected $unfeatured_id = 0;
	protected $spare_id      = 0;

	/**
	 * @var Tribe__Events__Featured_Events
	 */
	protected $featured_events;

	public function setUp() {
		parent::setUp();
		$this->featured_events = tribe( 'tec.featured_events' );
		$this->create_test_posts();
	}

	protected function create_test_posts() {
		// Create three test events
		for ( $i = 1; $i < 4; $i++ ) {
			$start_date = strtotime( "+$i weeks" );
			$end_date = strtotime( "+$i weeks 1 hour" );

			$this->posts[] = $this->factory()->post->create_and_get( [
				'post_type' => Tribe__Events__Main::POSTTYPE,
				'meta_input' => [
					'_EventStartDate' => $start_date,
					'_EventEndDate'   => $end_date
				],
			] );
		}

		// Feature one of them
		$this->featured_events->feature( $this->posts[ 0 ] );

		$this->featured_id   = $this->posts[ 0 ]->ID;
		$this->unfeatured_id = $this->posts[ 1 ]->ID;
		$this->spare_id      = $this->posts[ 2 ]->ID;
	}

	public function test_can_obtain_featured_events_instance() {
		$this->assertInstanceOf( 'Tribe__Events__Featured_Events', $this->featured_events );
	}

	public function test_event_can_be_set_as_featured() {
		$test_post_id = $this->spare_id;
		$this->featured_events->feature( $test_post_id );
		$this->assertTrue( $this->featured_events->is_featured( $test_post_id ) );
	}

	public function test_can_clear_featured_status() {
		$test_post_id = $this->spare_id;
		$this->featured_events->feature( $test_post_id );
		$this->featured_events->unfeature( $test_post_id );
		$this->assertFalse( $this->featured_events->is_featured( $test_post_id ) );
	}

	public function test_can_query_for_featured_events() {
		// Try to fetch all featured events
		$featured_events = tribe_get_events( [
			'featured' => true,
			'posts_per_page' => -1,
		] );

		// We should have at least one returned event
		$this->assertGreaterThanOrEqual( 1, count( $featured_events ) );

		// All returned events should be featured
		foreach ( $featured_events as $test_event ) {
			$this->assertTrue( $this->featured_events->is_featured( $test_event ) );
		}

		// We do not expect the unfeatured event we just added to have been returned
		$this->assertNotContains( $this->posts[ 1 ]->ID, wp_list_pluck( $featured_events, 'ID' ) );
	}
}