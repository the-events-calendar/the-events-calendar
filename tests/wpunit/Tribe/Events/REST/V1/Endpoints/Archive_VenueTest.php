<?php

namespace Tribe\Events\REST\V1\Endpoints;

use Tribe\Events\Tests\Factories\Event;
use Tribe\Events\Tests\Factories\Venue;
use Tribe__Events__REST__V1__Endpoints__Archive_Venue as Archive;

class Archive_VenueTest extends \Codeception\TestCase\WPTestCase {
	/**
	 * @var \Tribe__REST__Messages_Interface
	 */
	protected $messages;

	/**
	 * @var \Tribe__Events__REST__Interfaces__Post_Repository
	 */
	protected $repository;

	/**
	 * @var \Tribe__Validator__Interface
	 */
	protected $validator;

	public function setUp() {
		// before
		parent::setUp();

		// your set up methods here
		$this->factory()->event = new Event();
		$this->factory()->venue = new Venue();
		$this->messages         = new \Tribe__Events__REST__V1__Messages();
		$this->repository       = new \Tribe__Events__REST__V1__Post_Repository( new \Tribe__Events__REST__V1__Messages() );
		$this->validator        = new \Tribe__Events__Validator__Base;

		// to avoid date filters from being canned
		\tribe( 'context' )->doing_ajax( true );
	}

	public function tearDown() {
		// your tear down methods here

		// then
		parent::tearDown();
	}

	/**
	 * @return Archive
	 */
	private function make_instance() {
		$messages   = $this->messages instanceof ObjectProphecy ? $this->messages->reveal() : $this->messages;
		$repository = $this->repository instanceof ObjectProphecy ? $this->repository->reveal() : $this->repository;
		$validator  = $this->validator instanceof ObjectProphecy ? $this->validator->reveal() : $this->validator;

		return new Archive( $messages, $repository, $validator );
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
	 * it should return empty array if there are no venues in site
	 */
	public function it_should_return_empty_array_if_there_are_no_venues_in_site() {
		$request = new \WP_REST_Request( 'GET', '' );

		$sut      = $this->make_instance();
		$response = $sut->get( $request );

		$this->assertInstanceOf( \WP_Error::class, $response );
	}

	/**
	 * @test
	 * it should return a number of venues equal to the posts per page option
	 */
	public function it_should_return_a_number_of_venues_equal_to_the_posts_per_page_option() {
		$request = new \WP_REST_Request( 'GET', '' );
		tribe_update_option( 'posts_per_page', 3 );
		$this->factory()->venue->create_many( 5 );

		$sut      = $this->make_instance();
		$response = $sut->get( $request );

		$this->assertInstanceOf( \WP_REST_Response::class, $response );
		$this->assertCount( 3, $response->get_data()['venues'] );
	}

	/**
	 * @test
	 * it should allow overriding the posts_per_page setting with the per_page parameter
	 */
	public function it_should_allow_overriding_the_posts_per_page_setting_with_the_per_page_parameter() {
		$request = new \WP_REST_Request( 'GET', '' );
		$request->set_param( 'per_page', 10 );
		update_option( 'posts_per_page', 3 );
		$this->factory()->venue->create_many( 5 );

		$sut      = $this->make_instance();
		$response = $sut->get( $request );

		$this->assertInstanceOf( \WP_REST_Response::class, $response );
		$this->assertCount( 5, $response->get_data()['venues'] );
	}

	/**
	 * @test
	 * it should allow filtering the venue by event
	 */
	public function it_should_allow_filtering_the_venues_by_event() {
		$this->factory()->venue->create_many(3);
		$venue  = $this->factory()->venue->create();
		$events = $this->factory()->event->create_many( 2, [ 'venue' => $venue ] );

		update_option( 'posts_per_page', 10 );

		$sut = $this->make_instance();

		$request = new \WP_REST_Request();
		$request->set_param( 'event', $events[0] );
		$response = $sut->get( $request );

		$this->assertInstanceOf( \WP_REST_Response::class, $response );
		$response_venues = $response->get_data()['venues'];
		$this->assertCount( 1, $response_venues );
		$this->assertEquals( [ $venue ], array_column( $response_venues, 'id' ) );

		$request = new \WP_REST_Request();
		$request->set_param( 'event', $events[1] );
		$response = $sut->get( $request );

		$this->assertInstanceOf( \WP_REST_Response::class, $response );
		$response_venues = $response->get_data()['venues'];
		$this->assertCount( 1, $response_venues );
		$this->assertEquals( [ $venue ], array_column( $response_venues, 'id' ) );
	}

	/**
	 * @test
	 * it should allow filtering the venue by having or not linked events
	 */
	public function it_should_allow_filtering_the_venues_by_having_or_not_linked_events() {
		$venue_1 = $this->factory()->venue->create();
		$this->factory()->event->create( [ 'venue' => $venue_1 ] );
		$venue_2 = $this->factory()->venue->create();
		$this->factory()->event->create( [ 'venue' => $venue_2 ] );
		$venue_3 = $this->factory()->venue->create();
		$venue_4 = $this->factory()->venue->create();

		update_option( 'posts_per_page', 10 );

		$sut = $this->make_instance();

		$request = new \WP_REST_Request();
		$request->set_param( 'has_events', 'true' );
		$response = $sut->get( $request );

		$this->assertInstanceOf( \WP_REST_Response::class, $response );
		$response_venues = $response->get_data()['venues'];
		sort( $response_venues );
		$this->assertCount( 2, $response_venues );
		$this->assertEquals( [ $venue_1, $venue_2 ], array_column( $response_venues, 'id' ) );


		$request = new \WP_REST_Request();
		$request->set_param( 'has_events', 'false' );
		$response = $sut->get( $request );

		$this->assertInstanceOf( \WP_REST_Response::class, $response );
		$response_venues = $response->get_data()['venues'];
		sort( $response_venues );
		$this->assertCount( 2, $response_venues );
		$this->assertEquals( [ $venue_3, $venue_4 ], array_column( $response_venues, 'id' ) );
	}

	/**
	 * @test
	 * it should allow filtering the venues by having upcoming events or not
	 */
	public function it_should_allow_filtering_the_venues_by_having_upcoming_events_or_not() {
		$venue_1 = $this->factory()->venue->create();
		$this->factory()->event->create( [ 'venue' => $venue_1, 'when' => '+ 1month' ] );
		$venue_2 = $this->factory()->venue->create();
		$this->factory()->event->create( [ 'venue' => $venue_2, 'when' => '+1 month' ] );
		$venue_3 = $this->factory()->venue->create();
		$this->factory()->event->create( [ 'venue' => $venue_3, 'when' => '-1 month' ] );
		$venue_4 = $this->factory()->venue->create();

		update_option( 'posts_per_page', 10 );

		$sut = $this->make_instance();

		$request = new \WP_REST_Request();
		$request->set_param( 'only_with_upcoming', 'true' );
		$response = $sut->get( $request );

		$this->assertInstanceOf( \WP_REST_Response::class, $response );
		$response_venues = $response->get_data()['venues'];
		sort( $response_venues );
		$this->assertCount( 2, $response_venues );
		$this->assertEquals( [ $venue_1, $venue_2 ], array_column( $response_venues, 'id' ) );


		$request = new \WP_REST_Request();
		$request->set_param( 'only_with_upcoming', 'false' );
		$response = $sut->get( $request );

		$this->assertInstanceOf( \WP_REST_Response::class, $response );
		$response_venues = $response->get_data()['venues'];
		sort( $response_venues );
		$this->assertCount( 4, $response_venues );
		$this->assertEquals( [ $venue_1, $venue_2, $venue_3, $venue_4 ], array_column( $response_venues, 'id' ) );
	}

	/**
	 * @test
	 * it should allow filtering venues by search
	 */
	public function it_should_allow_filtering_events_by_start_and_end_date() {
		$venue_1 = $this->factory()->venue->create( [ 'post_title' => 'Foo zork', 'post_content' => 'lorem dolor' ] );
		$venue_2 = $this->factory()->venue->create( [ 'post_title' => 'Bar Two', 'post_content' => 'dolor sit' ] );
		$venue_3 = $this->factory()->venue->create( [ 'post_title'   => 'Baz Three',
		                                              'post_content' => 'sit nunqua zork'
		] );

		update_option( 'posts_per_page', 10 );

		$sut = $this->make_instance();

		$expected = [
			'lorem' => 1,
			'dolor' => 2,
			'two'   => 1,
			'zork'  => 2,
		];

		foreach ( $expected as $search => $n ) {
			$request = new \WP_REST_Request();
			$request->set_param( 'search', $search );
			$response = $sut->get( $request );

			$this->assertInstanceOf( \WP_REST_Response::class, $response );
			$response_venues = $response->get_data()['venues'];
			sort( $response_venues );
			$this->assertCount( $n, $response_venues );
		}
	}

	/**
	 * @test
	 * it should allow specifying the page to get
	 */
	public function it_should_allow_specifying_the_page_to_get() {
		$request = new \WP_REST_Request( 'GET', '' );
		$request->set_param( 'page', 2 );
		$request->set_param( 'per_page', 3 );
		$this->factory()->venue->create_many( 9 );

		$sut      = $this->make_instance();
		$response = $sut->get( $request );

		$this->assertInstanceOf( \WP_REST_Response::class, $response );
		$this->assertCount( 3, $response->get_data()['venues'] );
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
		$this->factory()->venue->create_many( 2 );

		$sut      = $this->make_instance();
		$response = $sut->get( $request );

		$this->assertWPError( $response );
	}

	/**
	 * @test
	 * it should return WP_Error if search string does not validate
	 */
	public function it_should_return_wp_error_if_search_string_does_not_validate() {
		$request = new \WP_REST_Request( 'GET', '' );
		$request->set_param( 'search', new \stdClass() );

		$sut      = $this->make_instance();
		$response = $sut->get( $request );

		$this->assertWPError( $response );
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
		$this->factory()->venue->create_many( $count );
		update_option( 'posts_per_page', $per_page );
		$request = new \WP_REST_Request( 'GET', '' );

		$sut      = $this->make_instance();
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
		$this->factory()->venue->create_many( 5, [ 'post_status' => 'publish' ] );
		$this->factory()->venue->create_many( 5, [ 'post_status' => 'draft' ] );
		update_option( 'posts_per_page', 5 );
		$user = $this->factory()->user->create( [ 'role' => 'administrator' ] );
		wp_set_current_user( $user );

		$request = new \WP_REST_Request( 'GET', '' );

		$sut      = $this->make_instance();
		$response = $sut->get( $request );

		$this->assertInstanceOf( \WP_REST_Response::class, $response );
		$this->assertEquals( 10, $response->get_data()['total'] );
		$this->assertEquals( 2, $response->get_data()['total_pages'] );
		$this->assertEquals( 10, $response->get_headers()['X-TEC-Total'] );
		$this->assertEquals( 2, $response->get_headers()['X-TEC-TotalPages'] );
	}

	/**
	 * @test
	 * it should hide non published venues from visitors
	 */
	public function it_should_hide_non_published_venues_from_visitors() {
		$this->factory()->venue->create_many( 5, [ 'post_status' => 'publish' ] );
		$this->factory()->venue->create_many( 5, [ 'post_status' => 'draft' ] );
		update_option( 'posts_per_page', 5 );
		// visitors cannot see drafts
		wp_set_current_user( 0 );

		$request = new \WP_REST_Request( 'GET', '' );


		$sut      = $this->make_instance();
		$response = $sut->get( $request );

		$this->assertInstanceOf( \WP_REST_Response::class, $response );
		$this->assertEquals( 5, $response->get_data()['total'] );
		$this->assertEquals( 1, $response->get_data()['total_pages'] );
		$this->assertEquals( 5, $response->get_headers()['X-TEC-Total'] );
		$this->assertEquals( 1, $response->get_headers()['X-TEC-TotalPages'] );
	}

	/**
	 * It should allow filtering the max number of posts per page
	 *
	 * @test
	 */
	public function it_should_allow_filtering_the_max_number_of_posts_per_page() {
		add_filter( 'tribe_rest_venue_max_per_page', function () {
			return 7;
		} );

		$sut = $this->make_instance();

		$this->assertEquals( 7, $sut->get_max_posts_per_page() );
	}
}