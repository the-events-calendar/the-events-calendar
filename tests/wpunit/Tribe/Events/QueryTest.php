<?php
namespace Tribe\Events;

use Tribe\Events\Tests\Testcases\Events_TestCase;
use Tribe__Events__Main as Main;
use Tribe__Events__Query as Query;
use Tribe__Main;
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
	 * Event date & upcoming filters should not be removed from non-admin pages
	 *
	 * @test
	 */
	public function it_should_not_remove_date_filters_on_front_end() {
		$query = new WP_Query;
		$query->parse_query( [
			'post_type' => Main::POSTTYPE,
		] );

		$this->assertFalse( Query::should_remove_date_filters( $query ), 'Date filters should not be removed on the front end' );
	}

	/**
	 * Event date & upcoming filters should not be removed from queries for non event post types
	 *
	 * @test
	 */
	public function it_should_not_remove_date_filters_on_non_event_query() {
		$query = new WP_Query;
		$query->parse_query( [
			'post_type' => 'post',
		] );

		$this->assertFalse( Query::should_remove_date_filters( $query ), 'Date filters should not be removed on non-event queries' );
	}

	/**
	 * Event date & upcoming filters should not be removed from admin pages that aren't event edit pages
	 *
	 * @test
	 */
	public function it_should_not_remove_date_filters_on_non_event_edit_pages() {
		set_current_screen( 'edit-post' );

		$query = new WP_Query;
		$query->parse_query( [
			'post_type' => Main::POSTTYPE,
		] );

		$this->assertFalse( Query::should_remove_date_filters( $query ), 'Date filters should not be removed on non-event edit pages' );
	}

	/**
	 * Event date & upcoming filters should not be removed from the event import page
	 *
	 * @test
	 */
	public function it_should_remove_date_filters_on_event_import_page() {
		set_current_screen( 'edit-' . Main::POSTTYPE );

		$_GET['page'] = 'events-importer';
		$_GET['tab'] = 'general';

		$query = new WP_Query;
		$query->parse_query( [
			'post_type' => Main::POSTTYPE,
		] );

		$this->assertTrue( Query::should_remove_date_filters( $query ), 'Date filters should be removed when on the event import page' );
	}

	/**
	 * Event date & upcoming filters should not be removed when the query is being done via AJAX
	 *
	 * We have to segregate the AJAX tests because we're going to twiddle the DOING_AJAX constant
	 *
	 * @test
	 */
	public function it_should_not_remove_date_filters_when_doing_ajax() {
		Tribe__Main::instance()->doing_ajax( true );

		$query = new WP_Query;
		$query->parse_query( [
			'post_type' => Main::POSTTYPE,
		] );

		$this->assertFalse( Query::should_remove_date_filters( $query ), 'Date filters should not be removed when doing AJAX stuff' );

		Tribe__Main::instance()->doing_ajax( false );
	}

	/**
	 * Event date & upcoming filters SHOULD be removed from event list pages (they do their own date filtering)
	 *
	 * @test
	 */
	public function it_should_remove_date_filters_on_event_list() {
		set_current_screen( 'edit-' . Main::POSTTYPE );

		$query = new WP_Query;
		$query->parse_query( [
			'post_type' => Main::POSTTYPE,
		] );

		if ( isset( $_GET['page'] ) ) {
			unset( $_GET['page'] );
		}

		if ( isset( $_GET['tab'] ) ) {
			unset( $_GET['tab'] );
		}

		$this->assertTrue( Query::should_remove_date_filters( $query ), 'Date filters should be removed when on the event list page' );
	}

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
}
