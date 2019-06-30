<?php

namespace Tribe\Events\Views\V2\Query;

use PHPUnit\Framework\AssertionFailedError;
use Tribe\Events\Test\Factories\Event;

class ControllerTest extends \Codeception\TestCase\WPTestCase {

	public function setUp() {
		parent::setUp();
		static::factory()->event = new Event();
	}

	/**
	 * @test
	 * it should be instantiatable
	 */
	public function it_should_be_instantiatable() {
		$sut = $this->make_instance();

		$this->assertInstanceOf( Event_Query_Controller::class, $sut );
	}

	/**
	 * @return Event_Query_Controller
	 */
	private function make_instance() {
		return tribe( Event_Query_Controller::class );
	}

	/**
	 * It should allow filtering the injected posts
	 *
	 * @test
	 */
	public function should_allow_filtering_the_injected_posts() {
		$controller = $this->make_instance();
		$calls      = 0;
		add_filter( 'tribe_events_views_v2_events_query_controller_posts', static function () use ( &$calls ) {
			$calls ++;

			return [ 23 ];
		} );
		$events = self::factory()->event->create();

		$query = new \WP_Query( [ 'post_type' => 'tribe_events' ] );
		$this->promote_query_to_main_query( $query );
		$filtered = $controller->inject_posts( [ $events ], $query );

		$this->assertEquals( 1, $calls );
		$this->assertEquals( [ 23 ], $filtered );
	}

	protected function promote_query_to_main_query( \WP_Query $query ) {
		global $wp_the_query;
		$wp_the_query = $query;
	}

	/**
	 * It should not inject the posts if the query object is not a query
	 *
	 * @test
	 */
	public function should_not_inject_the_posts_if_the_query_object_is_not_a_query() {
		$controller = $this->make_instance();
		add_filter( 'tribe_events_views_v2_events_query_controller_posts', '__return_empty_array' );
		$events   = self::factory()->event->create();
		$filtered = $controller->inject_posts( [ $events ], null );

		$this->assertEquals( [ $events ], $filtered );
	}

	/**
	 * It should not inject posts if the query is not the main one
	 *
	 * @test
	 */
	public function should_not_inject_posts_if_the_query_is_not_the_main_one() {
		$controller = $this->make_instance();
		add_filter( 'tribe_events_views_v2_events_query_controller_posts', '__return_empty_array' );
		$events   = self::factory()->event->create();
		$filtered = $controller->inject_posts( [ $events ], new \WP_Query( [ 'post_type' => 'tribe_event' ] ) );

		$this->assertEquals( [ $events ], $filtered );
	}

	/**
	 * It should not inject the posts if the query is not for a supported post type
	 *
	 * @test
	 */
	public function should_not_inject_the_posts_if_the_query_is_not_for_a_supported_post_type() {
		$controller = $this->make_instance();
		add_filter( 'tribe_events_views_v2_events_query_controller_posts', '__return_empty_array' );
		$posts = self::factory()->post->create();
		$query = new \WP_Query( [ 'post_type' => 'posts' ] );
		$this->promote_query_to_main_query( $query );
		$filtered = $controller->inject_posts( [ $posts ], $query );

		$this->assertEquals( [ $posts ], $filtered );
	}

	/**
	 * It should not inject posts if query contains one or more unsupported post types
	 *
	 * @test
	 */
	public function should_not_inject_posts_if_query_contains_one_or_more_unsupported_post_types() {
		$controller = $this->make_instance();
		$posts      = self::factory()->post->create();
		$filtered   = $controller->inject_posts( [ $posts ],
			new \WP_Query( [ 'post_type' => [ 'posts', 'tribe_events' ] ] ) );

		$this->assertEquals( [ $posts ], $filtered );
	}

	/**
	 * It should inject posts if query is main and for supported types
	 *
	 * @test
	 */
	public function should_inject_posts_if_query_is_main_and_for_supported_types() {
		$past_event       = static::factory()->event->create( [ 'when' => '2018-09-01' ] );
		$expected_event_1 = static::factory()->event->create( [ 'when' => '2018-10-15' ] );
		$expected_event_2 = static::factory()->event->create( [ 'when' => '2018-10-16' ] );
		add_filter( 'tribe_events_views_v2_events_query_controller_orm_args', static function () {
			return [
				'posts_per_page' => 1,
				'fields'         => 'ids',
				'starts_after'   => '2018-10-01',
			];
		} );
		$controller = $this->make_instance();
		$posts      = [ $past_event, $expected_event_1 ];
		$query      = new \WP_Query( [ 'post_type' => 'tribe_events', 'fields' => 'ids' ] );
		$this->promote_query_to_main_query( $query );
		$filtered = $controller->inject_posts( [ $posts ], $query );

		$this->assertEquals( [ $expected_event_1 ], $filtered );
		$this->assertEquals( 1, $query->post_count );
		$this->assertEquals( $expected_event_1, $query->post );
		$this->assertEquals( 2, $query->found_posts );
		$this->assertEquals( 2, $query->max_num_pages );
	}

	/**
	 * It should filter the same query at most once
	 *
	 * @test
	 */
	public function should_filter_the_same_query_at_most_once() {
		$calls = 0;
		add_filter( 'tribe_events_views_v2_events_query_controller_posts', function() use( &$calls ) {
			if ( ++ $calls > 1 ) {
				throw new AssertionFailedError( 'Post injection should not happen more than once on the same query.' );
			}

			return [ 23 ];
		} );
		$controller = $this->make_instance();
		$posts      = self::factory()->event->create();
		$query      = new \WP_Query( [ 'post_type' => 'tribe_events' ] );
		$this->promote_query_to_main_query( $query );
		$first_result = $controller->inject_posts( [ $posts ], $query );
		$this->assertEquals( $first_result, $controller->inject_posts( $first_result, $query ) );
		$this->assertEquals( $first_result, $controller->inject_posts( $first_result, $query ) );
	}
}
