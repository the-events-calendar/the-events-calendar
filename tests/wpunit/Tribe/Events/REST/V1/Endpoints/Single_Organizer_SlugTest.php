<?php

namespace Tribe\Events\REST\V1\Endpoints;

use Codeception\TestCase\WPRestApiTestCase;
use Prophecy\Prophecy\ObjectProphecy;
use Tribe\Events\Test\Factories\Event;
use Tribe\Events\Test\Factories\Organizer;
use Tribe\Events\Test\Factories\Venue;
use Tribe__Events__REST__V1__Endpoints__Single_Organizer_Slug as Single_Organizer_Slug;

class Single_Organizer_SlugTest extends WPRestApiTestCase {
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
		$this->assertInstanceOf( Single_Organizer_Slug::class, $this->make_instance() );
	}

	/**
	 * @return Single_Organizer_Slug
	 */
	protected function make_instance() {
		$messages = $this->messages instanceof ObjectProphecy ? $this->messages->reveal() : $this->messages;
		$repository = $this->repository instanceof ObjectProphecy ? $this->repository->reveal() : $this->repository;
		$validator = $this->validator instanceof ObjectProphecy ? $this->validator->reveal() : $this->validator;

		return new Single_Organizer_Slug( $messages, $repository, $validator );
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

		$post = get_post( $organizer_id );

		$request->set_param( 'slug', $post->post_name );

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

		$post = get_post( $organizer_id );

		$request->set_param( 'slug', $post->post_name );

		wp_set_current_user( $this->factory()->user->create( [ 'role' => 'subscriber' ] ) );
		$response = $sut->get( $request );

		$this->assertErrorResponse( 'organizer-not-accessible', $response, 403 );
	}

	/**
	 * It should allow inserting a organizer
	 *
	 * @test
	 */
	public function it_should_allow_inserting_a_organizer() {
		$data = [
			'organizer' => 'A organizer',
			'phone'     => 'Organizer phone',
			'email'     => 'doe@john.com',
			'website'   => 'http://organizer.com',
		];
		$request = new \WP_REST_Request();
		foreach ( $data as $key => $value ) {
			$request->set_param( $key, $value );
		}

		$sut = $this->make_instance();
		$response = $sut->create( $request );

		$this->assertInstanceOf( \WP_REST_Response::class, $response );
	}

	/**
	 * It should return WP_Error if organizer could not be created
	 *
	 * @test
	 */
	public function it_should_return_wp_error_if_organizer_could_not_be_created() {
		$request = new \WP_REST_Request();
		$request->set_param( 'organizer', 'A organizer' );
		add_filter( 'tribe_events_tribe_organizer_create', function () {
			return false;
		} );

		$sut = $this->make_instance();
		/** @var \WP_Error $response */
		$response = $sut->create( $request );

		$this->assertWPError( $response );
		$this->assertEquals( 'could-not-create-organizer', $response->get_error_code() );
	}

	/**
	 * It should return the inserted organizer ID when requesting just the organizer ID
	 *
	 * @test
	 */
	public function it_should_return_the_inserted_organizer_id_when_requesting_just_the_organizer_id() {
		$request = new \WP_REST_Request();
		$request->set_param( 'organizer', 'A organizer' );

		$sut = $this->make_instance();
		$response = $sut->create( $request, true );

		$this->assertTrue( tribe_is_organizer( $response ) );
	}
	/**
	 * It should allow updating a organizer
	 *
	 * @test
	 */
	public function it_should_allow_updating_a_organizer() {
		$organizer_id = $this->factory()->organizer->create();

		$post = get_post( $organizer_id );

		$data = [
			'slug'      => $post->post_name,
			'organizer' => 'A organizer',
			'phone'     => 'Organizer phone',
			'email'     => 'doe@john.com',
			'website'   => 'http://organizer.com',
		];
		$request = new \WP_REST_Request();
		foreach ( $data as $key => $value ) {
			$request->set_param( $key, $value );
		}

		$sut = $this->make_instance();
		$response = $sut->update( $request );

		$this->assertInstanceOf( \WP_REST_Response::class, $response );
	}

	/**
	 * It should return WP_Error if organizer could not be updated
	 *
	 * @test
	 */
	public function it_should_return_wp_error_if_organizer_could_not_be_updated() {
		$organizer_id = $this->factory()->organizer->create();

		$post = get_post( $organizer_id );

		$request = new \WP_REST_Request();
		$request->set_param( 'slug', $post->post_name );
		$request->set_param( 'organizer', 'A organizer' );
		add_filter( 'tribe_events_tribe_organizer_update', function () {
			return false;
		} );

		$sut = $this->make_instance();
		/** @var \WP_Error $response */
		$response = $sut->update( $request );

		$this->assertWPError( $response );
		$this->assertEquals( 'could-not-update-organizer', $response->get_error_code() );
	}
}
