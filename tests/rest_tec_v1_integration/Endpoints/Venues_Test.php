<?php

namespace TEC\Events\Tests\REST\TEC\V1\Endpoints;

use TEC\Events\REST\TEC\V1\Endpoints\Venues;
use Closure;

class Venues_Test extends Venue_Test {
	protected $endpoint_class = Venues::class;

	public function test_get_formatted_entity() {
		[ $organizers, $venues ] = $this->create_test_data();

		$data = [];
		foreach ( $venues as $venue ) {
			$data[] = $this->endpoint->get_formatted_entity( $this->endpoint->get_orm()->by_args( [ 'id' => $venue, 'status' => 'any' ] )->first() );
		}

		$json = wp_json_encode( $data, JSON_SNAPSHOT_OPTIONS );

		$json = str_replace( $organizers, '{ORGANIZER_ID}', $json );
		$json = str_replace( $venues, '{VENUE_ID}', $json );

		$this->assertMatchesJsonSnapshot( $json );
	}

	/**
	 * @dataProvider different_user_roles_provider
	 */
	public function test_read_responses( Closure $fixture ) {
		[ $organizers, $venues ] = $this->create_test_data();
		$fixture();

		$responses = [];
		foreach ( $venues as $venue_id ) {
			if ( 'publish' === get_post_status( $venue_id ) ) {
				$responses[] = $this->assert_endpoint( '/venues/' . $venue_id );
			} else {
				$should_pass = is_user_logged_in() && current_user_can( 'read_post', $venue_id );
				$response = $this->assert_endpoint( '/venues/' . $venue_id, 'GET', $should_pass ? 200 : ( is_user_logged_in() ? 403 : 401 ) );
				if ( $should_pass ) {
					$responses[] = $response;
				}
			}
		}

		$json = wp_json_encode( $responses, JSON_SNAPSHOT_OPTIONS );

		$json = str_replace( $organizers, '{ORGANIZER_ID}', $json );
		$json = str_replace( $venues, '{VENUE_ID}', $json );

		$this->assertMatchesJsonSnapshot( $json );
	}

	/**
	 * @dataProvider different_user_roles_provider
	 */
	public function test_read_responses_with_password( Closure $fixture ) {
		[ $organizers, $venues ] = $this->create_test_data();
		$fixture();

		$responses = [];
		foreach ( $venues as $venue_id ) {
			if ( 'publish' === get_post_status( $venue_id ) ) {
				$responses[] = $this->assert_endpoint( '/venues/' . $venue_id, 'GET', 200, [ 'password' => 'password123' ] );
			} else {
				$should_pass = is_user_logged_in() && current_user_can( 'read_post', $venue_id );
				$response = $this->assert_endpoint( '/venues/' . $venue_id, 'GET', $should_pass ? 200 : ( is_user_logged_in() ? 403 : 401 ), [ 'password' => 'password123' ] );
				if ( $should_pass ) {
					$responses[] = $response;
				}
			}
		}

		$json = wp_json_encode( $responses, JSON_SNAPSHOT_OPTIONS );

		$json = str_replace( $organizers, '{ORGANIZER_ID}', $json );
		$json = str_replace( $venues, '{VENUE_ID}', $json );

		$this->assertMatchesJsonSnapshot( $json );
	}
}