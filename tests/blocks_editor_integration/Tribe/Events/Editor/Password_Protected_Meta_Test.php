<?php

namespace Tribe\Events\Editor;

use Codeception\TestCase\WPTestCase;
use Generator;
use Tribe__Events__Main as TEC;
use WP_REST_Request;

class Password_Protected_Meta_Test extends WPTestCase {
	public function setUp() {
		parent::setUp();

		// The suite bootstrap registers the editor provider after `init`, so the meta registration has not run.
		$meta = tribe( 'events.editor.meta' );
		if ( ! has_filter( 'rest_prepare_' . TEC::POSTTYPE, [ $meta, 'meta_backwards_compatibility' ] ) ) {
			$meta->register();
		}
	}

	public function tearDown() {
		wp_set_current_user( 0 );

		parent::tearDown();
	}

	/**
	 * @return Generator<string,array{0:string,1:array<string,string>}>
	 */
	public function linked_post_type_provider(): Generator {
		yield 'venue' => [
			TEC::VENUE_POST_TYPE,
			[
				'_VenueAddress' => '88 Example Street',
				'_VenueCity'    => 'Example City',
				'_VenueZip'     => '12345',
				'_VenuePhone'   => '555-0100',
				'_VenueURL'     => 'https://venue.example.invalid',
				'_VenueLat'     => '41.09',
				'_VenueLng'     => '-8.61',
			],
		];

		yield 'organizer' => [
			TEC::ORGANIZER_POST_TYPE,
			[
				'_OrganizerEmail'   => 'organizer@example.invalid',
				'_OrganizerPhone'   => '555-0101',
				'_OrganizerWebsite' => 'https://organizer.example.invalid',
			],
		];
	}

	/**
	 * @test
	 * @dataProvider linked_post_type_provider
	 */
	public function it_should_blank_the_meta_of_a_password_protected_linked_post_in_a_single_response( string $post_type, array $meta ) {
		$post_id = $this->create_post( $post_type, $meta, 'secret-password' );

		$response_meta = $this->get_single_meta( $post_type, $post_id );

		foreach ( $meta as $key => $value ) {
			$this->assertSame( '', $response_meta[ $key ], $key );
		}
	}

	/**
	 * @test
	 * @dataProvider linked_post_type_provider
	 */
	public function it_should_blank_the_meta_of_a_password_protected_linked_post_in_a_collection_response( string $post_type, array $meta ) {
		$protected_id = $this->create_post( $post_type, $meta, 'secret-password' );
		$public_id    = $this->create_post( $post_type, $meta );

		$collection_meta = $this->get_collection_meta( $post_type );

		foreach ( $meta as $key => $value ) {
			$this->assertSame( '', $collection_meta[ $protected_id ][ $key ], $key );
			$this->assertSame( $value, $collection_meta[ $public_id ][ $key ], $key );
		}
	}

	/**
	 * @test
	 * @dataProvider linked_post_type_provider
	 */
	public function it_should_return_the_meta_of_a_linked_post_without_a_password( string $post_type, array $meta ) {
		$post_id = $this->create_post( $post_type, $meta );

		$response_meta = $this->get_single_meta( $post_type, $post_id );

		foreach ( $meta as $key => $value ) {
			$this->assertSame( $value, $response_meta[ $key ], $key );
		}
	}

	/**
	 * @test
	 * @dataProvider linked_post_type_provider
	 */
	public function it_should_return_the_meta_of_a_password_protected_linked_post_when_the_request_has_its_password( string $post_type, array $meta ) {
		$password = 'secret-password';
		$post_id  = $this->create_post( $post_type, $meta, $password );

		$response_meta = $this->get_single_meta( $post_type, $post_id, [ 'password' => $password ] );

		foreach ( $meta as $key => $value ) {
			$this->assertSame( $value, $response_meta[ $key ], $key );
		}
	}

	/**
	 * @test
	 * @dataProvider linked_post_type_provider
	 */
	public function it_should_return_the_meta_of_a_password_protected_linked_post_to_an_editor_in_edit_context( string $post_type, array $meta ) {
		$post_id = $this->create_post( $post_type, $meta, 'secret-password' );
		wp_set_current_user( $this->factory()->user->create( [ 'role' => 'editor' ] ) );

		$response_meta = $this->get_single_meta( $post_type, $post_id, [ 'context' => 'edit' ] );

		foreach ( $meta as $key => $value ) {
			$this->assertSame( $value, $response_meta[ $key ], $key );
		}
	}

	/**
	 * @test
	 */
	public function it_should_blank_the_protected_meta_of_a_password_protected_event() {
		$meta     = [
			'_EventCost' => '42',
			'_EventURL'  => 'https://event.example.invalid',
		];
		$venue_id = $this->create_post( TEC::VENUE_POST_TYPE, [] );
		$event_id = $this->create_event( $meta, $venue_id, 'secret-password' );

		$single_meta     = $this->get_single_meta( TEC::POSTTYPE, $event_id );
		$collection_meta = $this->get_collection_meta( TEC::POSTTYPE );

		foreach ( array_keys( $meta ) as $key ) {
			$this->assertSame( '', $single_meta[ $key ], $key );
			$this->assertSame( '', $collection_meta[ $event_id ][ $key ], $key );
		}
		$this->assertSame( [], $single_meta['_EventVenueID'] );
		$this->assertSame( [], $collection_meta[ $event_id ]['_EventVenueID'] );
	}

	/**
	 * @test
	 */
	public function it_should_return_the_meta_of_an_event_without_a_password() {
		$meta     = [
			'_EventCost' => '42',
			'_EventURL'  => 'https://event.example.invalid',
		];
		$venue_id = $this->create_post( TEC::VENUE_POST_TYPE, [] );
		$event_id = $this->create_event( $meta, $venue_id );

		$response_meta = $this->get_single_meta( TEC::POSTTYPE, $event_id );

		foreach ( $meta as $key => $value ) {
			$this->assertSame( $value, $response_meta[ $key ], $key );
		}
		// The meta is registered as `number`, so core returns the ID as a float.
		$this->assertEquals( [ $venue_id ], $response_meta['_EventVenueID'] );
	}

	/**
	 * @param string                $post_type The post type to create.
	 * @param array<string,string>  $meta      The meta to store on the post.
	 * @param string                $password  The post password, empty for none.
	 *
	 * @return int The created post ID.
	 */
	private function create_post( string $post_type, array $meta, string $password = '' ): int {
		$post_id = $this->factory()->post->create(
			[
				'post_type'     => $post_type,
				'post_status'   => 'publish',
				'post_password' => $password,
			]
		);

		foreach ( $meta as $key => $value ) {
			update_post_meta( $post_id, $key, $value );
		}

		return $post_id;
	}

	/**
	 * Creates an upcoming Event, which the Events collection lists.
	 *
	 * @param array<string,string> $meta     The meta to store on the Event.
	 * @param int                  $venue_id The Venue to link.
	 * @param string               $password The Event password, empty for none.
	 *
	 * @return int The created Event ID.
	 */
	private function create_event( array $meta, int $venue_id, string $password = '' ): int {
		$event_id = tribe_events()->set_args(
			[
				'title'         => 'Event',
				'status'        => 'publish',
				'start_date'    => '+1 week',
				'duration'      => HOUR_IN_SECONDS,
				'venue'         => $venue_id,
				'post_password' => $password,
			]
		)->create()->ID;

		foreach ( $meta as $key => $value ) {
			update_post_meta( $event_id, $key, $value );
		}

		return $event_id;
	}

	/**
	 * @param string               $post_type The post type the request reads.
	 * @param int                  $post_id   The post the request reads.
	 * @param array<string,string> $params    Extra query parameters.
	 *
	 * @return array<string,string|bool|list<float>> The `meta` of the response.
	 */
	private function get_single_meta( string $post_type, int $post_id, array $params = [] ): array {
		$request = new WP_REST_Request( 'GET', '/wp/v2/' . $post_type . '/' . $post_id );
		$request->set_query_params( $params );
		$response = rest_do_request( $request );

		$this->assertSame( 200, $response->get_status(), wp_json_encode( $response->get_data() ) );

		return $response->get_data()['meta'];
	}

	/**
	 * @param string $post_type The post type the request lists.
	 *
	 * @return array<int,array<string,string|bool|list<float>>> The `meta` of each listed post, by post ID.
	 */
	private function get_collection_meta( string $post_type ): array {
		$response = rest_do_request( new WP_REST_Request( 'GET', '/wp/v2/' . $post_type ) );

		$this->assertSame( 200, $response->get_status(), wp_json_encode( $response->get_data() ) );

		return array_column( $response->get_data(), 'meta', 'id' );
	}
}
