<?php

namespace Tribe\Events\REST\V1\Endpoints;

use Prophecy\Prophecy\ObjectProphecy;
use Tribe\Events\Tests\Factories\Event;
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

		$this->assertInstanceOf( \WP_REST_Response::class, $response );
		$this->assertEquals( [], $response->get_data()['events'] );
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
		$request->set_param( 'start_date', strtotime( '+1 month' ) );
		update_option( 'posts_per_page', 10 );
		$this->factory()->event->create_many( 10, [ 'time_space' => '+4 days' ] );

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
		$this->assertNotRegExp( '/page=/', $response->get_data()['previous_rest_url'] );
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

		$this->assertCount( 5, array_intersect( wp_list_pluck( $foo_events, 'ID' ), wp_list_pluck( $bar_events, 'ID' ) ) );
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
}