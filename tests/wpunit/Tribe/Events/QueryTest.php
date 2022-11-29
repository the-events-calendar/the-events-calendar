<?php

namespace Tribe\Events;

use Spatie\Snapshots\MatchesSnapshots;
use Tribe\Events\Test\Testcases\Events_TestCase;
use Tribe\Events\Test\Traits\With_Uopz;
use Tribe__Events__Main as Main;
use Tribe__Events__Query as Query;
use Tribe__Events__Organizer as Organizer;
use Tribe__Events__Venue as Venue;
use WP_Query;
use Tribe__Admin__Helpers as Admin_Helpers;

/**
 * Test that the Event Queries behave as expected
 *
 * @group   core
 *
 * @package Tribe__Events__Main
 */
class QueryTest extends Events_TestCase {
	use MatchesSnapshots;
	use With_Uopz;

	/**
	 * @before
	 */
	public function reset_screen(): void {
		unset( $GLOBALS['current_screen'] );
	}

	/**
	 * It should allow getting found posts for arguments
	 *
	 * @test
	 */
	public function should_allow_getting_found_posts_for_arguments(): void {
		$this->factory()->event->create_many( 5 );

		$args        = [ 'found_posts' => true ];
		$found_posts = Query::getEvents( $args );

		$this->assertEquals( 5, $found_posts );
	}

	public function truthy_and_falsy_values(): array {
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
	public function should_allow_truthy_and_falsy_values_for_the_found_posts_argument( $found_posts, $bool ): void {
		$this->factory()->event->create_many( 5 );

		$this->assertEquals( Query::getEvents( [ 'found_posts' => $found_posts ] ), Query::getEvents( [ 'found_posts' => $bool ] ) );
	}

	/**
	 * It should override posts_per_page and paged arguments when using found_posts
	 *
	 * @test
	 */
	public function should_override_posts_per_page_and_paged_arguments_when_using_found_posts(): void {
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
	public function should_return_0_when_no_posts_are_found_and_found_posts_is_set(): void {
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
	public function should_allow_queries_to_ignore_hidden_events(): void {
		// Create 4 events, of which 1 will be marked as "hidden from event listings"
		$this->factory()->event->create_many( 3 );
		$this->factory()->event->create( [ 'meta_input' => [ '_EventHideFromUpcoming' => 'yes' ] ] );

		// Respecting hidden events is the default behaviour
		$all_unhidden_upcoming_events = Query::getEvents( [
			'found_posts' => true,
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
	public function should_allow_queries_to_fetch_hidden_events(): void {
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
	public function should_bail_out_of_all_filtering_if_tribe_suppress_query_filters_set(): void {
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

	public function posts_orderby_application_flags(): array {
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

	public function orderby_clause_not_requiring_date_filtering_data_provider(): \Generator {
		yield 'none' => [ 'none' ];
		yield 'rand' => [ 'rand' ];
	}

	/**
	 * It should not add date-based orderby clauses when not required
	 *
	 * @test
	 * @dataProvider orderby_clause_not_requiring_date_filtering_data_provider
	 */
	public function should_not_add_date_based_orderby_clauses_when_not_required( string $orderby ): void {
		$query = new \WP_Query( [
			'post_type' => Main::POSTTYPE,
			'orderby'   => $orderby,
			'order'     => 'ASC',
		] );

		$this->assertMatchesSnapshot( $query->request );
	}

	/**
	 * It should not throw when trying to parse non query object
	 *
	 * @test
	 */
	public function should_not_throw_when_trying_to_parse_non_query_object(): void {
		Query::parse_query( 'test' );
	}

	/**
	 * It should not parse query in admin context
	 *
	 * @test
	 */
	public function should_not_parse_query_in_admin_context(): void {
		// Simulate an admin context request.
		$this->uopz_set_return( 'is_admin', true );
		$wp_query = new WP_Query( [ 'post_type' => 'tribe_events' ] );

		$this->assertFalse( isset( $wp_query->tribe_is_event ) );
	}

	/**
	 * It should not parse a query if TEC query filters are suppressed
	 *
	 * @test
	 */
	public function should_not_parse_a_query_if_tec_query_filters_are_suppressed(): void {
		$wp_query = new WP_Query( [ 'post_type' => 'tribe_events', 'tribe_suppress_query_filters' => true ] );

		$this->assertFalse( isset( $wp_query->tribe_is_event ) );
	}

	/**
	 * It should not filter main query for post or page
	 *
	 * @test
	 */
	public function should_not_filter_main_query_for_post_or_page(): void {
		// Simulate a main query to fetch a post.
		global $wp_the_query;
		$id           = $this->factory()->post->create();
		$wp_the_query = new WP_Query();
		$wp_the_query->query( [ 'p' => $id ] );


		$this->assertNull( $wp_the_query->tribe_is_event );
	}

	/**
	 * It should set TEC post type main query paged prop based on Events paged query var
	 *
	 * @test
	 */
	public function should_set_TEC_post_type_main_query_paged_prop_based_on_events_paged_query_var(): void {
		// Simulate a request to $_GET the `tribe_paged=3`.
		$_GET['tribe_paged'] = 3;
		// Simulate a main query to fetch Events.
		global $wp_the_query;
		$wp_the_query = new WP_Query();
		$wp_the_query->query( [ 'post_type' => Main::POSTTYPE ] );

		$this->assertEquals( 3, $wp_the_query->get( 'paged' ) );
		$this->assertTrue( $wp_the_query->tribe_is_event );
		$this->assertFalse( $wp_the_query->tribe_is_event_venue );
		$this->assertFalse( $wp_the_query->tribe_is_event_organizer );

		// Simulate a main query to fetch Venues.
		$wp_the_query = new WP_Query();
		$wp_the_query->query( [ 'post_type' => Venue::POSTTYPE ] );

		$this->assertEquals( 3, $wp_the_query->get( 'paged' ) );
		$this->assertFalse( $wp_the_query->tribe_is_event );
		$this->assertTrue( $wp_the_query->tribe_is_event_venue );
		$this->assertFalse( $wp_the_query->tribe_is_event_organizer );

		// Simulate a main query to fetch Organizers.
		$wp_the_query = new WP_Query();
		$wp_the_query->query( [ 'post_type' => Organizer::POSTTYPE ] );

		$this->assertEquals( 3, $wp_the_query->get( 'paged' ) );
		$this->assertFalse( $wp_the_query->tribe_is_event );
		$this->assertFalse( $wp_the_query->tribe_is_event_venue );
		$this->assertTrue( $wp_the_query->tribe_is_event_organizer );
	}

	/**
	 * It should filter query on front for home queries
	 *
	 * @test
	 */
	public function should_filter_query_on_front_for_home_queries(): void {
		// Start with the default setting of "Your latest posts".
		update_option( 'page_on_front', 0 );

		$this->assertFalse(
			has_filter( 'option_page_on_front', [ Query::class, 'default_page_on_front' ] ),
			'To start, the query should not filtering the `page_on_front` option.'
		);

		// Simulate a main query for the home page.
		global $wp_the_query;
		$wp_the_query = new WP_Query();
		$wp_the_query->query( [ 'post_type' => 'posts' ] );

		// To start, the query should not filtering the `page_on_front` option.
		$this->assertEquals(
			10,
			has_filter( 'option_page_on_front', [ Query::class, 'default_page_on_front' ] ),
			'Page on front should be filterd for home queries.'
		);
	}

	/**
	 * It should correctly add Event post type to home queries depending on events show in loop option
	 *
	 * @test
	 */
	public function should_correctly_add_event_post_type_to_home_queries_depending_on_events_show_in_loop_option(): void {
		// Start with the default setting of "Your latest posts".
		update_option( 'page_on_front', 0 );

		// Events should not show in the loop.
		tribe_update_option( 'showEventsInMainLoop', false );

		// Simulate a main query for the home page targeting posts.
		global $wp_the_query;
		$wp_the_query = new WP_Query();
		$wp_the_query->query( [ 'post_type' => 'post' ] );

		$this->assertEquals( 'post', $wp_the_query->get( 'post_type' ) );
		$this->assertFalse( $wp_the_query->tribe_is_multi_posttype );

		// Events should appear in the loop.
		tribe_update_option( 'showEventsInMainLoop', true );

		// Simulate a main query for the home page targeting posts.
		$wp_the_query = new WP_Query();
		$wp_the_query->query( [ 'post_type' => 'post' ] );

		$this->assertEquals( [ Main::POSTTYPE, 'post' ], $wp_the_query->get( 'post_type' ) );
		$this->assertTrue( $wp_the_query->tribe_is_multi_posttype );

		// Simulate a main query for home targeting any post (already filtered, probably by a plugin).
		$wp_the_query = new WP_Query();
		$wp_the_query->query( [ 'post_type' => 'any' ] );

		$this->assertEquals( 'any', $wp_the_query->get( 'post_type' ) );
		$this->assertTrue( $wp_the_query->tribe_is_multi_posttype );
	}

	/**
	 * It should not add events to tag archives when looking at admin screen for posts
	 *
	 * @test
	 */
	public function should_not_add_events_to_tag_archives_when_looking_at_admin_screen_for_posts(): void {
		// Simulate the fact we're looking at an admin tag archive for posts.
		$this->uopz_set_return( Admin_Helpers::class, 'instance', new class extends Admin_Helpers {
			public function is_post_type_screen( $post_type = null ) {
				return true;
			}
		} );

		// Create a query for a tag archive.
		$tag   = static::factory()->tag->create();
		$query = new WP_Query( [ 'post_type' => 'post', 'tag_id' => $tag ] );

		$this->assertEquals( 'post', $query->get( 'post_type' ) );
	}

	/**
	 * It should add events to tag archives when not looking at admin screen for posts
	 *
	 * @test
	 */
	public function should_add_events_to_tag_archives_when_not_looking_at_admin_screen_for_posts(): void {
		// Simulate the fact we're looking at an admin tag archive for posts.
		$this->uopz_set_return( Admin_Helpers::class, 'instance', new class extends Admin_Helpers {
			public function is_post_type_screen( $post_type = null ) {
				return false;
			}
		} );

		// Create a query for a tag archive.
		$tag   = static::factory()->tag->create();
		$query = new WP_Query( [ 'post_type' => 'post', 'tag_id' => $tag ] );

		$this->assertEquals( [ Main::POSTTYPE, 'post' ], $query->get( 'post_type' ) );

		// Create a query for tag archive for any post (already filtered, probably by a plugin).
		$tag   = static::factory()->tag->create();
		$query = new WP_Query( [ 'post_type' => 'any', 'tag_id' => $tag ] );

		$this->assertEquals( 'any', $query->get( 'post_type' ) );
	}

	/**
	 * It should prevent 404 on Month View
	 *
	 * @test
	 */
	public function should_prevent_404_on_month_view(): void {
		// Simulate a main query for Events, there are none in the database.
		global $wp_the_query;
		$wp_the_query = new WP_Query();
		$wp_the_query->query( [ 'post_type' => Main::POSTTYPE, 'eventDisplay' => 'month' ] );

		$this->assertTrue( $wp_the_query->is_post_type_archive );
		$this->assertInstanceOf( \WP_Post_Type::class, $wp_the_query->queried_object );
		$this->assertEquals( Main::POSTTYPE, $wp_the_query->queried_object->name );
		$this->assertEquals( 0, $wp_the_query->queried_object_id );
	}

	/**
	 * It should set past flag correctly
	 *
	 * @test
	 */
	public function should_set_past_flag_correctly() {
		// Start with a non-main query for Events.
		$query = new WP_Query( [ 'post_type' => Main::POSTTYPE ] );

		$this->assertFalse( $query->tribe_is_past );

		// A main query for Events.
		global $wp_the_query;
		$wp_the_query = new WP_Query();
		$wp_the_query->query( [ 'post_type' => Main::POSTTYPE ] );

		$this->assertFalse( $wp_the_query->tribe_is_past );

		// A main query for Events specifying `eventDisplay=past`.
		$wp_the_query = new WP_Query();
		$wp_the_query->query( [ 'post_type' => Main::POSTTYPE, 'eventDisplay' => 'past' ] );

		$this->assertTrue( $wp_the_query->tribe_is_past );

		// A main query for Events where the event display comes from the context.
		add_filter( 'tribe_context_pre_event_display', static function () {
			return 'past';
		} );

		$wp_the_query = new WP_Query();
		$wp_the_query->query( [ 'post_type' => Main::POSTTYPE ] );

		$this->assertTrue( $wp_the_query->tribe_is_past );
	}

	public function flag_properties_data_provider(): \Generator {
		yield 'events query' => [
			[ 'post_type' => Main::POSTTYPE ],
			[
				'tribe_is_event'           => true,
				'tribe_is_multi_posttype'  => false,
				'tribe_is_event_category'  => false,
				'tribe_is_event_venue'     => false,
				'tribe_is_event_organizer' => false,
				'tribe_is_event_query'     => true,
				'tribe_is_past'            => false,
			]
		];

		yield 'past events query from query argument' => [
			[ 'post_type' => Main::POSTTYPE, 'eventDisplay' => 'past' ],
			[
				'tribe_is_event'           => true,
				'tribe_is_multi_posttype'  => false,
				'tribe_is_event_category'  => false,
				'tribe_is_event_venue'     => false,
				'tribe_is_event_organizer' => false,
				'tribe_is_event_query'     => true,
				'tribe_is_past'            => true,
			]
		];

		yield 'venues and organizers' => [
			[ 'post_type' => [ Venue::POSTTYPE, Organizer::POSTTYPE ] ],
			[
				'tribe_is_event'           => false,
				'tribe_is_multi_posttype'  => false,
				'tribe_is_event_category'  => false,
				'tribe_is_event_venue'     => false,
				'tribe_is_event_organizer' => false,
				'tribe_is_event_query'     => false,
				'tribe_is_past'            => false,
			]
		];

		yield 'venues query' => [
			[ 'post_type' => Venue::POSTTYPE ],
			[
				'tribe_is_event'           => false,
				'tribe_is_multi_posttype'  => false,
				'tribe_is_event_category'  => false,
				'tribe_is_event_venue'     => true,
				'tribe_is_event_organizer' => false,
				'tribe_is_event_query'     => true,
				'tribe_is_past'            => false,
			]
		];

		yield 'organizers query' => [
			[ 'post_type' => Organizer::POSTTYPE ],
			[
				'tribe_is_event'           => false,
				'tribe_is_multi_posttype'  => false,
				'tribe_is_event_category'  => false,
				'tribe_is_event_venue'     => false,
				'tribe_is_event_organizer' => true,
				'tribe_is_event_query'     => true,
				'tribe_is_past'            => false,
			]
		];
	}

	/**
	 * It should correctly set query flag properties
	 *
	 * @test
	 * @dataProvider flag_properties_data_provider
	 */
	public function should_correctly_set_query_flag_properties( array $query_args, array $expected_props ): void {
		$query = new WP_Query( $query_args );

		foreach ( $expected_props as $prop => $expected ) {
			$this->assertEquals(
				$expected, $query->{$prop},
				"Expected {$prop} to be {$expected} but it was {$query->{$prop}}"
			);
		}
	}

	/**
	 * It should correctly add Events to main query for posts.
	 *
	 * This is the logic underlying the "Included Events in main blog loop" option in Events > Settings.
	 *
	 * @test
	 */
	public function should_correctly_add_events_to_main_query_for_postst(): void {
		// Simulate a main query for posts, WordPress would NOT specify the `post_type`.
		global $wp_the_query;
		$wp_the_query = new WP_Query();
		$wp_the_query->query([]);

		$this->assertEquals( [ Main::POSTTYPE, 'post' ], $wp_the_query->get( 'post_type' ) );
		$this->assertFalse( $wp_the_query->tribe_is_event );
		$this->assertTrue( $wp_the_query->tribe_is_multi_posttype );
		$this->assertFalse( $wp_the_query->tribe_is_event_category );
		$this->assertFalse( $wp_the_query->tribe_is_event_venue );
		$this->assertFalse( $wp_the_query->tribe_is_event_organizer );
		$this->assertFalse( $wp_the_query->tribe_is_event_query );
	}
}
