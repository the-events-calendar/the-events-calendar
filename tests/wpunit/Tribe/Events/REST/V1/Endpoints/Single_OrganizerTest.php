<?php

namespace Tribe\Events\REST\V1\Endpoints;

use Codeception\TestCase\WPRestApiTestCase;
use Prophecy\Prophecy\ObjectProphecy;
use Tribe\Events\Tests\Factories\Event;
use Tribe\Events\Tests\Factories\Organizer;
use Tribe\Events\Tests\Factories\Venue;
use Tribe__Events__REST__V1__Endpoints__Single_Organizer as Single_Organizer;

class Single_OrganizerTest extends WPRestApiTestCase {
	/**
	 * @var \Tribe__REST__Messages_Interface
	 */
	protected $messages;

	/**
	 * @var \Tribe__Events__REST__Interfaces__Post_Repository
	 */
	protected $repository;

	/**
	 * @var \Tribe__Events__REST__V1__Validator__Interface
	 */
	protected $validator;

	function setUp() {
		parent::setUp();
		$this->factory()->event = new Event();
		$this->factory()->venue = new Venue();
		$this->factory()->organizer = new Organizer();
		$this->messages = new \Tribe__Events__REST__V1__Messages();
		$this->repository = new \Tribe__Events__REST__V1__Post_Repository( new \Tribe__Events__REST__V1__Messages() );
		$this->validator = new \Tribe__Events__REST__V1__Validator__Base();
	}

	/**
	 * It should be instantiatable
	 *
	 * @test
	 */
	public function be_instantiatable() {
		$this->assertInstanceOf( Single_Organizer::class, $this->make_instance() );
	}

	/**
	 * @return Single_Organizer
	 */
	protected function make_instance() {
		$messages = $this->messages instanceof ObjectProphecy ? $this->messages->reveal() : $this->messages;
		$repository = $this->repository instanceof ObjectProphecy ? $this->repository->reveal() : $this->repository;
		$validator = $this->validator instanceof ObjectProphecy ? $this->validator->reveal() : $this->validator;

		return new Single_Organizer( $messages, $repository, $validator );
	}

	/**
	 * It should return the organizer data when requesting existing organizer ID
	 *
	 * @test
	 */
	public function it_should_return_the_organizer_data_when_requesting_existing_organizer_id() {
		$sut = $this->make_instance();
		$request = new \WP_REST_Request();
		$organizer_id = $this->factory()->organizer->create();
		$request->set_param( 'id', $organizer_id );

		$response = $sut->get( $request );

		$this->assertInstanceOf( \WP_REST_Response::class, $response );
		$this->assertNotEmpty( $response->get_data() );
		$this->assertEquals( $organizer_id, $response->data['id'] );
	}

	/**
	 * It should return a WP_Error if organizer is not public and user cannot read it
	 *
	 * @test
	 */
	public function it_should_return_a_wp_error_if_organizer_is_not_public_and_user_cannot_read_it() {
		$sut = $this->make_instance();
		$request = new \WP_REST_Request();
		$organizer_id = $this->factory()->organizer->create( [ 'post_status' => 'draft' ] );
		$request->set_param( 'id', $organizer_id );

		wp_set_current_user( $this->factory()->user->create( [ 'role' => 'subscriber' ] ) );
		$response = $sut->get( $request );

		$this->assertErrorResponse( 'organizer-not-accessible', $response, 403 );
	}
}
