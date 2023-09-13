<?php

namespace TEC\Events\Integrations\Plugins\Event_Tickets\Emails;

use Tribe\Events\Test\Factories\Venue;
use Tribe\Events\Test\Factories\Organizer;

class EmailTest extends \Codeception\TestCase\WPTestCase {

	public $event_ids         = [];
	public $test_venue_id     = null;
	public $test_organizer_id = null;

	public function setUp() {
		parent::setUp();

		$this->factory()->venue     = new Venue();
		$this->factory()->organizer = new Organizer();
		$this->create_organizers();
		$this->create_venues();
		$this->create_test_event();

	}

	public function create_test_event(): void {
		$created = tribe_events()->set_args(
			[
				'title'      => "Test Event",
				'status'     => 'publish',
				'start_date' => "+1 weeks 10am",
				'duration'   => 2 * HOUR_IN_SECONDS,
				'venue'      => $this->test_venue_id,
				'organizer'  => $this->test_organizer_id,
			]
		)->create();

		$this->assertInstanceOf( \WP_Post::class, $created );
		$this->event_ids[] = $created->ID;
	}

	/**
	 * Create our organizer so can test editing it.
	 *
	 * @return void
	 */
	public function create_organizers(): void {

		$this->test_organizer_id = $this->factory()->organizer->create( [
				'post_title'   => 'Test Organizer',
				'post_content' => 'Organizer Description',
				'meta_input'   => [
					'_OrganizerPhone'   => '123-555-9999',
					'_OrganizerWebsite' => 'http://example.com',
					'_OrgsanizerEmail'  => 'test@example.com'
				],
			]
		);

	}

	public function create_venues(): void {

		$this->test_venue_id = $this->factory()->venue->create( [
				'post_title'   => 'Test Venue',
				'post_content' => 'Venue Description',
				'meta_input'   => [
					'_VenuePhone'    => '123-555-9999',
					'_VenueURL'      => 'http://example.com',
					'_VenueEmail'    => 'test@example.com',
					'_VenueZIP'      => '90210',
					'_VenueProvince' => 'Area 1',
					'_VenueCity'     => 'Area 2',
					'_VenueAddress'  => '123 Street',
				],
			]
		);

	}

	public function test_get_event_placeholders() {
		$event = tribe_get_event( $this->event_ids[0] );

		$emails = new Emails();

		$placeholders = $emails->get_event_placeholders( $event );

		// Assert the placeholders are as expected
		$this->assertEquals( $this->event_ids[0], $placeholders['{event_id}'] );
		$this->assertEquals( wp_kses( $event->schedule_details->value(), [] ), $placeholders['{event_date}'] );
		$this->assertEquals( wp_kses( $event->dates->start->format( tribe_get_datetime_format( true ) ), [] ), $placeholders['{event_start_date}'] );
		$this->assertEquals( wp_kses( $event->dates->end->format( tribe_get_datetime_format( true ) ), [] ), $placeholders['{event_end_date}'] );
		$this->assertEquals( wp_kses( $event->post_title, [] ), $placeholders['{event_name}'] );
		$this->assertEquals( $event->timezone, $placeholders['{event_timezone}'] );
		$this->assertEquals( $event->permalink, $placeholders['{event_url}'] );
		$this->assertEquals( ! empty( $event->thumbnail->exists ) ? $event->thumbnail->full->url : '', $placeholders['{event_image_url}'] );

	}

	public function test_get_venue_placeholders() {
		$event = tribe_get_event( $this->event_ids[0] );

		$emails = new Emails();


		$placeholders = $emails->get_venue_placeholders( $event );
		// If the event has a venue, add the venue placeholders.
		if ( ! empty( $event->venues->count() ) ) {
			$venue = $event->venues[0];

			$state_or_province = $venue->state;
			if ( $venue->country !== 'US' ) {
				$state_or_province = $venue->province;
			}
			if ( empty( $state_or_province ) ) {
				$state_or_province = $venue->state_province;
			}

			// Assert the placeholders are as expected
			$this->assertEquals( $venue->ID, $placeholders['{event_venue_id}'] );
			$this->assertEquals( wp_kses( $venue->post_title, [] ), $placeholders['{event_venue_name}'] );
			$this->assertEquals( $venue->address, $placeholders['{event_venue_street_address}'] );
			$this->assertEquals( $venue->city, $placeholders['{event_venue_city}'] );
			$this->assertEquals( $state_or_province, $placeholders['{event_venue_state_or_province}'] );
			$this->assertEquals( $venue->province, $placeholders['{event_venue_province}'] );
			$this->assertEquals( $venue->state, $placeholders['{event_venue_state}'] );
			$this->assertEquals( $venue->zip, $placeholders['{event_venue_zip}'] );
			$this->assertEquals( $venue->permalink, $placeholders['{event_venue_url}'] );
		}
	}

	public function test_get_organizer_placeholders() {
		$event = tribe_get_event( $this->event_ids[0] );

		$emails = new Emails();

		$placeholders = $emails->get_organizer_placeholders( $event );

		// Assuming there's only one organizer
		$organizer            = $event->organizers[0];
		$organizer_id         = $organizer->ID;
		$organizer_post_title = wp_kses( $organizer->post_title, [] );
		$organizer_permalink  = $organizer->permalink;
		$organizer_url        = tribe_get_organizer_website_url( $organizer->ID );
		$organizer_email      = tribe_get_organizer_email( $organizer->ID );
		$organizer_phone      = $organizer->phone;

		$this->assertEquals( $organizer_id, $placeholders['{event_organizer_id}'], 'Event Organizer ID does not match.' );
		$this->assertEquals( $organizer_post_title, $placeholders['{event_organizer_name}'], 'Event Organizer Name does not match.' );
		$this->assertEquals( $organizer_permalink, $placeholders['{event_organizer_url}'], 'Event Organizer URL does not match.' );
		$this->assertEquals( html_entity_decode( $organizer_email ), html_entity_decode( $placeholders['{event_organizer_email}'] ), 'Event Organizer Email does not match.' );
		$this->assertEquals( $organizer_url, $placeholders['{event_organizer_website}'], 'Event Organizer Website does not match.' );
		$this->assertEquals( $organizer_phone, $placeholders['{event_organizer_phone}'], 'Event Organizer Phone does not match.' );
		$this->assertEquals( 1, $placeholders['{event_organizers_count}'], 'Event Organizers Count does not match.' );
		$this->assertEquals( $organizer_post_title, $placeholders['{event_organizers_names}'], 'Event Organizers Names do not match.' );
	}

}