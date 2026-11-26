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
use Tribe__Events__Main;
use WP_REST_Request;
use WP_REST_Response;

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
		// Creating a venue requires a user who may also read the resulting draft back.
		wp_set_current_user( $this->factory()->user->create( [ 'role' => 'editor' ] ) );
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
	 * @test
	 */
	public function it_should_keep_the_author_when_updating_without_one() {
		$original_author = $this->factory()->user->create( [ 'role' => 'author' ] );
		$venue           = $this->factory()->venue->create( [ 'post_author' => $original_author ] );

		wp_set_current_user( $this->factory()->user->create( [ 'role' => 'administrator' ] ) );

		$request = new \WP_REST_Request();
		$request->set_param( 'id', $venue );
		$request->set_param( 'city', 'Venue city' );

		$response = $this->make_instance()->update( $request );

		$this->assertInstanceOf( \WP_REST_Response::class, $response );
		$this->assertEquals( $original_author, get_post_field( 'post_author', $venue ) );
	}

	/**
	 * @test
	 */
	public function it_should_check_the_venue_capability_when_setting_the_author_on_create() {
		$current_user = $this->factory()->user->create( [ 'role' => 'editor' ] );
		$other_user   = $this->factory()->user->create( [ 'role' => 'administrator' ] );

		// The user may still set other authors on events, just not on venues.
		get_user_by( 'id', $current_user )->add_cap( get_post_type_object( \Tribe__Events__Main::VENUE_POST_TYPE )->cap->edit_others_posts, false );
		$this->assertTrue( user_can( $current_user, get_post_type_object( \Tribe__Events__Main::POSTTYPE )->cap->edit_others_posts ) );
		wp_set_current_user( $current_user );

		$request = new \WP_REST_Request();
		$request->set_param( 'venue', 'A venue' );
		$request->set_param( 'author', $other_user );

		$venue = $this->make_instance()->create( $request, true );

		$this->assertTrue( tribe_is_venue( $venue ) );
		$this->assertEquals( $current_user, get_post_field( 'post_author', $venue ) );
	}

	/**
	 * @test
	 */
	public function it_should_check_the_venue_capability_when_setting_the_author() {
		$current_user = $this->factory()->user->create( [ 'role' => 'editor' ] );
		$other_user   = $this->factory()->user->create( [ 'role' => 'administrator' ] );
		$venue        = $this->factory()->venue->create( [ 'post_author' => $current_user ] );

		// The user may still set other authors on events, just not on venues.
		get_user_by( 'id', $current_user )->add_cap( get_post_type_object( \Tribe__Events__Main::VENUE_POST_TYPE )->cap->edit_others_posts, false );
		wp_set_current_user( $current_user );

		$request = new \WP_REST_Request();
		$request->set_param( 'id', $venue );
		$request->set_param( 'author', $other_user );

		$response = $this->make_instance()->update( $request );

		$this->assertInstanceOf( \WP_REST_Response::class, $response );
		$this->assertEquals( $current_user, get_post_field( 'post_author', $venue ) );
	}

	/**
	 * It should properly set boolean meta fields
	 *
	 * @test
	 */
	public function it_should_properly_set_boolean_meta_fields_when_updating() {
		// Creating a venue requires a user who may also read the resulting draft back.
		wp_set_current_user( $this->factory()->user->create( [ 'role' => 'editor' ] ) );
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

	/**
	 * @test
	 */
	public function it_should_not_write_to_a_matching_venue_the_user_cannot_edit() {
		$existing_data = [
			'venue'   => 'Shared Venue',
			'address' => '1 Original Road',
			'phone'   => '555-0100',
		];
		wp_set_current_user( $this->factory()->user->create( [ 'role' => 'administrator' ] ) );
		$existing_id = $this->make_instance()->create( $this->make_request( $existing_data ), true );

		wp_set_current_user( $this->factory()->user->create( [ 'role' => 'contributor' ] ) );
		$response = $this->make_instance()->create(
			$this->make_request(
				[
					'venue'   => $existing_data['venue'],
					'address' => '2 Submitted Road',
					'phone'   => '555-0199',
				]
			)
		);

		$this->assertInstanceOf( WP_REST_Response::class, $response );
		$this->assertNotEquals( $existing_id, $response->get_data()['id'] );
		$this->assertEquals( $existing_data['address'], tribe_get_address( $existing_id ) );
		$this->assertEquals( $existing_data['phone'], tribe_get_phone( $existing_id ) );
	}

	/**
	 * Builds a request carrying the given body parameters.
	 *
	 * @param array<string,string> $params The request parameters.
	 *
	 * @return WP_REST_Request The request.
	 */
	private function make_request( array $params ): WP_REST_Request {
		$request = new WP_REST_Request();
		foreach ( $params as $key => $value ) {
			$request->set_param( $key, $value );
		}

		return $request;
	}

	/**
	 * @test
	 */
	public function it_should_check_the_venue_capability_when_publishing() {
		wp_set_current_user( $this->create_venue_editor( [ get_post_type_object( Tribe__Events__Main::POSTTYPE )->cap->publish_posts ] ) );

		$venue = $this->create_venue_with_status( 'publish' );

		$this->assertEquals( 'pending', get_post_status( $venue ) );
	}

	/**
	 * @test
	 */
	public function it_should_keep_the_publish_status_for_a_venue_publisher() {
		wp_set_current_user( $this->create_venue_editor( [ $this->venue_cap( 'publish_posts' ) ] ) );

		$venue = $this->create_venue_with_status( 'publish' );

		$this->assertEquals( 'publish', get_post_status( $venue ) );
	}

	/**
	 * @test
	 */
	public function it_should_scale_back_the_private_status_without_the_publish_capability() {
		wp_set_current_user( $this->create_venue_editor() );

		$venue = $this->create_venue_with_status( 'private' );

		$this->assertEquals( 'pending', get_post_status( $venue ) );
	}

	/**
	 * @test
	 */
	public function it_should_keep_the_current_status_when_trashing_without_the_delete_capability() {
		$user_id = $this->create_venue_editor();
		/* Pending, not draft, so the kept status is distinguishable from the create fallback. */
		$venue = $this->factory()->venue->create( [ 'post_author' => $user_id, 'post_status' => 'pending' ] );
		wp_set_current_user( $user_id );

		$this->update_venue( $venue, [ 'status' => 'trash' ] );

		$this->assertEquals( 'pending', get_post_status( $venue ) );
	}

	/**
	 * @test
	 */
	public function it_should_trash_on_update_with_the_delete_capability() {
		$user_id = $this->create_venue_editor( [ $this->venue_cap( 'delete_posts' ) ] );
		$venue   = $this->factory()->venue->create( [ 'post_author' => $user_id, 'post_status' => 'pending' ] );
		wp_set_current_user( $user_id );

		$this->update_venue( $venue, [ 'status' => 'trash' ] );

		$this->assertEquals( 'trash', get_post_status( $venue ) );
	}

	/**
	 * @test
	 */
	public function it_should_not_take_the_status_of_an_id_sent_on_create() {
		$published = $this->factory()->venue->create( [ 'post_status' => 'publish' ] );
		wp_set_current_user( $this->create_venue_editor() );

		$request = new WP_REST_Request( 'POST', '/tribe/events/v1/venues' );
		$request->set_param( 'venue', 'A venue' );
		$request->set_param( 'status', 'trash' );
		$request->set_param( 'id', $published );
		$venue = $this->make_instance()->create( $request, true );

		$this->assertTrue( tribe_is_venue( $venue ) );
		$this->assertNotEquals( $published, $venue );
		$this->assertEquals( 'draft', get_post_status( $venue ) );
	}

	/**
	 * @test
	 */
	public function it_should_keep_the_current_status_when_an_update_omits_it() {
		$user_id = $this->create_venue_editor( [ $this->venue_cap( 'publish_posts' ) ] );
		$venue   = $this->factory()->venue->create( [ 'post_author' => $user_id, 'post_status' => 'draft' ] );
		wp_set_current_user( $user_id );

		$this->update_venue( $venue, [ 'venue' => 'A renamed venue' ] );

		$this->assertEquals( 'A renamed venue', get_post_field( 'post_title', $venue ) );
		$this->assertEquals( 'draft', get_post_status( $venue ) );
	}

	/**
	 * @test
	 */
	public function it_should_not_write_back_the_status_read_before_the_update() {
		$user_id = $this->create_venue_editor( [ $this->venue_cap( 'publish_posts' ) ] );
		$venue = $this->factory()->venue->create( [ 'post_author' => $user_id, 'post_status' => 'draft' ] );
		wp_set_current_user( $user_id );
		/* A status change saved by another request after the status check, before the write. */
		add_filter(
			'tribe_events_rest_venue_prepare_postarr',
			static function ( $postarr ) use ( $venue ) {
				wp_update_post( [ 'ID' => $venue, 'post_status' => 'private' ] );

				return $postarr;
			}
		);

		$this->update_venue( $venue, [ 'venue' => 'A renamed venue' ] );

		$this->assertEquals( 'A renamed venue', get_post_field( 'post_title', $venue ) );
		$this->assertEquals( 'private', get_post_status( $venue ) );
	}

	/**
	 * Creates a user who can edit venues and holds only the extra capabilities given.
	 *
	 * @param string[] $extra_caps The capabilities to grant on top of the venue `edit_posts` one.
	 *
	 * @return int The user ID.
	 */
	private function create_venue_editor( array $extra_caps = [] ): int {
		$user_id = $this->factory()->user->create( [ 'role' => 'subscriber' ] );
		$user    = get_user_by( 'id', $user_id );

		foreach ( array_merge( [ $this->venue_cap( 'edit_posts' ) ], $extra_caps ) as $cap ) {
			$user->add_cap( $cap );
		}

		return $user_id;
	}

	/**
	 * Returns the venue post type's mapping of a primitive capability.
	 *
	 * @param string $cap The generic capability name, e.g. `publish_posts`.
	 *
	 * @return string The venue capability name.
	 */
	private function venue_cap( string $cap ): string {
		return get_post_type_object( Tribe__Events__Main::VENUE_POST_TYPE )->cap->{$cap};
	}

	/**
	 * Creates a venue through the endpoint as the current user.
	 *
	 * @param string $status The requested status.
	 *
	 * @return int The created venue ID.
	 */
	private function create_venue_with_status( string $status ): int {
		$request = new WP_REST_Request( 'POST', '/tribe/events/v1/venues' );
		$request->set_param( 'venue', 'A venue' );
		$request->set_param( 'status', $status );

		$venue = $this->make_instance()->create( $request, true );

		$this->assertTrue( tribe_is_venue( $venue ) );

		return $venue;
	}

	/**
	 * Updates a venue through the endpoint as the current user, with the ID in the route as the API sets it.
	 *
	 * @param int                   $venue  The venue ID.
	 * @param array<string, string> $params The body parameters to send.
	 *
	 * @return void
	 */
	private function update_venue( int $venue, array $params ): void {
		$request = new WP_REST_Request( 'POST', '/tribe/events/v1/venues/' . $venue );
		$request->set_url_params( [ 'id' => $venue ] );

		foreach ( $params as $key => $value ) {
			$request->set_param( $key, $value );
		}

		$this->assertInstanceOf( WP_REST_Response::class, $this->make_instance()->update( $request ) );
	}
}
