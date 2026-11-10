<?php

namespace Tribe\Events\Linked_Posts;

use Codeception\TestCase\WPTestCase;
use Generator;
use Tribe\Events\Views\V2\iCalendar\Links\Google_Calendar;
use Tribe__Events__Main as TEC;
use Tribe__Events__Venue as Venue;
use WP_Post;

/**
 * Linked Venue and Organizer details reach the iCal export and the add-to-calendar links only when
 * the current user may see the linked post.
 */
class Export_Visibility_Test extends WPTestCase {
	/**
	 * @before
	 */
	public function log_out(): void {
		wp_set_current_user( 0 );
	}

	public function hidden_linked_post_provider(): Generator {
		yield 'password protected' => [ [ 'post_password' => 'secret' ] ];
		yield 'draft' => [ [ 'post_status' => 'draft' ] ];
		yield 'pending' => [ [ 'post_status' => 'pending' ] ];
		yield 'private' => [ [ 'post_status' => 'private' ] ];
	}

	/**
	 * @test
	 * @dataProvider hidden_linked_post_provider
	 */
	public function it_should_not_export_a_hidden_venue_address( array $venue_overrides ): void {
		$venue = $this->create_venue();
		$event = $this->create_event( [ 'venue' => $venue['id'] ] );
		wp_update_post( array_merge( [ 'ID' => $venue['id'] ], $venue_overrides ) );

		$item = tribe( 'tec.iCal' )->get_ical_output_for_an_event( $event, TEC::instance() );

		$this->assertArrayNotHasKey( 'LOCATION', $item );
		$this->assertStringNotContainsString( $venue['address'], implode( "\n", $item ) );
		$this->assertSame( '', Google_Calendar::generate_string_address( $event ) );
		$this->assertSame( '', Venue::generate_string_address( $event->ID ) );
	}

	/**
	 * @test
	 * @dataProvider hidden_linked_post_provider
	 */
	public function it_should_not_export_a_hidden_organizer_email( array $organizer_overrides ): void {
		$organizer = $this->create_organizer();
		$event     = $this->create_event( [ 'organizer' => $organizer['id'] ] );
		wp_update_post( array_merge( [ 'ID' => $organizer['id'] ], $organizer_overrides ) );

		$item = tribe( 'tec.iCal' )->get_ical_output_for_an_event( $event, TEC::instance() );

		$this->assertArrayNotHasKey( 'ORGANIZER', $item );
		$this->assertStringNotContainsString( $organizer['email'], implode( "\n", $item ) );
	}

	/**
	 * @test
	 */
	public function it_should_export_a_published_venue_and_organizer(): void {
		$venue     = $this->create_venue();
		$organizer = $this->create_organizer();
		$event     = $this->create_event( [ 'venue' => $venue['id'], 'organizer' => $organizer['id'] ] );

		$item = tribe( 'tec.iCal' )->get_ical_output_for_an_event( $event, TEC::instance() );

		$this->assertStringContainsString( $venue['address'], $item['LOCATION'] );
		$this->assertStringContainsString( $venue['city'], $item['LOCATION'] );
		$this->assertStringEndsWith( 'MAILTO:' . $organizer['email'], $item['ORGANIZER'] );
		$this->assertStringContainsString( $venue['address'], Google_Calendar::generate_string_address( $event ) );
		$this->assertStringContainsString( $venue['address'], Venue::generate_string_address( $event->ID ) );
	}

	/**
	 * @test
	 */
	public function it_should_export_an_unpublished_venue_and_organizer_to_a_user_who_can_read_them(): void {
		$venue     = $this->create_venue();
		$organizer = $this->create_organizer();
		$event     = $this->create_event( [ 'venue' => $venue['id'], 'organizer' => $organizer['id'] ] );
		wp_update_post( [ 'ID' => $venue['id'], 'post_status' => 'draft' ] );
		wp_update_post( [ 'ID' => $organizer['id'], 'post_status' => 'private' ] );
		wp_set_current_user( static::factory()->user->create( [ 'role' => 'administrator' ] ) );

		$item = tribe( 'tec.iCal' )->get_ical_output_for_an_event( $event, TEC::instance() );

		$this->assertStringContainsString( $venue['address'], $item['LOCATION'] );
		$this->assertStringEndsWith( 'MAILTO:' . $organizer['email'], $item['ORGANIZER'] );
	}

	private function create_venue(): array {
		$venue = [
			'address' => '742 Evergreen Terrace',
			'city'    => 'Springfield',
		];

		$venue['id'] = tribe_venues()->set_args(
			[
				'title'   => 'Venue',
				'address' => $venue['address'],
				'city'    => $venue['city'],
				'status'  => 'publish',
			]
		)->create()->ID;

		return $venue;
	}

	private function create_organizer(): array {
		$organizer = [ 'email' => 'organizer@example.com' ];

		$organizer['id'] = tribe_organizers()->set_args(
			[
				'title'  => 'Organizer',
				'email'  => $organizer['email'],
				'status' => 'publish',
			]
		)->create()->ID;

		return $organizer;
	}

	private function create_event( array $linked ): WP_Post {
		return tribe_events()->set_args(
			array_merge(
				[
					'title'      => 'Event',
					'start_date' => '+1 week 10:00',
					'duration'   => HOUR_IN_SECONDS,
					'status'     => 'publish',
				],
				$linked
			)
		)->create();
	}
}
