<?php

namespace Tribe\Events\REST\V1\Endpoints;

use Prophecy\Prophecy\ObjectProphecy;
use Tribe\Events\Tests\Factories\Event;
use Tribe__Events__REST__V1__Endpoints__Single_Event as Endpoint;

class Single_EventTest extends \Codeception\TestCase\WPRestApiTestCase {

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
	 * it should return a WP_Error if user cannot access requested event
	 */
	public function it_should_return_a_wp_error_if_user_cannot_access_requested_event() {
		$request = new \WP_REST_Request( 'GET', '' );
		$request->set_param( 'id', $this->factory()->event->create( [ 'post_status' => 'draft' ] ) );

		$sut = $this->make_instance();
		$response = $sut->get( $request );

		$this->assertErrorResponse( 'event-not-accessible', $response, 403 );
	}

	/**
	 * @test
	 * it should return event data if event accessible
	 */
	public function it_should_return_event_data_if_event_accessible() {
		$request = new \WP_REST_Request( 'GET', '' );
		$id = $this->factory()->event->create();
		$request->set_param( 'id', $id );

		$this->repository = $this->prophesize( \Tribe__Events__REST__Interfaces__Post_Repository::class );
		$this->repository->get_event_data( $id )->willReturn( [ 'some' => 'data' ] );

		$sut = $this->make_instance();
		$response = $sut->get( $request );

		$this->assertInstanceOf( \WP_REST_Response::class, $response );
		$this->assertEquals( [ 'some' => 'data' ], $response->get_data() );
	}

	/**
	 * @return Endpoint
	 */
	private function make_instance() {
		$messages = $this->messages instanceof ObjectProphecy ? $this->messages->reveal() : $this->messages;
		$repository = $this->repository instanceof ObjectProphecy ? $this->repository->reveal() : $this->repository;

		return new Endpoint( $messages, $repository );
	}
}
