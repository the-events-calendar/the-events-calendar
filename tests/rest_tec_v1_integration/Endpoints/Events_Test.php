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
		return;
		[ $venues, $organizers, $events ] = $this->create_test_data();
		$fixture();

		$responses = [];
		foreach ( $events as $event_id ) {
			$responses[] = $this->assert_endpoint( '/events/' . $event_id, 'GET' );
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
		return;
		[ $venues, $organizers, $events ] = $this->create_test_data();
		$fixture();

		$responses = [];
		foreach ( $events as $event_id ) {
			$responses[] = $this->assert_endpoint( '/events/' . $event_id, 'GET', 200, [ 'post_password' => 'password123' ] );
		}

		$json = wp_json_encode( $responses, JSON_SNAPSHOT_OPTIONS );

		$json = str_replace( $venues, '{VENUE_ID}', $json );
		$json = str_replace( $organizers, '{ORGANIZER_ID}', $json );
		$json = str_replace( $events, '{EVENT_ID}', $json );

		$this->assertMatchesJsonSnapshot( $json );
	}
}
