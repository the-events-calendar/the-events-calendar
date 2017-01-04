<?php
namespace Tribe\Events\REST\V1\Endpoints;

use Prophecy\Prophet;
use Tribe__Events__Main as Main;
use Tribe__Events__REST__V1__Endpoints__Single_Event as Endpoint;

class Single_EventTest extends \Codeception\TestCase\WPRestApiTestCase {

	/**
	 * @var \Tribe__REST__Messages_Interface
	 */
	protected $messages;

	/**
	 * @var \Tribe__REST__Post_Repository_Interface
	 */
	protected $repository;

	public function setUp() {
		// before
		parent::setUp();

		// your set up methods here
		$this->messages = new \Tribe__Events__REST__V1__Messages();
		$this->repository = new \Tribe__Events__REST__V1__Post_Repository();
	}

	public function tearDown() {
		// your tear down methods here

		// then
		parent::tearDown();
	}

	/**
	 * @test
	 * it should be instantiatable
	 */
	public function it_should_be_instantiatable() {
		$sut = $this->make_instance();

		$this->assertInstanceOf( Endpoint::class, $sut );
	}

	/**
	 * @test
	 * it should return a bad request status if id is missing from request
	 */
	public function it_should_return_a_bad_request_status_if_id_is_missing_from_request() {
		$request = new \WP_REST_Request();

		$sut = $this->make_instance();
		$response = $sut->get( $request );

		$this->assertErrorResponse( 'missing-event-id', $response, 400 );
	}

	/**
	 * @test
	 * it should return a WP_Error if requested event does not exist
	 */
	public function it_should_return_a_wp_error_if_requested_event_does_not_exist() {
		$request = new \WP_REST_Request( 'GET', '' );
		$request->set_param( 'id', 23 );

		$sut = $this->make_instance();
		$response = $sut->get( $request );

		$this->assertErrorResponse( 'event-not-found', $response, 404 );
	}

	/**
	 * @test
	 * it should return a WP_Error if requested event is not an event
	 */
	public function it_should_return_a_wp_error_if_requested_event_is_not_an_event() {
		$request = new \WP_REST_Request( 'GET', '' );
		$request->set_param( 'id', $this->factory()->post->create() );

		$sut = $this->make_instance();
		$response = $sut->get( $request );

		$this->assertErrorResponse( 'event-not-found', $response, 404 );
	}

	/**
	 * @test
	 * it should return a WP_Error if user cannot access requested event
	 */
	public function it_should_return_a_wp_error_if_user_cannot_access_requested_event() {
		$request = new \WP_REST_Request( 'GET', '' );
		$request->set_param( 'id', $this->factory()->post->create( [ 'post_type' => Main::POSTTYPE, 'post_status' => 'draft' ] ) );

		$sut = $this->make_instance();
		$response = $sut->get( $request );

		$this->assertErrorResponse( 'event-not-accessible', $response, 403 );
	}

	/**
	 * @return Endpoint
	 */
	private function make_instance() {
		$messages = $this->messages instanceof Prophet ? $this->messages->reveal() : $this->messages;
		$repository = $this->repository instanceof Prophet ? $this->repository->reveal() : $this->repository;

		return new Endpoint( $messages, $repository );
	}
}
