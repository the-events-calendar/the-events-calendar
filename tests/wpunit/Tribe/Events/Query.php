<?php
namespace Tribe\Events;

/**
 * Test that the Event Queries behave as expected
 *
 * @group   core
 *
 * @package Tribe__Events__Main
 */
class QueryTest extends \Codeception\TestCase\WPTestCase {
	/**
	 * Event date & upcoming filters should not be removed from non-admin pages
	 *
	 * @test
	 */
	public function it_should_not_remove_date_filters_on_front_end() {
		$query = new WP_Query;
		$query->parse_query( [
			'post_type' => Tribe__Events__Main::POSTTYPE,
		] );

		$this->assertFalse( Tribe__Events__Query::should_remove_date_filters( $query ), 'Date filters should not be removed on the front end' );
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

		$this->assertFalse( Tribe__Events__Query::should_remove_date_filters( $query ), 'Date filters should not be removed on non-event queries' );
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
			'post_type' => Tribe__Events__Main::POSTTYPE,
		] );

		$this->assertFalse( Tribe__Events__Query::should_remove_date_filters( $query ), 'Date filters should not be removed on non-event edit pages' );
	}

	/**
	 * Event date & upcoming filters should not be removed from the event import page
	 *
	 * @test
	 */
	public function it_should_remove_date_filters_on_event_import_page() {
		set_current_screen( 'edit-' . Tribe__Events__Main::POSTTYPE );

		$_GET['page'] = 'events-importer';
		$_GET['tab'] = 'general';

		$query = new WP_Query;
		$query->parse_query( [
			'post_type' => Tribe__Events__Main::POSTTYPE,
		] );

		$this->assertTrue( Tribe__Events__Query::should_remove_date_filters( $query ), 'Date filters should be removed when on the event import page' );
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
			'post_type' => Tribe__Events__Main::POSTTYPE,
		] );

		$this->assertFalse( Tribe__Events__Query::should_remove_date_filters( $query ), 'Date filters should not be removed when doing AJAX stuff' );

		Tribe__Main::instance()->doing_ajax( false );
	}

	/**
	 * Event date & upcoming filters SHOULD be removed from event list pages (they do their own date filtering)
	 *
	 * @test
	 */
	public function it_should_remove_date_filters_on_event_list() {
		set_current_screen( 'edit-' . Tribe__Events__Main::POSTTYPE );

		$query = new WP_Query;
		$query->parse_query( [
			'post_type' => Tribe__Events__Main::POSTTYPE,
		] );

		if ( isset( $_GET['page'] ) ) {
			unset( $_GET['page'] );
		}

		if ( isset( $_GET['tab'] ) ) {
			unset( $_GET['tab'] );
		}

		$this->assertTrue( Tribe__Events__Query::should_remove_date_filters( $query ), 'Date filters should be removed when on the event list page' );
	}
}
