<?php

namespace Tribe\Events\REST\V1\Endpoints;

use Prophecy\Prophecy\ObjectProphecy;
use Tribe\Events\Tests\Factories\Event;
use Tribe__Events__Main as Main;
use Tribe__Events__REST__V1__Endpoints__Archive_Event as Archive;

class Archive_EventTest extends \Codeception\TestCase\WPRestApiTestCase {


	/**
	 * @var \Tribe__REST__Messages_Interface
	 */
	protected $messages;

	/**
	 * @var \Tribe__Events__REST__Interfaces__Post_Repository
	 */
	protected $repository;

	public function setUp() {
		// before
		parent::setUp();

		// your set up methods here
		$this->factory()->event = new Event();
		$this->messages = new \Tribe__Events__REST__V1__Messages();
		$this->repository = new \Tribe__Events__REST__V1__Post_Repository( new \Tribe__Events__REST__V1__Messages() );
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
	 * @test
	 * it should return empty array if there are no events in site
	 */
	public function it_should_return_empty_array_if_there_are_no_events_in_site() {
		$request = new \WP_REST_Request( 'GET', '' );

		$sut = $this->make_instance();
		$response = $sut->get( $request );

		$this->assertInstanceOf( \WP_Error::class, $response );
	}

	/**
	 * @test
	 * it should return a number of events equal to the posts per page option
	 */
	public function it_should_return_a_number_of_events_equal_to_the_posts_per_page_option() {
		$request = new \WP_REST_Request( 'GET', '' );
		update_option( 'posts_per_page', 3 );
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
	 * it should cap the per_page value at 50
	 */
	public function it_should_cap_the_per_page_value_at_50() {
		$request = new \WP_REST_Request( 'GET', '' );
		$request->set_param( 'per_page', 100 );
		update_option( 'posts_per_page', 10 );
		$this->factory()->event->create_many( 51 );

		$sut = $this->make_instance();
		$response = $sut->get( $request );

		$this->assertInstanceOf( \WP_REST_Response::class, $response );
		$this->assertCount( 50, $response->get_data()['events'] );
	}

	/**
	 * @test
	 * it should allow filtering the per_page cap
	 */
	public function it_should_allow_filtering_the_per_page_cap() {
		$request = new \WP_REST_Request( 'GET', '' );
		$request->set_param( 'per_page', 100 );
		update_option( 'posts_per_page', 10 );
		$this->factory()->event->create_many( 21 );
		add_filter( 'tribe_rest_event_max_per_page', function () {
			return 20;
		} );

		$sut = $this->make_instance();
		$response = $sut->get( $request );

		$this->assertInstanceOf( \WP_REST_Response::class, $response );
		$this->assertCount( 20, $response->get_data()['events'] );
	}

	public function not_positive_integers_above_one() {
		return [
			[ 'foo' ],
			[ 'Happy as Larry' ],
			[ '' ],
			[ '0' ],
			[ 0 ],
			[ '-1' ],
			[ - 1 ],
			[ new \stdClass() ],
			[ array( 'foo' => 'bar' ) ],
		];
	}

	/**
	 * @test
	 * it should return a WP_Error  if per_page is not a positive integer above 1
	 * @dataProvider not_positive_integers_above_one
	 */
	public function it_should_return_a_wp_error_if_per_page_is_not_a_positive_integer_above_1( $bad ) {
		$request = new \WP_REST_Request( 'GET', '' );
		$request->set_param( 'per_page', $bad );

		$sut = $this->make_instance();
		$response = $sut->get( $request );

		$this->assertWPError( $response );
	}

	/**
	 * @test
	 * it should allow filtering the events by start date
	 */
	public function it_should_allow_filtering_the_events_by_start_date() {
		$request = new \WP_REST_Request( 'GET', '' );
		$request->set_param( 'start_date', strtotime( '+3 days' ) );
		update_option( 'posts_per_page', 10 );
		$this->factory()->event->create_many( 4, [ 'time_space' => '24' ] );
		$this->assertCount( 4, tribe_get_events() );

		$sut = $this->make_instance();
		$response = $sut->get( $request );

		$this->assertInstanceOf( \WP_REST_Response::class, $response );
		$this->assertCount( 2, $response->get_data()['events'] );
	}

	/**
	 * @test
	 * it should allow filtering the events by end date
	 */
	public function it_should_allow_filtering_the_events_by_end_date() {
		$request = new \WP_REST_Request( 'GET', '' );
		$request->set_param( 'end_date', strtotime( '+1 month' ) );
		update_option( 'posts_per_page', 10 );
		$this->factory()->event->create_many( 10, [ 'time_space' => '+12 days' ] );

		$sut = $this->make_instance();
		$response = $sut->get( $request );

		$this->assertInstanceOf( \WP_REST_Response::class, $response );
		$this->assertCount( 6, $response->get_data()['events'] );
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
		$this->factory()->event->create_many( 10, [ 'time_space' => '+12 days' ] );

		$sut = $this->make_instance();
		$response = $sut->get( $request );

		$this->assertInstanceOf( \WP_REST_Response::class, $response );
		$this->assertCount( 3, $response->get_data()['events'] );
	}

	/**
	 * @test
	 * it should return a WP_Error when sending bad start_date parameter
	 */
	public function it_should_return_a_wp_error_when_sending_bad_start_date_parameter() {
		$request = new \WP_REST_Request( 'GET', '' );
		$request->set_param( 'start_date', 'Happy as Larry' );
		update_option( 'posts_per_page', 10 );
		$this->factory()->event->create_many( 10, [ 'time_space' => '+12 days' ] );

		$sut = $this->make_instance();
		$response = $sut->get( $request );

		$this->assertWPError( $response );
	}

	/**
	 * @test
	 * it should return a WP_Error when sending a bad end_date parameter
	 */
	public function it_should_return_a_wp_error_when_sending_a_bad_end_date_parameter() {
		$request = new \WP_REST_Request( 'GET', '' );
		$request->set_param( 'end_date', 'Happy as Larry' );
		update_option( 'posts_per_page', 10 );
		$this->factory()->event->create_many( 10, [ 'time_space' => '+12 days' ] );

		$sut = $this->make_instance();
		$response = $sut->get( $request );

		$this->assertWPError( $response );
	}

	/**
	 * @test
	 * it should allow specifying the page to get
	 */
	public function it_should_allow_specifying_the_page_to_get() {
		$request = new \WP_REST_Request( 'GET', '' );
		$request->set_param( 'page', 2 );
		update_option( 'posts_per_page', 3 );
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
	 * it should return WP_Error if page is not a positive integer above 1
	 * @dataProvider not_positive_integers_above_one
	 */
	public function it_should_return_wp_error_if_page_is_not_a_positive_integer_above_1( $bad ) {
		$request = new \WP_REST_Request( 'GET', '' );
		$request->set_param( 'page', $bad );

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

	/**
	 * @test
	 * it should return WP_Error if search string does not validate
	 */
	public function it_should_return_wp_error_if_search_string_does_not_validate() {
		$request = new \WP_REST_Request( 'GET', '' );
		$request->set_param( 'search', new \stdClass() );

		$sut = $this->make_instance();
		$response = $sut->get( $request );

		$this->assertWPError( $response );
	}

	/**
	 * @return Archive
	 */
	private function make_instance() {
		$messages = $this->messages instanceof ObjectProphecy ? $this->messages->reveal() : $this->messages;
		$repository = $this->repository instanceof ObjectProphecy ? $this->repository->reveal() : $this->repository;

		return new Archive( $messages, $repository );
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
}