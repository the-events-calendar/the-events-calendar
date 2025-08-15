<?php

namespace TEC\Events\Tests\REST\TEC\V1\Endpoints;

use TEC\Events\REST\TEC\V1\Endpoints\Organizers;
use Closure;

class Organizers_Test extends Organizer_Test {
	protected $endpoint_class = Organizers::class;

	public function test_get_formatted_entity() {
		[ $venues, $organizers ] = $this->create_test_data();

		$data = [];
		foreach ( $organizers as $organizer ) {
			$data[] = $this->endpoint->get_formatted_entity( $this->endpoint->get_orm()->by_args( [ 'id' => $organizer, 'status' => 'any' ] )->first() );
		}

		$json = wp_json_encode( $data, JSON_SNAPSHOT_OPTIONS );

		$json = str_replace( $venues, '{VENUE_ID}', $json );
		$json = str_replace( $organizers, '{ORGANIZER_ID}', $json );

		$this->assertMatchesJsonSnapshot( $json );
	}

	/**
	 * @dataProvider different_user_roles_provider
	 */
	public function test_read_responses( Closure $fixture ) {
		[ $venues, $organizers ] = $this->create_test_data();
		$fixture();

		$responses = [];
		foreach ( $organizers as $organizer_id ) {
			if ( 'publish' === get_post_status( $organizer_id ) ) {
				$responses[] = $this->assert_endpoint( '/organizers/' . $organizer_id );
			} else {
				$should_pass = is_user_logged_in() && current_user_can( 'read_post', $organizer_id );
				$response = $this->assert_endpoint( '/organizers/' . $organizer_id, 'GET', $should_pass ? 200 : ( is_user_logged_in() ? 403 : 401 ) );
				if ( $should_pass ) {
					$responses[] = $response;
				}
			}
		}

		$json = wp_json_encode( $responses, JSON_SNAPSHOT_OPTIONS );

		$json = str_replace( $venues, '{VENUE_ID}', $json );
		$json = str_replace( $organizers, '{ORGANIZER_ID}', $json );

		$this->assertMatchesJsonSnapshot( $json );
	}

	/**
	 * @dataProvider different_user_roles_provider
	 */
	public function test_read_responses_with_password( Closure $fixture ) {
		[ $venues, $organizers ] = $this->create_test_data();
		$fixture();

		$responses = [];
		foreach ( $organizers as $organizer_id ) {
			if ( 'publish' === get_post_status( $organizer_id ) ) {
				$responses[] = $this->assert_endpoint( '/organizers/' . $organizer_id, 'GET', 200, [ 'password' => 'password123' ] );
			} else {
				$should_pass = is_user_logged_in() && current_user_can( 'read_post', $organizer_id );
				$response = $this->assert_endpoint( '/organizers/' . $organizer_id, 'GET', $should_pass ? 200 : ( is_user_logged_in() ? 403 : 401 ), [ 'password' => 'password123' ] );
				if ( $should_pass ) {
					$responses[] = $response;
				}
			}
		}

		$json = wp_json_encode( $responses, JSON_SNAPSHOT_OPTIONS );

		$json = str_replace( $venues, '{VENUE_ID}', $json );
		$json = str_replace( $organizers, '{ORGANIZER_ID}', $json );

		$this->assertMatchesJsonSnapshot( $json );
	}
}