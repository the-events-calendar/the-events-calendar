<?php

namespace TEC\Events\Tests\REST\TEC\V1\Endpoints;

use TEC\Events\REST\TEC\V1\Endpoints\Events;
use Closure;

class Events_Test extends Event_Test {
	protected $endpoint_class = Events::class;

	public function test_get_formatted_entity() {
		[ $venues, $organizers, $events ] = $this->create_test_data();

		$data = [];
		foreach ( $events as $event ) {
			$data[] = $this->endpoint->get_formatted_entity( $this->endpoint->get_orm()->by_args( [ 'id' => $event, 'status' => 'any' ] )->first() );
		}

		$json = wp_json_encode( $data, JSON_SNAPSHOT_OPTIONS );

		$json = str_replace( $venues, '{VENUE_ID}', $json );
		$json = str_replace( $organizers, '{ORGANIZER_ID}', $json );
		$json = str_replace( $events, '{EVENT_ID}', $json );

		$this->assertMatchesJsonSnapshot( $json );
	}

	/**
	 * @dataProvider different_user_roles_provider
	 */
	public function test_read_responses( Closure $fixture ) {
		[ $venues, $organizers, $events ] = $this->create_test_data();
		$fixture();

		$responses = [];
		foreach ( $events as $event_id ) {
			if ( 'publish' === get_post_status( $event_id ) ) {
				$responses[] = $this->assert_endpoint( '/events/' . $event_id );
			} else {
				$should_pass = is_user_logged_in() && current_user_can( 'read_post', $event_id );
				$response = $this->assert_endpoint( '/events/' . $event_id, 'GET', $should_pass ? 200 : ( is_user_logged_in() ? 403 : 401 ) );
				if ( $should_pass ) {
					$responses[] = $response;
				}
			}
		}

		$json = wp_json_encode( $responses, JSON_SNAPSHOT_OPTIONS );

		$json = str_replace( $venues, '{VENUE_ID}', $json );
		$json = str_replace( $organizers, '{ORGANIZER_ID}', $json );
		$json = str_replace( $events, '{EVENT_ID}', $json );

		$this->assertMatchesJsonSnapshot( $json );
	}

	/**
	 * @dataProvider different_user_roles_provider
	 */
	public function test_read_responses_with_password( Closure $fixture ) {
		[ $venues, $organizers, $events ] = $this->create_test_data();
		$fixture();

		$responses = [];
		foreach ( $events as $event_id ) {
			if ( 'publish' === get_post_status( $event_id ) ) {
				$responses[] = $this->assert_endpoint( '/events/' . $event_id, 'GET', 200, [ 'password' => 'password123' ] );
			} else {
				$should_pass = is_user_logged_in() && current_user_can( 'read_post', $event_id );
				$response = $this->assert_endpoint( '/events/' . $event_id, 'GET', $should_pass ? 200 : ( is_user_logged_in() ? 403 : 401 ), [ 'password' => 'password123' ] );
				if ( $should_pass ) {
					$responses[] = $response;
				}
			}
		}

		$json = wp_json_encode( $responses, JSON_SNAPSHOT_OPTIONS );

		$json = str_replace( $venues, '{VENUE_ID}', $json );
		$json = str_replace( $organizers, '{ORGANIZER_ID}', $json );
		$json = str_replace( $events, '{EVENT_ID}', $json );

		$this->assertMatchesJsonSnapshot( $json );
	}

	public function test_visitor_does_not_receive_unpublished_venues_and_organizers() {
		[ , , $event_id ] = $this->create_event_with_unpublished_linked_posts();

		$response = $this->assert_endpoint( '/events' );
		$events   = array_values( array_filter( $response, fn( $event ) => $event['id'] === $event_id ) );

		$this->assertCount( 1, $events );
		$this->assertSame( [], $events[0]['venues'] );
		$this->assertSame( [], $events[0]['organizers'] );
	}

	public function test_editor_receives_unpublished_venues_and_organizers() {
		[ $venue_id, $organizer_id, $event_id ] = $this->create_event_with_unpublished_linked_posts();
		wp_set_current_user( self::factory()->user->create( [ 'role' => 'editor' ] ) );

		$response = $this->assert_endpoint( '/events' );
		$events   = array_values( array_filter( $response, fn( $event ) => $event['id'] === $event_id ) );

		$this->assertCount( 1, $events );
		$this->assertSame( [ $venue_id ], array_column( $events[0]['venues'], 'id' ) );
		$this->assertSame( [ $organizer_id ], array_column( $events[0]['organizers'], 'id' ) );
	}
}
