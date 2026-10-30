<?php

namespace TEC\Events\Tests\REST\TEC\V1\Endpoints;

use TEC\Common\Tests\Testcases\REST\TEC\V1\REST_Test_Case;
use TEC\Events\REST\TEC\V1\Endpoints\Event;

class Event_Linked_Password_Test extends REST_Test_Case {
	protected $endpoint_class = Event::class;

	private function create_event_with_password( string $password ): int {
		return tribe_events()->set_args(
			[
				'title'         => 'Carrier',
				'status'        => 'publish',
				'start_date'    => '2030-01-01 10:00:00',
				'end_date'      => '2030-01-01 12:00:00',
				'post_password' => $password,
			]
		)->create()->ID;
	}

	/**
	 * @test
	 */
	public function it_should_not_expose_a_linked_page_protected_by_another_password() {
		$page_password = 'page-password';
		$page_secret   = 'page-secret-content';
		$page_id       = $this->factory()->post->create(
			[
				'post_type'     => 'page',
				'post_status'   => 'publish',
				'post_content'  => $page_secret,
				'post_password' => $page_password,
			]
		);
		$event_password = 'carrier-password';
		$event_id       = $this->create_event_with_password( $event_password );
		update_post_meta( $event_id, '_EventOrganizerID', $page_id );

		wp_set_current_user( 0 );

		$data = $this->assert_endpoint( '/events/' . $event_id, 'GET', 200, [ 'password' => $event_password ] );
		$json = wp_json_encode( $data );

		$this->assertStringNotContainsString( $page_secret, $json );
		$this->assertStringNotContainsString( $page_password, $json );
	}

	/**
	 * @test
	 */
	public function it_should_not_expose_a_linked_organizer_protected_by_another_password() {
		$organizer_password = 'organizer-password';
		$organizer_secret   = 'organizer-secret-content';
		$organizer_id       = tribe_organizers()->set_args(
			[
				'title'         => 'Protected Organizer',
				'status'        => 'publish',
				'content'       => $organizer_secret,
				'post_password' => $organizer_password,
			]
		)->create()->ID;
		$event_password     = 'carrier-password';
		$event_id           = $this->create_event_with_password( $event_password );
		update_post_meta( $event_id, '_EventOrganizerID', $organizer_id );

		wp_set_current_user( 0 );

		$data = $this->assert_endpoint( '/events/' . $event_id, 'GET', 200, [ 'password' => $event_password ] );
		$json = wp_json_encode( $data );

		$this->assertStringNotContainsString( $organizer_secret, $json );
		$this->assertStringNotContainsString( $organizer_password, $json );
	}
}
