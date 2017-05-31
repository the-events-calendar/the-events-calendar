<?php

namespace Tribe\Events\REST\V1\Endpoints;

use Prophecy\Prophecy\ObjectProphecy;
use Tribe\Events\Tests\Factories\Event;
use Tribe\Events\Tests\Factories\Organizer;
use Tribe\Events\Tests\Factories\Venue;
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

	/**
	 * @var \Tribe__Validator__Interface
	 */
	protected $validator;

	/**
	 * @var \Tribe__Events__REST__V1__Endpoints__Linked_Post_Endpoint_Interface
	 */
	protected $venue_endpoint;

	/**
	 * @var \Tribe__Events__REST__V1__Endpoints__Linked_Post_Endpoint_Interface
	 */
	protected $organizer_endpoint;

	public function setUp() {
		// before
		parent::setUp();

		// your set up methods here
		$this->factory()->event = new Event();
		$this->factory()->venue = new Venue();
		$this->factory()->organizer = new Organizer();
		$this->messages = new \Tribe__Events__REST__V1__Messages();
		$this->repository = new \Tribe__Events__REST__V1__Post_Repository( new \Tribe__Events__REST__V1__Messages() );
		$this->validator = new \Tribe__Events__REST__V1__Validator__Base();
		$this->venue_endpoint = tribe( 'tec.rest-v1.endpoints.single-venue' );
		$this->organizer_endpoint = tribe( 'tec.rest-v1.endpoints.single-organizer' );
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
	 * @return Endpoint
	 */
	private function make_instance() {
		$messages = $this->messages instanceof ObjectProphecy ? $this->messages->reveal() : $this->messages;
		$repository = $this->repository instanceof ObjectProphecy ? $this->repository->reveal() : $this->repository;
		$validator = $this->validator instanceof ObjectProphecy ? $this->validator->reveal() : $this->validator;
		$venue_endpoint = $this->venue_endpoint instanceof ObjectProphecy ? $this->venue_endpoint->reveal() : $this->venue_endpoint;
		$organizer_endpoint = $this->organizer_endpoint instanceof ObjectProphecy ? $this->organizer_endpoint->reveal() :
			$this->organizer_endpoint;

		return new Endpoint( $messages, $repository, $validator, $venue_endpoint, $organizer_endpoint );
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

	public function post_stati_and_user_roles() {
		return [
			[ 'publish', 'administrator', 'publish' ],
			[ 'publish', 'editor', 'publish' ],
			[ 'publish', 'author', 'publish' ],
			[ 'publish', 'contributor', 'pending' ],
			[ 'publish', 'subscriber', 'pending' ],
			[ 'future', 'administrator', 'future' ],
			[ 'future', 'editor', 'future' ],
			[ 'future', 'author', 'future' ],
			[ 'future', 'contributor', 'pending' ],
			[ 'future', 'subscriber', 'pending' ],
			[ 'draft', 'contributor', 'draft' ],
			[ 'draft', 'subscriber', 'draft' ],
		];
	}

	/**
	 * It should correctly scale back post status *
	 *
	 * @test
	 * @dataProvider post_stati_and_user_roles
	 */
	public function it_should_correctly_scale_back_post_status( $post_status, $role, $expected ) {
		$user_id = $this->factory()->user->create( [ 'role' => $role ] );
		wp_set_current_user( $user_id );

		$sut = $this->make_instance();

		$this->assertEquals( $expected, $sut->scale_back_post_status( $post_status, \Tribe__Events__Main::POSTTYPE ) );
	}

	/**
	 * It should allow inserting (POSTing) an event
	 *
	 * @test
	 */
	public function it_should_allow_inserting_pos_ting_an_event() {
		$sut = $this->make_instance();

		$request = new \WP_REST_Request();

		$venue = $this->factory()->venue->create();
		$organizer_1 = $this->factory()->organizer->create();
		$organizer_2 = $this->factory()->organizer->create();

		$params = [
			'title'       => 'An event',
			'description' => 'An event content',
			'start_date'  => 'tomorrow 9am',
			'end_date'    => 'tomorrow 11am',
			'categories'  => [ 'cat1', 'cat2' ],
			'tags'        => [ 'tag1', 'tag2' ],
			'venue'       => $venue,
			'organizer'   => [ $organizer_1, $organizer_2 ],
		];

		foreach ( $params as $key => $value ) {
			$request->set_param( $key, $value );
		}

		/** @var \WP_REST_Response $response */
		$response = $sut->post( $request );

		$this->assertInstanceOf( \WP_REST_Response::class, $response );
		$this->assertEquals( 201, $response->status );
	}

	/**
	 * It should return venue error if trying to insert event with invalid venue data
	 *
	 * @test
	 */
	public function it_should_return_venue_error_if_trying_to_insert_event_with_invalid_venue_data() {
		$sut = $this->make_instance();

		$request = new \WP_REST_Request();

		$params = [
			'title'       => 'An event',
			'description' => 'An event content',
			'start_date'  => 'tomorrow 9am',
			'end_date'    => 'tomorrow 11am',
			'venue'       => 23,
		];

		foreach ( $params as $key => $value ) {
			$request->set_param( $key, $value );
		}

		/** @var \WP_Error $response */
		$response = $sut->post( $request );

		$this->assertWPError( $response );
		$this->assertEquals( 'could-not-create-venue', $response->get_error_code() );
		$error_data = $response->get_error_data();
		$this->assertEquals( 400, $error_data['status'] );
	}

	/**
	 * It should return organizer error if trying to insert organizer with invalid organizer data
	 *
	 * @test
	 */
	public function it_should_return_organizer_error_if_trying_to_insert_organizer_with_invalid_organizer_data() {
		$sut = $this->make_instance();

		$request = new \WP_REST_Request();

		$params = [
			'title'       => 'An event',
			'description' => 'An event content',
			'start_date'  => 'tomorrow 9am',
			'end_date'    => 'tomorrow 11am',
			'organizer'   => 23,
		];

		foreach ( $params as $key => $value ) {
			$request->set_param( $key, $value );
		}

		/** @var \WP_Error $response */
		$response = $sut->post( $request );

		$this->assertWPError( $response );
		$this->assertEquals( 'could-not-create-organizer', $response->get_error_code() );
		$error_data = $response->get_error_data();
		$this->assertEquals( 400, $error_data['status'] );
	}

	/**
	 * It should allow some users that can publish posts to set presentation data
	 *
	 * @test
	 */
	public function it_should_allow_some_users_that_can_publish_posts_and_edit_others_posts_to_set_presentation_data() {
		wp_set_current_user( $this->factory()->user->create( [ 'role' => 'editor' ] ) );

		$sut = $this->make_instance();

		$request = new \WP_REST_Request();

		$params = [
			'title'       => 'An event',
			'description' => 'An event content',
			'start_date'  => 'tomorrow 9am',
			'end_date'    => 'tomorrow 11am',
			'featured'    => true,
		];

		foreach ( $params as $key => $value ) {
			$request->set_param( $key, $value );
		}

		/** @var \WP_REST_Response $response */
		$response = $sut->post( $request );

		$this->assertInstanceOf( \WP_REST_Response::class, $response );
		$this->assertEquals( 201, $response->get_status() );

		$data = $response->get_data();
		$this->assertNotEmpty( $data['featured'] );
	}

	/**
	 * Test swaggerize_args
	 *
	 * @test
	 */
	public function test_swaggerize_args() {
		$sut = $this->make_instance();

		$this->assertEmpty( $sut->swaggerize_args( [] ) );
		$args = [
			'id' => [
				'in'                => 'path',
				'type'              => 'integer',
				'description'       => 'Param description',
				'required'          => true,
				'validate_callback' => [ $this->validator, 'is_event_id' ]
			],
		];

		$expected = [
			[
				'name'        => 'id',
				'in'          => 'path',
				'type'        => 'integer',
				'description' => 'Param description',
				'required'    => true,
				'default'     => '',
			],
		];

		$this->assertEqualSets( $expected, $sut->swaggerize_args( $args ) );
	}
}
