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

		$response = $this->assert_endpoint( $this->endpoint->get_base_path() );

		// Count how many published organizers we have.
		$expected_count = 0;
		foreach ( $organizers as $organizer_id ) {
			if ( 'publish' === get_post_status( $organizer_id ) ) {
				$expected_count++;
			}
		}

		// Ensure we have all of the published organizers in the response.
		$this->assertEquals( $expected_count, count( $response ) );

		// Some organizers aren't published, so also assert that we have less that the total in the response.
		$this->assertLessThan( count( $organizers ), count( $response ), 'There should be fewer organizers in the response.' );

		// Snapshot the response.
		$json = wp_json_encode( $response, JSON_SNAPSHOT_OPTIONS );
		$json = str_replace( $venues, '{VENUE_ID}', $json );
		$json = str_replace( $organizers, '{ORGANIZER_ID}', $json );

		$this->assertMatchesJsonSnapshot( $json );
	}
}
