<?php

namespace Tribe\Events\REST\V1\Endpoints;

use Prophecy\Prophecy\ObjectProphecy;
use Tribe\Events\Test\Factories\Event;
use Tribe\Events\Test\Factories\Organizer;
use Tribe\Events\Test\Factories\Venue;
use Tribe__Events__Main as Main;
use Tribe__Events__REST__V1__Endpoints__Archive_Event as Archive;
use function Patchwork\Config\set;

class Archive_EventTest extends \Codeception\TestCase\WPRestApiTestCase {
	/**
	 * @var \Tribe__REST__Messages_Interface
	 */
	protected $messages;

	/**
	 * @var \Tribe__Events__REST__Interfaces__Post_Repository
	 */
	protected $repository;

	/**
	 * @var \Tribe__Events__Validator__Interface
	 */
	protected $validator;

	public function setUp(): void {
		// before
		parent::setUp();

		tribe_unset_var( \Tribe__Settings_Manager::OPTION_CACHE_VAR_NAME );

		// your set up methods here
		$this->factory()->event = new Event();
		$this->factory()->venue = new Venue();
		$this->factory()->organizer = new Organizer();
		$this->messages = new \Tribe__Events__REST__V1__Messages();
		$this->repository = new \Tribe__Events__REST__V1__Post_Repository( new \Tribe__Events__REST__V1__Messages() );
		$this->validator = new \Tribe__Events__Validator__Base;

		// to avoid date filters from being canned
		tribe( 'context' )->doing_ajax( true );
	}

	/**
	 * @test
	 * it should be instantiatable
	 */
	public function it_should_be_instantiatable() {
		$sut = $this->make_instance();

		$this->assertInstanceOf( Archive::class, $sut );
	}

	/**
	 * @return Archive
	 */
	private function make_instance() {
		$messages = $this->messages instanceof ObjectProphecy ? $this->messages->reveal() : $this->messages;
		$repository = $this->repository instanceof ObjectProphecy ? $this->repository->reveal() : $this->repository;
		$validator = $this->validator instanceof ObjectProphecy ? $this->validator->reveal() : $this->validator;

		return new Archive( $messages, $repository, $validator );
	}

	/**
	 * @test
	 * it should return empty array if there are no events in site
	 */
	public function it_should_return_empty_array_if_there_are_no_events_in_site() {
		$request = new \WP_REST_Request( 'GET', '' );

		$sut = $this->make_instance();
		$response = $sut->get( $request );

		$this->assertInstanceOf( \WP_REST_Response::class, $response );
		$this->assertEmpty( $response->data['events'] );
	}

	/**
	 * @test
	 * it should return a number of events equal to the posts per page option
	 */
	public function it_should_return_a_number_of_events_equal_to_the_posts_per_page_option() {
		$request = new \WP_REST_Request( 'GET', '' );
		tribe_update_option( 'posts_per_page', 3 );
		$this->factory()->event->create_many( 5 );

		$sut = $this->make_instance();
		$response = $sut->get( $request );

		$this->assertInstanceOf( \WP_REST_Response::class, $response );
		$this->assertCount( 3, $response->get_data()['events'] );
	}

	/**
	 * @test
	 * it should allow overriding the posts_per_page setting with the per_page parameter
	 */
	public function it_should_allow_overriding_the_posts_per_page_setting_with_the_per_page_parameter() {
		$request = new \WP_REST_Request( 'GET', '' );
		$request->set_param( 'per_page', 10 );
		update_option( 'posts_per_page', 3 );
		$this->factory()->event->create_many( 5 );

		$sut = $this->make_instance();
		$response = $sut->get( $request );

		$this->assertInstanceOf( \WP_REST_Response::class, $response );
		$this->assertCount( 5, $response->get_data()['events'] );
	}

	/**
	 * @test
	 * it should allow filtering the events by start date
	 */
	public function it_should_allow_filtering_the_events_by_start_date() {
		$request = new \WP_REST_Request( 'GET', '' );
		$request->set_param( 'start_date', strtotime( '15 days' ) );
		update_option( 'posts_per_page', 10 );
		 // space events by 3 days
		$this->factory()->event->create_many( 10, [ 'time_space' => 3 * 24 ] );
		$this->assertCount( 10, tribe_get_events() );

		$sut = $this->make_instance();
		$response = $sut->get( $request );

		$this->assertInstanceOf( \WP_REST_Response::class, $response );

		$this->assertCount( 6, $response->get_data()['events'] );
	}

	/**
	 * @test
	 * it should allow filtering the events by end date
	 */
	public function it_should_allow_filtering_the_events_by_end_date() {
		$request = new \WP_REST_Request( 'GET', '' );
		$request->set_param( 'orderby', 'event_date' );
		$request->set_param( 'order', 'ASC' );
		$request->set_param( 'end_date', strtotime( '+1 month' ) );
		update_option( 'posts_per_page', 10 );
		// create many events 5 days apart
		$total_events = 5;
		$this->factory()->event->create_many( $total_events, [ 'time_space' => 5 * 24 ] );

		$sut      = $this->make_instance();
		$response = $sut->get( $request );

		$this->assertInstanceOf( \WP_REST_Response::class, $response );
		$this->assertCount( $total_events, $response->get_data()['events'] );
	}

	/**
	 * @test
	 * it should allow filtering events by start and end date
	 */
	public function it_should_allow_filtering_events_by_start_and_end_date() {
		$request = new \WP_REST_Request( 'GET', '' );
		$request->set_param( 'start_date', strtotime( '+1 week' ) );
		$request->set_param( 'end_date', strtotime( '+5 weeks' ) );
		update_option( 'posts_per_page', 10 );
		// create events 10 days apart
		$this->factory()->event->create_many( 10, [ 'time_space' => 10 * 24 ] );

		$sut = $this->make_instance();
		$response = $sut->get( $request );

		$this->assertInstanceOf( \WP_REST_Response::class, $response );
		$this->assertCount( 3, $response->get_data()['events'] );
	}

	/**
	 * @test
	 * it should allow specifying the page to get
	 */
	public function it_should_allow_specifying_the_page_to_get() {
		$request = new \WP_REST_Request( 'GET', '' );
		$request->set_param( 'page', 2 );
		$request->set_param( 'per_page', 3 );
		$this->factory()->event->create_many( 9 );

		$sut = $this->make_instance();
		$response = $sut->get( $request );

		$this->assertInstanceOf( \WP_REST_Response::class, $response );
		$this->assertCount( 3, $response->get_data()['events'] );
		$this->assertRegExp( '/page=2/', $response->get_data()['rest_url'] );
		$this->assertRegExp( '/page=3/', $response->get_data()['next_rest_url'] );
		$this->assertNotRegExp( '/(?<!per_)page=/', $response->get_data()['previous_rest_url'] );
	}

	/**
	 * @test
	 * it should return WP_Error if requesting non existing page
	 */
	public function it_should_return_wp_error_if_requesting_non_existing_page() {
		$request = new \WP_REST_Request( 'GET', '' );
		$request->set_param( 'page', 2 );
		update_option( 'posts_per_page', 3 );
		$this->factory()->event->create_many( 2 );

		$sut = $this->make_instance();
		$response = $sut->get( $request );

		$this->assertWPError( $response );
	}

	/**
	 * @test
	 * it should allow fetching events by search string
	 */
	public function it_should_allow_fetching_events_by_search_string() {
		$request = new \WP_REST_Request( 'GET', '' );
		$request->set_param( 'search', 'foo' );
		update_option( 'posts_per_page', 20 );
		$this->factory()->event->create_many( 5, [ 'post_title' => 'foo' ] );
		$this->factory()->event->create_many( 5, [ 'post_title' => 'foo bar' ] );
		$this->factory()->event->create_many( 5, [ 'post_title' => 'bar' ] );

		$sut = $this->make_instance();
		$response = $sut->get( $request );

		$this->assertInstanceOf( \WP_REST_Response::class, $response );
		$foo_events = $response->get_data()['events'];
		$this->assertCount( 10, $foo_events );

		$request->set_param( 'search', 'bar' );

		$response = $sut->get( $request );

		$this->assertInstanceOf( \WP_REST_Response::class, $response );
		$bar_events = $response->get_data()['events'];
		$this->assertCount( 10, $bar_events );

		$this->assertCount( 5, array_intersect( wp_list_pluck( $foo_events, 'id' ), wp_list_pluck( $bar_events, 'id' ) ) );
	}

	public function events_and_per_page_settings() {
		return [
			[ 9, 3, 3 ],
			[ 10, 3, 4 ],
			[ 1, 3, 1 ],
			[ 3, 3, 1 ],
		];
	}

	/**
	 * @test
	 * it should return the amount of pages in archive for the current archive setting
	 * @dataProvider events_and_per_page_settings
	 */
	public function it_should_return_the_amount_of_pages_in_archive_for_the_current_archive_setting( $count, $per_page, $pages ) {
		$this->factory()->event->create_many( $count );
		update_option( 'posts_per_page', $per_page );
		$request = new \WP_REST_Request( 'GET', '' );

		$sut = $this->make_instance();
		$response = $sut->get( $request );

		$this->assertInstanceOf( \WP_REST_Response::class, $response );
		$this->assertEquals( $count, $response->get_data()['total'] );
		$this->assertEquals( $pages, $response->get_data()['total_pages'] );
		$this->assertEquals( $count, $response->get_headers()['X-TEC-Total'] );
		$this->assertEquals( $pages, $response->get_headers()['X-TEC-TotalPages'] );
	}

	/**
	 * @test
	 * it should return different number of pages according to the requesting user access rights
	 */
	public function it_should_return_different_number_of_pages_according_to_the_requesting_user_access_rights() {
		$this->factory()->event->create_many( 5, [ 'post_status' => 'publish' ] );
		$this->factory()->event->create_many( 5, [ 'post_status' => 'draft' ] );
		update_option( 'posts_per_page', 5 );
		$user = $this->factory()->user->create( [ 'role' => 'administrator' ] );
		wp_set_current_user( $user );

		$request = new \WP_REST_Request( 'GET', '' );

		$sut = $this->make_instance();
		$response = $sut->get( $request );

		$this->assertInstanceOf( \WP_REST_Response::class, $response );
		$this->assertEquals( 10, $response->get_data()['total'] );
		$this->assertEquals( 2, $response->get_data()['total_pages'] );
		$this->assertEquals( 10, $response->get_headers()['X-TEC-Total'] );
		$this->assertEquals( 2, $response->get_headers()['X-TEC-TotalPages'] );
	}

	/**
	 * @test
	 * it should hide non published events from visitors
	 */
	public function it_should_hide_non_published_events_from_visitors() {
		$this->factory()->event->create_many( 5, [ 'post_status' => 'publish' ] );
		$this->factory()->event->create_many( 5, [ 'post_status' => 'draft' ] );
		update_option( 'posts_per_page', 5 );
		// visitors cannot see drafts
		wp_set_current_user( 0 );

		$request = new \WP_REST_Request( 'GET', '' );


		$sut = $this->make_instance();
		$response = $sut->get( $request );

		$this->assertInstanceOf( \WP_REST_Response::class, $response );
		$this->assertEquals( 5, $response->get_data()['total'] );
		$this->assertEquals( 1, $response->get_data()['total_pages'] );
		$this->assertEquals( 5, $response->get_headers()['X-TEC-Total'] );
		$this->assertEquals( 1, $response->get_headers()['X-TEC-TotalPages'] );
	}

	/**
	 * @test
	 * it should allow filtering events by a category
	 */
	public function it_should_allow_filtering_events_by_a_category() {
		$user = $this->factory()->user->create( [ 'role' => 'administrator' ] );
		wp_set_current_user( $user );
		$term_id = $this->factory()->term->create( [ 'taxonomy' => Main::TAXONOMY, 'slug' => 'cat1' ] );
		$this->factory()->event->create_many( 3 );
		$this->factory()->event->create_many( 3, [ 'tax_input' => [ Main::TAXONOMY => [ $term_id ] ] ] );

		update_option( 'posts_per_page', 6 );

		$request = new \WP_REST_Request( 'GET', '' );
		$request->set_param( 'categories', 'cat1' );

		$sut = $this->make_instance();
		$response = $sut->get( $request );

		$this->assertInstanceOf( \WP_REST_Response::class, $response );
		$this->assertEquals( 3, $response->get_data()['total'] );
	}

	/**
	 * @test
	 * it should allow filtering events by category term_id
	 */
	public function it_should_allow_filtering_events_by_category_term_id() {
		$user = $this->factory()->user->create( [ 'role' => 'administrator' ] );
		wp_set_current_user( $user );
		$term_id = $this->factory()->term->create( [ 'taxonomy' => Main::TAXONOMY, 'slug' => 'cat1' ] );
		$this->factory()->event->create_many( 3 );
		$this->factory()->event->create_many( 3, [ 'tax_input' => [ Main::TAXONOMY => [ $term_id ] ] ] );

		update_option( 'posts_per_page', 6 );

		$request = new \WP_REST_Request( 'GET', '' );
		$request->set_param( 'categories', $term_id );

		$sut = $this->make_instance();
		$response = $sut->get( $request );

		$this->assertInstanceOf( \WP_REST_Response::class, $response );
		$this->assertEquals( 3, $response->get_data()['total'] );
	}

	/**
	 * @test
	 * it should allow filtering events by multiple categories with OR logic
	 */
	public function it_should_allow_filtering_events_by_multiple_categories_with_or_logic() {
		$user = $this->factory()->user->create( [ 'role' => 'administrator' ] );
		wp_set_current_user( $user );
		$t1 = $this->factory()->term->create( [ 'taxonomy' => Main::TAXONOMY, 'slug' => 'cat1' ] );
		$t2 = $this->factory()->term->create( [ 'taxonomy' => Main::TAXONOMY, 'slug' => 'cat2' ] );
		$t3 = $this->factory()->term->create( [ 'taxonomy' => Main::TAXONOMY, 'slug' => 'cat3' ] );
		$this->factory()->event->create_many( 3 );
		$this->factory()->event->create_many( 3, [ 'tax_input' => [ Main::TAXONOMY => [ $t1, $t2, $t3 ] ] ] );
		$this->factory()->event->create_many( 3, [ 'tax_input' => [ Main::TAXONOMY => [ $t1, $t2 ] ] ] );
		$this->factory()->event->create_many( 3, [ 'tax_input' => [ Main::TAXONOMY => [ $t1 ] ] ] );

		update_option( 'posts_per_page', 10 );

		$request = new \WP_REST_Request( 'GET', '' );
		$request->set_param( 'categories', [ $t1, 'cat2', 'cat3' ] );

		$sut = $this->make_instance();
		$response = $sut->get( $request );

		$this->assertInstanceOf( \WP_REST_Response::class, $response );
		$this->assertEquals( 9, $response->get_data()['total'] );
	}

	/**
	 * @test
	 * it should paginate events when filtering by category
	 */
	public function it_should_paginate_events_when_filtering_by_category() {
		$user = $this->factory()->user->create( [ 'role' => 'administrator' ] );
		wp_set_current_user( $user );
		$t1 = $this->factory()->term->create( [ 'taxonomy' => Main::TAXONOMY, 'slug' => 'cat1' ] );
		$this->factory()->event->create_many( 5 );
		$this->factory()->event->create_many( 5, [ 'tax_input' => [ Main::TAXONOMY => [ $t1 ] ] ] );

		$request = new \WP_REST_Request( 'GET', '' );
		$request->set_param( 'categories', [ $t1 ] );
		$request->set_param( 'per_page', 3 );

		$sut = $this->make_instance();
		$response = $sut->get( $request );

		$this->assertInstanceOf( \WP_REST_Response::class, $response );
		$data = $response->get_data();
		$this->assertCount( 3, $data['events'] );
		$this->assertEquals( 5, $data['total'] );
		$this->assertEquals( 2, $data['total_pages'] );
	}

	/**
	 * @test
	 * it should allow filtering events by a tag
	 */
	public function it_should_allow_filtering_events_by_a_tag() {
		$user = $this->factory()->user->create( [ 'role' => 'administrator' ] );
		wp_set_current_user( $user );
		$term_id = $this->factory()->term->create( [ 'taxonomy' => 'post_tag', 'slug' => 'tag1' ] );
		$this->factory()->event->create_many( 3 );
		$this->factory()->event->create_many( 3, [ 'tax_input' => [ 'post_tag' => [ $term_id ] ] ] );

		update_option( 'posts_per_page', 6 );

		$request = new \WP_REST_Request( 'GET', '' );
		$request->set_param( 'tags', 'tag1' );

		$sut = $this->make_instance();
		$response = $sut->get( $request );

		$this->assertInstanceOf( \WP_REST_Response::class, $response );
		$this->assertEquals( 3, $response->get_data()['total'] );
	}

	/**
	 * @test
	 * it should allow filtering events by tag term_id
	 */
	public function it_should_allow_filtering_events_by_tag_term_id() {
		$user = $this->factory()->user->create( [ 'role' => 'administrator' ] );
		wp_set_current_user( $user );
		$term_id = $this->factory()->term->create( [ 'taxonomy' => 'post_tag', 'slug' => 'tag1' ] );
		$this->factory()->event->create_many( 3 );
		$this->factory()->event->create_many( 3, [ 'tax_input' => [ 'post_tag' => [ $term_id ] ] ] );

		update_option( 'posts_per_page', 6 );

		$request = new \WP_REST_Request( 'GET', '' );
		$request->set_param( 'tags', $term_id );

		$sut = $this->make_instance();
		$response = $sut->get( $request );

		$this->assertInstanceOf( \WP_REST_Response::class, $response );
		$this->assertEquals( 3, $response->get_data()['total'] );
	}

	/**
	 * @test
	 * it should allow filtering events by multiple tags with OR logic
	 */
	public function it_should_allow_filtering_events_by_multiple_tags_with_or_logic() {
		$user = $this->factory()->user->create( [ 'role' => 'administrator' ] );
		wp_set_current_user( $user );
		$t1 = $this->factory()->term->create( [ 'taxonomy' => 'post_tag', 'slug' => 'tag1' ] );
		$t2 = $this->factory()->term->create( [ 'taxonomy' => 'post_tag', 'slug' => 'tag2' ] );
		$t3 = $this->factory()->term->create( [ 'taxonomy' => 'post_tag', 'slug' => 'tag3' ] );
		$this->factory()->event->create_many( 3 );
		$this->factory()->event->create_many( 3, [ 'tax_input' => [ 'post_tag' => [ $t1, $t2, $t3 ] ] ] );
		$this->factory()->event->create_many( 3, [ 'tax_input' => [ 'post_tag' => [ $t1, $t2 ] ] ] );
		$this->factory()->event->create_many( 3, [ 'tax_input' => [ 'post_tag' => [ $t1 ] ] ] );

		update_option( 'posts_per_page', 10 );

		$request = new \WP_REST_Request( 'GET', '' );
		$request->set_param( 'tags', [ $t1, 'tag2', 'tag3' ] );

		$sut = $this->make_instance();
		$response = $sut->get( $request );

		$this->assertInstanceOf( \WP_REST_Response::class, $response );
		$this->assertEquals( 9, $response->get_data()['total'] );
	}

	/**
	 * @test
	 * it should paginate events when filtering by tag
	 */
	public function it_should_paginate_events_when_filtering_by_tag() {
		$user = $this->factory()->user->create( [ 'role' => 'administrator' ] );
		wp_set_current_user( $user );
		$t1 = $this->factory()->term->create( [ 'taxonomy' => 'post_tag', 'slug' => 'tag1' ] );
		$this->factory()->event->create_many( 5 );
		$this->factory()->event->create_many( 5, [ 'tax_input' => [ 'post_tag' => [ $t1 ] ] ] );

		$request = new \WP_REST_Request( 'GET', '' );
		$request->set_param( 'tags', [ $t1 ] );
		$request->set_param( 'per_page', 3 );

		$sut = $this->make_instance();
		$response = $sut->get( $request );

		$this->assertInstanceOf( \WP_REST_Response::class, $response );
		$data = $response->get_data();
		$this->assertCount( 3, $data['events'] );
		$this->assertEquals( 5, $data['total'] );
		$this->assertEquals( 2, $data['total_pages'] );
	}

	/**
	 * It should allow filtering events by featured status
	 *
	 * @test
	 */
	public function it_should_allow_filtering_events_by_featured_status() {
		$not_featured = $this->factory()->event->create_many( 5 );
		$featured = $this->factory()->event->create_many( 5, [ 'meta_input' => [ \Tribe__Events__Featured_Events::FEATURED_EVENT_KEY => 'true' ] ] );

		$request = new \WP_REST_Request( 'GET', '' );
		$request->set_param( 'featured', true );
		$request->set_param( 'per_page', 20 );

		$sut = $this->make_instance();
		$response = $sut->get( $request );

		$this->assertInstanceOf( \WP_REST_Response::class, $response );
		$data = $response->get_data();
		$this->assertCount( 5, $data['events'] );
		$this->assertEqualSets( $featured, wp_list_pluck( $data['events'], 'id' ) );

		$request->set_param( 'featured', false );
		$response = $sut->get( $request );

		$this->assertInstanceOf( \WP_REST_Response::class, $response );
		$data = $response->get_data();
		$this->assertCount( 5, $data['events'] );
		$this->assertEqualSets( $not_featured, wp_list_pluck( $data['events'], 'id' ) );
	}

	/**
	 * It should allow filtering events by venue ID
	 *
	 * @test
	 */
	public function it_should_allow_filtering_events_by_venue_id() {
		$venue_id = $this->factory()->venue->create();
		$with_venue = $this->factory()->event->create_many( 3, [ 'meta_input' => [ '_EventVenueID' => $venue_id ] ] );
		$this->factory()->event->create_many( 3 );

		$sut = $this->make_instance();

		$request = new \WP_REST_Request();
		$request->set_param( 'venue', $venue_id );
		$request->set_param( 'per_page', 10 );

		/** @var \WP_REST_Response $response */
		$response = $sut->get( $request );

		$this->assertInstanceOf( \WP_REST_Response::class, $response );
		$data = $response->get_data();
		$this->assertCount( 3, $data['events'] );
		$this->assertEqualSets( $with_venue, wp_list_pluck( $data['events'], 'id' ) );
	}

	/**
	 * It should allow filtering events by organizer ID
	 *
	 * @test
	 */
	public function it_should_allow_filtering_events_by_organizer_id() {
		$organizer_id = $this->factory()->organizer->create();
		$with_organizer = $this->factory()->event->create_many( 3, [ 'meta_input' => [ '_EventOrganizerID' => $organizer_id ] ] );
		$this->factory()->event->create_many( 3 );

		$sut = $this->make_instance();

		$request = new \WP_REST_Request();
		$request->set_param( 'organizer', $organizer_id );
		$request->set_param( 'per_page', 10 );

		/** @var \WP_REST_Response $response */
		$response = $sut->get( $request );

		$this->assertInstanceOf( \WP_REST_Response::class, $response );
		$data = $response->get_data();
		$this->assertCount( 3, $data['events'] );
		$this->assertEqualSets( $with_organizer, wp_list_pluck( $data['events'], 'id' ) );
	}

	/**
	 * It should allow filtering events by multiple organizer ID
	 *
	 * with a logic OR behaviour (WP REST like)
	 *
	 * @test
	 */
	public function it_should_allow_filtering_events_by_multiple_organizer_id() {
		$organizer_id_1 = $this->factory()->organizer->create();
		$organizer_id_2 = $this->factory()->organizer->create();

		$with_organizer_1 = $this->factory()->event->create_many( 3, [ 'meta_input' => [ '_EventOrganizerID' => $organizer_id_1 ] ] );
		$with_organizer_2 = $this->factory()->event->create_many( 3, [ 'meta_input' => [ '_EventOrganizerID' => $organizer_id_2 ] ] );
		$with_organizer_1_and_2 = $this->factory()->event->create_many( 3 );
		foreach ( $with_organizer_1_and_2 as $id ) {
			add_post_meta( $id, '_EventOrganizerID', $organizer_id_1 );
			add_post_meta( $id, '_EventOrganizerID', $organizer_id_2 );
		}
		$this->factory()->event->create_many( 3 );

		$sut = $this->make_instance();

		$request = new \WP_REST_Request();
		$request->set_param( 'organizer', $organizer_id_1 );
		$request->set_param( 'per_page', 10 );

		/** @var \WP_REST_Response $response */
		$response = $sut->get( $request );

		$this->assertInstanceOf( \WP_REST_Response::class, $response );
		$data = $response->get_data();
		$this->assertCount( 6, $data['events'] );
		$this->assertEqualSets( array_merge( $with_organizer_1, $with_organizer_1_and_2 ), wp_list_pluck( $data['events'], 'id' ) );

		$request->set_param( 'organizer', $organizer_id_2 );
		$response = $sut->get( $request );

		$this->assertInstanceOf( \WP_REST_Response::class, $response );
		$data = $response->get_data();
		$this->assertCount( 6, $data['events'] );
		$this->assertEqualSets( array_merge( $with_organizer_2, $with_organizer_1_and_2 ), wp_list_pluck( $data['events'], 'id' ) );

		// we expect a logic OR behaviour
		$request->set_param( 'organizer', implode( ',', [ $organizer_id_1, $organizer_id_2 ] ) );
		$response = $sut->get( $request );

		$this->assertInstanceOf( \WP_REST_Response::class, $response );
		$data = $response->get_data();
		$this->assertCount( 9, $data['events'] );
		$this->assertEqualSets( array_merge( $with_organizer_1, $with_organizer_2, $with_organizer_1_and_2 ), wp_list_pluck( $data['events'], 'id' ) );
	}

	public function sanitize_per_page_inputs() {
		return [
			[ 23, 23 ],
			[ '23', 23 ],
			[ 0, false ],
			[ '0', false ],
		];
	}

	/**
	 * Test sanitize_per_page
	 *
	 * @test
	 * @dataProvider sanitize_per_page_inputs
	 */
	public function test_sanitize_per_page( $input, $expected ) {
		$sut = $this->make_instance();

		$this->assertEquals( $expected, $sut->sanitize_per_page( $input ) );
	}

	/**
	 * It should allow filtering the max number of posts per page
	 *
	 * @test
	 */
	public function it_should_allow_filtering_the_max_number_of_posts_per_page() {
		add_filter( 'tribe_rest_event_max_per_page', function () {
			return 7;
		} );

		$sut = $this->make_instance();

		$this->assertEquals( 7, $sut->get_max_posts_per_page() );
	}

	/**
	 * It should return the correct REST URL parameters when filtering by venue
	 *
	 * @test
	 */
	public function should_return_the_correct_rest_url_parameters_when_filtering_by_venue() {
		$venue = $this->factory()->venue->create();
		$this->factory()->event->create( [ 'venue' => $venue ] );
		$request = new \WP_REST_Request();
		$request->set_param( 'venue', $venue );

		$sut      = $this->make_instance();
		$response = $sut->get( $request );

		$this->assertNotEmpty( $response->data['rest_url'] );
		parse_str( parse_url( $response->data['rest_url'], PHP_URL_QUERY ), $params );

		$this->assertArrayHasKey( 'venue', $params );
		$this->assertEquals( $venue, $params['venue'] );
	}

	/**
	 * It should return the correct REST URL parameters when filtering by organizer
	 *
	 * @test
	 */
	public function should_return_the_correct_rest_url_parameters_when_filtering_by_organizer() {
		$organizer_1 = $this->factory()->organizer->create();
		$organizer_2 = $this->factory()->organizer->create();
		$this->factory()->event->create( [ 'organizer' => [ $organizer_1, $organizer_2 ] ] );
		$request = new \WP_REST_Request();
		$request->set_param( 'organizer', $organizer_1 );

		$sut      = $this->make_instance();
		$response = $sut->get( $request );

		$this->assertNotEmpty( $response->data['rest_url'] );
		parse_str( parse_url( $response->data['rest_url'], PHP_URL_QUERY ), $params );

		$this->assertArrayHasKey( 'organizer', $params );
		$this->assertEquals( $organizer_1, $params['organizer'] );

		$request->set_param( 'organizer', [ $organizer_1, $organizer_2 ] );

		$response = $sut->get( $request );

		$this->assertNotEmpty( $response->data['rest_url'] );
		parse_str( parse_url( $response->data['rest_url'], PHP_URL_QUERY ), $params );

		$this->assertArrayHasKey( 'organizer', $params );
		$this->assertEquals( implode( ',', [ $organizer_1, $organizer_2 ]), $params['organizer'] );
	}

	/**
	 * It should return the correct REST URL parameters when filtering by organizer and venue
	 *
	 * @test
	 */
	public function should_return_the_correct_rest_url_parameters_when_filtering_by_organizer_and_venue() {
		$venue = $this->factory()->venue->create();
		$organizer_1 = $this->factory()->organizer->create();
		$organizer_2 = $this->factory()->organizer->create();
		$this->factory()->event->create( [
			'organizer' => [ $organizer_1, $organizer_2 ] ,
			'venue' => $venue,
		] );
		$request = new \WP_REST_Request();
		$request->set_param( 'venue', $venue );
		$request->set_param( 'organizer', $organizer_1 );

		$sut      = $this->make_instance();
		$response = $sut->get( $request );

		$this->assertNotEmpty( $response->data['rest_url'] );
		parse_str( parse_url( $response->data['rest_url'], PHP_URL_QUERY ), $params );

		$this->assertArrayHasKey( 'organizer', $params );
		$this->assertEquals( $organizer_1, $params['organizer'] );
		$this->assertArrayHasKey( 'venue', $params );
		$this->assertEquals( $venue, $params['venue'] );

		$request->set_param( 'organizer', [ $organizer_1, $organizer_2 ] );

		$response = $sut->get( $request );

		$this->assertNotEmpty( $response->data['rest_url'] );
		parse_str( parse_url( $response->data['rest_url'], PHP_URL_QUERY ), $params );

		$this->assertArrayHasKey( 'organizer', $params );
		$this->assertEquals( implode( ',', [ $organizer_1, $organizer_2 ]), $params['organizer'] );
		$this->assertArrayHasKey( 'venue', $params );
		$this->assertEquals( $venue, $params['venue'] );
	}

	/**
	 * It should return the correct URL parameters when filtering by featured status
	 *
	 * @test
	 */
	public function should_return_the_correct_url_parameters_when_filtering_by_featured_status() {
		$this->factory()->event->create( [ 'meta_input' => [ \Tribe__Events__Featured_Events::FEATURED_EVENT_KEY => '1' ] ] );
		$this->factory()->event->create();
		$request = new \WP_REST_Request();
		$request->set_param( 'featured', '1' );

		$sut      = $this->make_instance();
		$response = $sut->get( $request );

		$this->assertNotEmpty( $response->data['rest_url'] );
		parse_str( parse_url( $response->data['rest_url'], PHP_URL_QUERY ), $params );

		$this->assertArrayHasKey( 'featured', $params );
		$this->assertEquals( '1', $params['featured'] );

		$request->set_param( 'featured', '0' );

		$response = $sut->get( $request );

		$this->assertNotEmpty( $response->data['rest_url'] );
		parse_str( parse_url( $response->data['rest_url'], PHP_URL_QUERY ), $params );

		$this->assertArrayHasKey( 'featured', $params );
		$this->assertEquals( '0', $params['featured'] );
	}

	/**
	 * It should return the correct REST URL parameters when filtering by date
	 *
	 * @test
	 *
	 * @dataProvider requestDatesDataProvider
	 */
	public function should_return_the_correct_rest_url_parameters_when_filtering_by_date(...$dates)
	{

		$this->set_permalink_structure('/%postname%/');
		$keys = [
			'start_date',
			'end_date',
			'starts_before',
			'ends_before',
			'starts_after',
			'ends_after',
			'strict_dates',
		];

		$request = new \WP_REST_Request();
		$assert  = [];
		$relative_dates = false;
		$set_dates = false;

		foreach ($keys as $key => $value) {
			if ( ! empty($dates[$key])) {
				$request[$value] = $dates[$key];

				if ( 'strict_dates' !== $value ) {
					$assert[]        = $value;
				}

				if( strpos( $key, 'starts' ) !== false || strpos( $key, 'ends' ) !== false ) {
					$relative_dates = true;
				}

				if( 'start_date' === $key || 'end_date' === $key ) {
					$set_dates = true;
				}
			}
		}

		$endpoint = $this->make_instance();
		$response = $endpoint->get($request);

		// All test cases must return a rest_url
		$this->assertNotEmpty($response->data['rest_url']);
		parse_str(parse_url($response->data['rest_url'], PHP_URL_QUERY), $params);

		$strict_dates = $request['strict_dates'] ?? false;

		if ( $relative_dates && ! $set_dates ) {
			$this->assertArrayNotHasKey( 'start_date', $params, 'If relative dates were set explicitly and set dates were not, no set dates should exist in the rest_url');
			$this->assertArrayNotHasKey( 'end_date', $params, 'If relative dates were set explicitly and set dates were not, no set dates should exist in the rest_url');
		}

		if ( ! $relative_dates && ! $set_dates ) {
			// These assertions work when this request is made in the browser, but not here :confused:
			// $this->assertArrayHasKey( 'start_date', $params, 'If no date params were set explicitly, the default start_date should exist in the rest_url');
			// $this->assertArrayHasKey( 'end_date', $params, 'If no date params were set explicitly, the default end_date should exist in the rest_url');
		}

		// strict_date related assertions work when the request is made in the browser, but not here :confused:
		$strict_dates_key = \array_search( 'strict_dates', $assert );
		unset( $assert[ $strict_dates_key ] );

		foreach ($assert as $key) {

			$this->assertArrayHasKey($key, $params, 'Any parameter passed explicitly must be present in the returned rest_url');

			if ($strict_dates) {
				$this->assertEquals(date('Y-m-d H:i:s', strtotime($request[$key])), $params[$key],
					'If strict_dates is set to true, any date passed must be returned exactly as-is.');
				continue;
			}

			switch ($key) {
				case 'start_date':
					$this->assertEquals(date('Y-m-d', strtotime($request[$key])).' 00:00:00', $params[$key],
						'If strict_dates is set to false, start_date must be returned as the start of the requested day.');
					break;
				case 'end_date':
					$this->assertEquals(date('Y-m-d', strtotime($request[$key])).' 23:59:59', $params[$key],
						'If strict_dates is set to false, end_date must be returned as the end of requested day.');
					break;
				case 'starts_before':
					$this->assertEquals(date('Y-m-d', strtotime($request[$key])).' 23:59:59', $params[$key],
						'If strict_dates is set to false, starts_before dates must be returned as the end of the requested day.');
					break;
				case 'ends_before':
				$this->assertEquals(date('Y-m-d', strtotime($request[$key])).' 23:59:59', $params[$key],
					'If strict_dates is set to false, ends_before dates must be returned as the end of requested day.');
					break;
				case 'starts_after':
					$this->assertEquals(date('Y-m-d', strtotime($request[$key])).' 23:59:59', $params[$key],
						'If strict_dates is set to false, starts_after dates must be returned as the end of the requested day.');
					break;
				case 'ends_after':
					$this->assertEquals(date('Y-m-d', strtotime($request[$key])).' 23:59:59', $params[$key],
						'If strict_dates is set to false, ends_after dates must be returned as the end of the requested day.');
					break;
			}
		}
	}

	/**
	 * @since 6.0.7
	 *
	 * data structure
	 * set_name => [
	 * 		0 => start_date,
	 * 		1 => end_date,
	 * 		2 => starts_before,
	 * 		3 => ends_before,
	 * 		4 => starts_after,
	 * 		5 => ends_after,
	 * 		6 => strict_dates
	 * ]
	 *
	 * @return array
	 */
	public function requestDatesDataProvider()
	{
		return [
			'no-dates' => [false, false, false, false, false, false, false],
			'no-dates-2' => [false, false, false, false, false, false, true],
			'set-dates-strict' => ['2018-01-01', '2018-12-31', false, false, false, false, true ],
			'relative-dates-strict' => [false, false, '2018-01-01', '2019-01-01', false, false, true ],
			'relative-dates-strict-2' => [false, false, false, false, '2018-01-01', '2018-12-31', true ],
			'set-and-relative-dates-strict' => ['2017-01-01', '2018-12-31', '2018-01-01', '2019-01-01', false, false, true ],
			'set-and-relative-dates-strict-2' => ['2017-01-01', '2018-12-31', false, false, '2018-01-01', '2019-01-01', true ],
			'set-dates-non-strict' => ['2018-01-01', '2018-12-31', false, false, false, false, false ],
			'relative-dates-non-strict' => [false, false, '2018-01-01', '2018-12-31', false, false, false ],
			'relative-dates-non-strict-2' => [false, false, false, false, '2018-01-01', '2018-12-31', false ],
			'set-and-relative-dates-non-strict' => ['2017-01-01', '2018-12-31', '2018-01-01', '2019-01-01', false, false, false ],
			'set-and-relative-dates-non-strict-2' => ['2017-01-01', '2018-12-31', false, false, '2018-01-01', '2019-01-01', false ],
		];
	}

	/**
	 * It should allow controlling inclusive dates at request level
	 *
	 * @test
	 */
	public function should_allow_controlling_inclusive_dates_at_request_level() {
		$event_0 = $this->factory()->event->create( [ 'when' => '2017-12-31 17:00:00' ] );
		$event_1 = $this->factory()->event->create( [ 'when' => '2018-01-01 16:00:00', 'duration' => 30 * MINUTE_IN_SECONDS ] );
		$event_2 = $this->factory()->event->create( [ 'when' => '2018-01-01 20:00:00', 'duration' => 30 * MINUTE_IN_SECONDS ] );
		$event_3 = $this->factory()->event->create( [ 'when' => '2018-01-02 10:00:00' ] );

		$request = new \WP_REST_Request();
		$request['start_date'] = '2018-01-01 15:00:00';
		$request['end_date'] = '2018-01-01 18:00:00';

		$endpoint = $this->make_instance();

		$results = $endpoint->get( $request );
		$ids = wp_list_pluck( $results->data['events'], 'id' );
		$this->assertEquals( [
			$event_1,
			$event_2,
		], $ids, 'Inclusive dates will extend to include all Events in the day.' );

		$request['strict_dates'] = false;
		$results = $endpoint->get( $request );
		$ids = wp_list_pluck( $results->data['events'], 'id' );
		$this->assertEquals( [
			$event_1,
			$event_2,
		], $ids, 'Inclusive dates will extend to include all Events in the day.' );

		$request['strict_dates'] = true;
		$results = $endpoint->get( $request );
		$ids = wp_list_pluck( $results->data['events'], 'id' );
		$this->assertEquals( [
			$event_1
		], $ids, 'Strict dates will include only Events in the time range.' );
	}

	/**
	 * It should allow setting relative dates in request
	 *
	 * @test
	 */
	public function should_allow_setting_relative_dates_in_request() {
		$event_0 = $this->factory()->event->create( [ 'when' => '2017-12-31 17:00:00' ] );
		$event_1 = $this->factory()->event->create( [ 'when' => '2018-01-01 16:00:00' ] );
		$event_2 = $this->factory()->event->create( [ 'when' => '2018-01-01 19:00:00' ] );
		$event_3 = $this->factory()->event->create( [ 'when' => '2018-01-02 10:00:00' ] );
		$event_4 = $this->factory()->event->create( [ 'when' => '2017-12-30 10:00:00', 'duration' => 3 * DAY_IN_SECONDS ] );
		$event_5 = $this->factory()->event->create( [ 'when' => '2017-12-29 10:00:00', 'duration' => 5 * DAY_IN_SECONDS ] );

		$request = new \WP_REST_Request();
		$request['ends_after'] = '2017-12-31';
		$request['starts_before'] = '2018-01-02';
		$endpoint = $this->make_instance();
		$results = $endpoint->get( $request );
		$ids = wp_list_pluck( $results->data['events'], 'id' );
		$expected_ids = [ $event_5, $event_4, $event_1, $event_2, $event_3 ]; // Ordered by date
		$this->assertEquals( $expected_ids, $ids, 'Setting relative dates in a request will retrieve single-day and multi-day events spanning the same period.' );

		$request['start_date'] = '2017-12-30';
		$request['end_date'] = '2018-01-02';
		$endpoint = $this->make_instance();
		$results = $endpoint->get( $request );
		$ids = wp_list_pluck( $results->data['events'], 'id' );
		$expected_ids = [ $event_4, $event_1, $event_2, $event_3 ]; // Ordered by date
		$this->assertEquals( $expected_ids, $ids, 'Setting mixed static and relative dates in a request will retrieve the stricter set of events.' );
	}
}
