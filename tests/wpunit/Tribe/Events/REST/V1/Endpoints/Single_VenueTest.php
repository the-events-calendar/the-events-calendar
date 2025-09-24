<?php

namespace Tribe\Events\REST\V1\Endpoints;

use Codeception\TestCase\WPRestApiTestCase;
use Prophecy\Prophecy\ObjectProphecy;
use Tribe\Events\Test\Factories\Event;
use Tribe\Events\Test\Factories\Organizer;
use Tribe\Events\Test\Factories\Venue;
use Tribe__Events__REST__V1__Endpoints__Single_Venue as Single_Venue;
use tad\Codeception\SnapshotAssertions\SnapshotAssertions;
use Tribe\Tests\Traits\With_Clock_Mock;
use Tribe__Date_Utils as Dates;

class Single_VenueTest extends WPRestApiTestCase {
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
		$this->assertInstanceOf( Single_Venue::class, $this->make_instance() );
	}

	/**
	 * @return Single_Venue
	 */
	protected function make_instance() {
		$messages = $this->messages instanceof ObjectProphecy ? $this->messages->reveal() : $this->messages;
		$repository = $this->repository instanceof ObjectProphecy ? $this->repository->reveal() : $this->repository;
		$validator = $this->validator instanceof ObjectProphecy ? $this->validator->reveal() : $this->validator;

		return new Single_Venue( $messages, $repository, $validator );
	}

	/**
	 * It should return the venue data when requesting existing venue ID
	 *
	 * @test
	 */
	public function it_should_return_the_venue_data_when_requesting_existing_venue_id() {
		$sut = $this->make_instance();
		$request = new \WP_REST_Request();
		$venue_id = $this->factory()->venue->create();
		$request->set_param( 'id', $venue_id );

		$response = $sut->get( $request );

		$this->assertInstanceOf( \WP_REST_Response::class, $response );
		$this->assertNotEmpty( $response->get_data() );
		$this->assertEquals( $venue_id, $response->data['id'] );
	}

	/**
	 * @test
	 */
	public function it_should_hide_password_protected_fields() {
		$request = new \WP_REST_Request( 'GET', '' );
		$this->freeze_time( Dates::immutable( '2024-06-13 17:25:00' ) );
		$venue_id = $this->factory()->venue->create( [ 'use_time_for_generation' => true ]);

		$this->assertEquals( '2024-06-13 17:25:00', date( 'Y-m-d H:i:s' ) );

		wp_update_post( [
			'ID' => $venue_id,
			'post_password' => 'password',
		] );

		$request->set_param( 'id', $venue_id );

		$sut = $this->make_instance();
		$response = $sut->get( $request );

		$data = $response->get_data();
		$this->assertInstanceOf( \WP_REST_Response::class, $response );

		$json = wp_json_encode( $data, JSON_PRETTY_PRINT );
		$json = str_replace(
			array_map( static fn( $id ) => '"id": ' . $id, [ $venue_id ] ),
			'"id": "{VENUE_ID}"',
			$json
		);
		$json = str_replace(
			array_map( static fn( $id ) => '?id=' . $id, [ $venue_id ] ),
			'?id={VENUE_ID}',
			$json
		);
		$json = str_replace(
			array_map( static fn( $id ) => '\/venues\/' . $id, [ $venue_id ] ),
			'\/venues\/{VENUE_ID}',
			$json
		);
		$json = str_replace( (string) $venue_id, '{VENUE_ID}', $json );
		$json = preg_replace( '/post-title-[\d]+/', 'post-title-{NUMBER}', $json );
		$json = preg_replace( '/post-slug-[\d]+/', 'post-slug-{NUMBER}', $json );
		$json = preg_replace( '/Post title [\d]+/', 'Post title {NUMBER}', $json );
		$json = preg_replace( '/Post content [\d]+/', 'Post content {NUMBER}', $json );
		$json = preg_replace( '/Post excerpt [\d]+/', 'Post excerpt {NUMBER}', $json );
		$this->assertMatchesJsonSnapshot( $json );
	}

	/**
	 * It should return a WP_Error if venue is not public and user cannot read it
	 *
	 * @test
	 */
	public function it_should_return_a_wp_error_if_venue_is_not_public_and_user_cannot_read_it() {
		$sut = $this->make_instance();
		$request = new \WP_REST_Request();
		$venue_id = $this->factory()->venue->create( [ 'post_status' => 'draft' ] );
		$request->set_param( 'id', $venue_id );

		wp_set_current_user( $this->factory()->user->create( [ 'role' => 'subscriber' ] ) );
		$response = $sut->get( $request );

		$this->assertErrorResponse( 'venue-not-accessible', $response, 403 );
	}

	/**
	 * It should allow inserting a venue
	 *
	 * @test
	 */
	public function it_should_allow_inserting_a_venue() {
		$data = [
			'venue'         => 'A venue',
			'show_map'      => false,
			'show_map_link' => false,
			'address'       => 'Venue address',
			'city'          => 'Venue city',
			'country'       => 'Venue country',
			'province'      => 'Venue province',
			'state'         => 'Venue state',
			'stateprovince' => 'Venue stateprovince',
			'zip'           => 'Venue zip',
			'phone'         => 'Venue phone',
			'website'       => 'http://venue.com',
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
	 * It should properly set boolean meta fields
	 *
	 * @test
	 */
	public function it_should_properly_set_boolean_meta_fields() {
		$data = [
			'venue'         => 'A venue',
			'show_map'      => false,
			'show_map_link' => false,
		];

		$request = new \WP_REST_Request();
		foreach ( $data as $key => $value ) {
			$request->set_param( $key, $value );
		}

		$sut = $this->make_instance();
		$response = $sut->create( $request );

		$this->assertInstanceOf( \WP_REST_Response::class, $response );
		$this->assertFalse( $response->data['show_map'] );
		$this->assertFalse( $response->data['show_map_link'] );

		$data = [
			'venue'         => 'A second venue',
			'show_map'      => true,
			'show_map_link' => true,
		];

		$request = new \WP_REST_Request();
		foreach ( $data as $key => $value ) {
			$request->set_param( $key, $value );
		}

		$sut = $this->make_instance();
		$response = $sut->create( $request );

		$this->assertInstanceOf( \WP_REST_Response::class, $response );
		$this->assertTrue( $response->data['show_map'] );
		$this->assertTrue( $response->data['show_map_link'] );
	}

	/**
	 * It should return WP_Error if venue could not be created
	 *
	 * @test
	 */
	public function it_should_return_wp_error_if_venue_could_not_be_created() {
		$request = new \WP_REST_Request();
		$request->set_param( 'venue', 'A venue' );
		add_filter( 'tribe_events_tribe_venue_create', function () {
			return false;
		} );

		$sut = $this->make_instance();
		/** @var \WP_Error $response */
		$response = $sut->create( $request );

		$this->assertWPError( $response );
		$this->assertEquals( 'could-not-create-venue', $response->get_error_code() );
	}

	/**
	 * It should return the inserted venue ID when requesting just the venue ID
	 *
	 * @test
	 */
	public function it_should_return_the_inserted_venue_id_when_requesting_just_the_venue_id() {
		$request = new \WP_REST_Request();
		$request->set_param( 'venue', 'A venue' );

		$sut = $this->make_instance();
		$response = $sut->create( $request, true );

		$this->assertTrue( tribe_is_venue( $response ) );
	}

	/**
	 * It should allow updating a venue
	 *
	 * @test
	 */
	public function it_should_allow_updating_a_venue() {
		$venue = $this->factory()->venue->create();

		$data = [
			'id'            => $venue,
			'venue'         => 'A venue',
			'show_map'      => false,
			'show_map_link' => false,
			'address'       => 'Venue address',
			'city'          => 'Venue city',
			'country'       => 'Venue country',
			'province'      => 'Venue province',
			'state'         => 'Venue state',
			'stateprovince' => 'Venue stateprovince',
			'zip'           => 'Venue zip',
			'phone'         => 'Venue phone',
			'website'       => 'http://venue.com',
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
	 * It should properly set boolean meta fields
	 *
	 * @test
	 */
	public function it_should_properly_set_boolean_meta_fields_when_updating() {
		$venue = $this->factory()->venue->create();

		$data = [
			'id'            => $venue,
			'venue'         => 'A venue',
			'show_map'      => false,
			'show_map_link' => false,
		];

		$request = new \WP_REST_Request();
		foreach ( $data as $key => $value ) {
			$request->set_param( $key, $value );
		}

		$sut = $this->make_instance();
		$response = $sut->create( $request );

		$this->assertInstanceOf( \WP_REST_Response::class, $response );
		$this->assertFalse( $response->data['show_map'] );
		$this->assertFalse( $response->data['show_map_link'] );

		$data = [
			'id'            => $venue,
			'venue'         => 'A venue',
			'show_map'      => true,
			'show_map_link' => true,
		];

		$request = new \WP_REST_Request();
		foreach ( $data as $key => $value ) {
			$request->set_param( $key, $value );
		}

		$sut = $this->make_instance();
		$response = $sut->update( $request );

		$this->assertInstanceOf( \WP_REST_Response::class, $response );
		$this->assertTrue( $response->data['show_map'] );
		$this->assertTrue( $response->data['show_map_link'] );
	}

	/**
	 * It should return WP_Error if venue could not be updated
	 *
	 * @test
	 */
	public function it_should_return_wp_error_if_venue_could_not_be_updated() {
		$venue = $this->factory()->venue->create();

		$request = new \WP_REST_Request();
		$request->set_param( 'id', $venue );
		$request->set_param( 'venue', 'A venue' );
		add_filter( 'tribe_events_tribe_venue_update', function () {
			return false;
		} );

		$sut = $this->make_instance();
		/** @var \WP_Error $response */
		$response = $sut->update( $request );

		$this->assertWPError( $response );
		$this->assertEquals( 'could-not-update-venue', $response->get_error_code() );
	}
}
