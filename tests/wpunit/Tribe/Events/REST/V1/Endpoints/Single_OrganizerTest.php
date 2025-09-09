<?php

namespace Tribe\Events\REST\V1\Endpoints;

use Codeception\TestCase\WPRestApiTestCase;
use Prophecy\Prophecy\ObjectProphecy;
use Tribe\Events\Test\Factories\Event;
use Tribe\Events\Test\Factories\Organizer;
use Tribe\Events\Test\Factories\Venue;
use Tribe__Events__REST__V1__Endpoints__Single_Organizer as Single_Organizer;
use tad\Codeception\SnapshotAssertions\SnapshotAssertions;
use Tribe\Tests\Traits\With_Clock_Mock;
use Tribe__Date_Utils as Dates;

class Single_OrganizerTest extends WPRestApiTestCase {
	use With_Clock_Mock;
	use SnapshotAssertions;

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
	 * @test
	 */
	public function it_should_hide_password_protected_fields() {
		$request = new \WP_REST_Request( 'GET', '' );
		$this->freeze_time( Dates::immutable( '2024-06-13 17:25:00' ) );
		$organizer_id = $this->factory()->organizer->create( [ 'use_time_for_generation' => true ]);

		$this->assertEquals( '2024-06-13 17:25:00', date( 'Y-m-d H:i:s' ) );

		wp_update_post( [
			'ID' => $organizer_id,
			'post_password' => 'password',
		] );

		$request->set_param( 'id', $organizer_id );

		$sut = $this->make_instance();
		$response = $sut->get( $request );

		$data = $response->get_data();
		$this->assertInstanceOf( \WP_REST_Response::class, $response );

		$json = wp_json_encode( $data, JSON_PRETTY_PRINT );
		$json = str_replace(
			array_map( static fn( $id ) => '"id": ' . $id, [ $organizer_id ] ),
			'"id": "{ORGANIZER_ID}"',
			$json
		);
		$json = str_replace(
			array_map( static fn( $id ) => '?id=' . $id, [ $organizer_id ] ),
			'?id={ORGANIZER_ID}',
			$json
		);
		$json = str_replace(
			array_map( static fn( $id ) => '\/organizers\/' . $id, [ $organizer_id ] ),
			'\/organizers\/{ORGANIZER_ID}',
			$json
		);
		$json = str_replace( $organizer_id, '{ORGANIZER_ID}', $json );
		$json = preg_replace( '/post-title-[\d]+/', 'post-title-{NUMBER}', $json );
		$json = preg_replace( '/post-slug-[\d]+/', 'post-slug-{NUMBER}', $json );
		$json = preg_replace( '/Post title [\d]+/', 'Post title {NUMBER}', $json );
		$json = preg_replace( '/Post content [\d]+/', 'Post content {NUMBER}', $json );
		$json = preg_replace( '/Post excerpt [\d]+/', 'Post excerpt {NUMBER}', $json );
		$this->assertMatchesJsonSnapshot( $json );
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
		$organizer = $this->factory()->organizer->create();

		$data = [
			'id'        => $organizer,
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
		$organizer = $this->factory()->organizer->create();

		$request = new \WP_REST_Request();
		$request->set_param( 'id', $organizer );
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
