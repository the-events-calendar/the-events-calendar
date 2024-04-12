<?php

namespace Tribe\Events\REST\V1\Endpoints;

use Prophecy\Argument;
use Prophecy\Prophecy\ObjectProphecy;
use Tribe\Events\Test\Factories\Event;
use Tribe\Events\Test\Factories\Organizer;
use Tribe\Events\Test\Factories\Venue;
use Tribe__Events__REST__V1__Endpoints__Single_Event_Slug as Endpoint;

class Single_Event_SlugTest extends \Codeception\TestCase\WPRestApiTestCase {
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

	public function setUp(): void {
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

		$id = $this->factory()->event->create( [ 'post_status' => 'draft' ] );

		$post = get_post( $id );

		$request->set_param( 'slug', $post->post_name );

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

		$post = get_post( $id );

		$request->set_param( 'slug', $post->post_name );

		$this->repository = $this->prophesize( \Tribe__Events__REST__V1__Post_Repository::class );
		$this->repository->get_event_data( $id, Argument::type( 'string' ) )->willReturn( [ 'some' => 'data' ] );

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
	 * It should allow creating an event
	 *
	 * @test
	 */
	public function it_should_allow_creating_an_event() {
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
		$response = $sut->create( $request );

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
			'venue'       => PHP_INT_MAX,
		];

		foreach ( $params as $key => $value ) {
			$request->set_param( $key, $value );
		}

		/** @var \WP_Error $response */
		$response = $sut->create( $request );

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
			'organizer'   => PHP_INT_MAX,
		];

		foreach ( $params as $key => $value ) {
			$request->set_param( $key, $value );
		}

		/** @var \WP_Error $response */
		$response = $sut->create( $request );

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
		$response = $sut->create( $request );

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
			'slug' => [
				'in'                => 'path',
				'swagger_type'      => 'string',
				'required'          => true,
				'validate_callback' => [ $this->validator, 'is_event_id' ]
			],
		];

		$expected = [
			[
				'name'        => 'slug',
				'in'          => 'path',
				'schema'      => [ 'type' => 'string' ],
				'description' => 'No description',
				'required'    => true,
			],
		];

		$this->assertEqualSets( $expected, $sut->swaggerize_args( $args, [ 'description' => 'No description' ] ) );
	}

	/**
	 * It should allow deleting an event
	 * @test
	 */
	public function it_should_allow_deleting_an_event() {
		$request = new \WP_REST_Request();

		$id = $this->factory()->event->create();

		$post = get_post( $id );

		$request->set_param( 'slug', $post->post_name );

		$sut      = $this->make_instance();
		$response = $sut->delete( $request );

		$this->assertInstanceOf( \WP_REST_Response::class, $response );
		$data = $response->get_data();
		$this->assertEquals( 'trash', ( get_post( $id )->post_status ) );
		$this->assertEquals( $id, $data['id'] );
	}

	/**
	 * It should return error if event has been deleted already
	 * @test
	 */
	public function it_should_return_error_if_event_has_been_deleted_already() {
		$id = $this->factory()->event->create();
		wp_trash_post( $id );

		$post = get_post( $id );

		$request = new \WP_REST_Request();
		$request->set_param( 'slug', $post->post_name );

		$sut      = $this->make_instance();
		$response = $sut->delete( $request );

		$this->assertInstanceOf( \WP_Error::class, $response );
		$this->assertEquals( 'event-is-in-trash', $response->get_error_code() );
	}

	/**
	 * It should return an error if event cannot be trashed
	 * @test
	 */
	public function it_should_return_an_error_if_event_cannot_be_trashed() {
		$request = new \WP_REST_Request();

		$id = $this->factory()->event->create();

		$post = get_post( $id );

		$request->set_param( 'slug', $post->post_name );

		add_filter( 'tribe_events_rest_event_delete', '__return_false' );

		$sut      = $this->make_instance();
		$response = $sut->delete( $request );

		$this->assertInstanceOf( \WP_Error::class, $response );
		$this->assertEquals( 'could-not-delete-event', $response->get_error_code() );
	}

	/**
	 * It should allow updating an event
	 *
	 * @test
	 */
	public function it_should_allow_updating_an_event() {
		$sut = $this->make_instance();

		$request = new \WP_REST_Request();

		$id = $this->factory()->event->create();

		$venue       = $this->factory()->venue->create();
		$organizer_1 = $this->factory()->organizer->create();
		$organizer_2 = $this->factory()->organizer->create();

		$post = get_post( $id );

		$params = [
			'slug'        => $post->post_name,
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
		$response = $sut->update( $request );

		$this->assertInstanceOf( \WP_REST_Response::class, $response );
		$this->assertEquals( 200, $response->status );
	}

	/**
	 * It should return venue error if trying to insert event with invalid venue data in update
	 *
	 * @test
	 */
	public function it_should_return_venue_error_if_trying_to_insert_event_with_invalid_venue_data_in_update() {
		$sut = $this->make_instance();

		$request = new \WP_REST_Request();

		$id = $this->factory()->event->create();

		$post = get_post( $id );

		$params = [
			'slug'        => $post->post_name,
			'title'       => 'An event',
			'description' => 'An event content',
			'start_date'  => 'tomorrow 9am',
			'end_date'    => 'tomorrow 11am',
			'venue'       => PHP_INT_MAX,
		];

		foreach ( $params as $key => $value ) {
			$request->set_param( $key, $value );
		}

		/** @var \WP_Error $response */
		$response = $sut->update( $request );

		$this->assertWPError( $response );
		$this->assertEquals( 'could-not-create-venue', $response->get_error_code() );
		$error_data = $response->get_error_data();
		$this->assertEquals( 400, $error_data['status'] );
	}

	/**
	 * It should return organizer error if trying to insert organizer with invalid organizer data in update
	 *
	 * @test
	 */
	public function it_should_return_organizer_error_if_trying_to_insert_organizer_with_invalid_organizer_data_in_update() {
		$sut = $this->make_instance();

		$request = new \WP_REST_Request();

		$id = $this->factory()->event->create();

		$post = get_post( $id );

		$params = [
			'slug'        => $post->post_name,
			'title'       => 'An event',
			'description' => 'An event content',
			'start_date'  => 'tomorrow 9am',
			'end_date'    => 'tomorrow 11am',
			'organizer'   => PHP_INT_MAX,
		];

		foreach ( $params as $key => $value ) {
			$request->set_param( $key, $value );
		}

		/** @var \WP_Error $response */
		$response = $sut->update( $request );

		$this->assertWPError( $response );
		$this->assertEquals( 'could-not-create-organizer', $response->get_error_code() );
		$error_data = $response->get_error_data();
		$this->assertEquals( 400, $error_data['status'] );
	}

	/**
	 * It should allow some users that can publish posts to set presentation data in update
	 *
	 * @test
	 */
	public function it_should_allow_some_users_that_can_publish_posts_and_edit_others_posts_to_set_presentation_data_in_update() {
		wp_set_current_user( $this->factory()->user->create( [ 'role' => 'editor' ] ) );

		$sut = $this->make_instance();

		$request = new \WP_REST_Request();

		$id = $this->factory()->event->create();

		$post = get_post( $id );

		$params = [
			'slug'        => $post->post_name,
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
		$response = $sut->update( $request );

		$this->assertInstanceOf( \WP_REST_Response::class, $response );
		$this->assertEquals( 200, $response->get_status() );

		$data = $response->get_data();
		$this->assertNotEmpty( $data['featured'] );
	}
}
