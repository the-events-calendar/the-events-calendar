<?php
namespace Tribe\Events;

use Tribe\Events\Test\Testcases\Events_TestCase;
use Tribe__Events__Main as Main;
use Tribe__Events__Query as Query;
use Tribe__Events__Venue as Venue;
use WP_Query;

/**
 * Test that the Event Queries behave as expected
 *
 * @group   core
 *
 * @package Tribe__Events__Main
 */
class QueryTest extends Events_TestCase {
	/**
	 * It should allow getting found posts for arguments
	 *
	 * @test
	 */
	public function should_allow_getting_found_posts_for_arguments() {
		$this->factory()->event->create_many( 5 );

		$args        = [ 'found_posts' => true ];
		$found_posts = Query::getEvents( $args );

		$this->assertEquals( 5, $found_posts );
	}

	public function truthy_and_falsy_values(  ) {
		return [
			[ 'true', true ],
			[ 'true', true ],
			[ '0', false ],
			[ '1', true ],
			[ 0, false ],
			[ 1, true ],
		];

}
	/**
	 * It should allow truthy and falsy values for the found_posts argument
	 *
	 * @test
	 * @dataProvider truthy_and_falsy_values
	 */
	public function should_allow_truthy_and_falsy_values_for_the_found_posts_argument($found_posts, $bool) {
		$this->factory()->event->create_many( 5 );

		$this->assertEquals( Query::getEvents( [ 'found_posts' => $found_posts ] ), Query::getEvents( [ 'found_posts' => $bool ] ) );
	}

	/**
	 * It should override posts_per_page and paged arguments when using found_posts
	 *
	 * @test
	 */
	public function should_override_posts_per_page_and_paged_arguments_when_using_found_posts() {
		$this->factory()->event->create_many( 5 );

		$args        = [ 'found_posts' => true, 'posts_per_page' => 3, 'paged' => 2 ];
		$found_posts = Query::getEvents( $args );

		$this->assertEquals( 5, $found_posts );
	}

	/**
	 * It should return 0 when no posts are found and found_posts is set
	 *
	 * @test
	 */
	public function should_return_0_when_no_posts_are_found_and_found_posts_is_set() {
		$args        = [ 'found_posts' => true ];
		$found_posts = Query::getEvents( $args );

		$this->assertEquals( 0, $found_posts );
	}

	/**
	 * Ensure queries respect events that are marked as "hidden from event listings".
	 *
	 * @test
	 *
	 * @since 4.6.10
	 */
	public function should_allow_queries_to_ignore_hidden_events() {
		// Create 4 events, of which 1 will be marked as "hidden from event listings"
		$this->factory()->event->create_many( 3 );
		$this->factory()->event->create( [ 'meta_input' => [ '_EventHideFromUpcoming' => 'yes' ] ] );

		// Respecting hidden events is the default behaviour
		$all_unhidden_upcoming_events = Query::getEvents( [
			'found_posts'   => true,
		] );

		// It should also be possible to explicitly request this
		$all_unhidden_upcoming_events_explicit = Query::getEvents( [
			'found_posts'   => true,
			'hide_upcoming' => true,
		] );

		$this->assertEquals( 3, $all_unhidden_upcoming_events );
		$this->assertEquals( 3, $all_unhidden_upcoming_events_explicit );
	}

	/**
	 * Ensure that queries can retrieve events that are nominally hidden from event listings
	 * when required.
	 *
	 * @test
	 *
	 * @since 4.6.10
	 */
	public function should_allow_queries_to_fetch_hidden_events() {
		// Create 4 events, of which 1 will be marked as "hidden from event listings"
		$this->factory()->event->create_many( 3 );
		$this->factory()->event->create( [ 'meta_input' => [ '_EventHideFromUpcoming' => 'yes' ] ] );

		$all_upcoming_events = Query::getEvents( [
			'found_posts'   => true,
			'hide_upcoming' => false,
		] );

		$this->assertEquals( 4, $all_upcoming_events );
	}

	/**
	 * It should bail out of all filtering if tribe_suppress_query_filters set
	 *
	 * @test
	 * @link https://moderntribe.atlassian.net/browse/TEC-3530
	 */
	public function should_bail_out_of_all_filtering_if_tribe_suppress_query_filters_set() {
		// Run a Venue query first, this will hook the filters and is the condition that would trigger the issue.
		new \WP_Query( [ 'post_type' => Venue::POSTTYPE ] );

		$filtered_query = new \WP_Query( [
			'post_type'                    => Main::POSTTYPE,
			'tribe_suppress_query_filters' => true,
		] );

		$this->assertNotContains( 'ORDER BY EventStartDate', $filtered_query->request );
		// Run the same request and make sure the SQL does not contain any error.
		global $wpdb;
		$wpdb->query( $filtered_query->request );
		$this->assertEmpty( $wpdb->last_error );
	}

	public function posts_orderby_application_flags(  ):array {
		$applicator = static function ( $flag ): \Closure {
			return static function ( WP_Query $query ) use ( $flag ) {
				$query->{$flag} = true;
			};
		};

		return [
			'tribe_is_event'          => [ $applicator( 'tribe_is_event' ) ],
			'tribe_is_event_category' => [ $applicator( 'tribe_is_event_category' ) ],
		];
	}
}
