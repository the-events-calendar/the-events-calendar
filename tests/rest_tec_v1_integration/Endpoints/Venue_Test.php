<?php

namespace TEC\Events\Tests\REST\TEC\V1\Endpoints;

use TEC\Common\Tests\Testcases\REST\TEC\V1\Post_Entity_REST_Test_Case;
use Tribe__Events__Repositories__Venue as Venue_Repository;
use Tribe\Events\Models\Post_Types\Venue as Venue_Model;
use TEC\Events\REST\TEC\V1\Endpoints\Venue;
use Closure;

class Venue_Test extends Post_Entity_REST_Test_Case {
	protected $endpoint_class = Venue::class;

	protected function create_test_data(): array {
		wp_set_current_user( 1 );

		// Create test organizers for relationships
		$organizer_1 = tribe_organizers()->set_args(
			[
				'title' => 'Test Organizer for Venues',
				'status' => 'publish',
				'email' => 'venue-test@example.com',
			]
		)->create();

		// Create standard venues
		$venue_1 = tribe_venues()->set_args(
			[
				'title' => 'Downtown Convention Center',
				'status' => 'publish',
				'address' => '123 Main St',
				'city' => 'New York',
				'state_province' => 'NY',
				'zip' => '10001',
				'country' => 'United States',
				'phone' => '+1-212-555-0123',
				'website' => 'https://downtowncc.example.com',
				'description' => 'A modern convention center in the heart of downtown.',
			]
		)->create();

		$venue_2 = tribe_venues()->set_args(
			[
				'title' => 'Beach Resort Amphitheater',
				'status' => 'publish',
				'address' => '456 Ocean Blvd',
				'city' => 'Miami',
				'state_province' => 'FL',
				'zip' => '33139',
				'country' => 'United States',
				'phone' => '+1-305-555-0456',
				'description' => 'Outdoor amphitheater with ocean views.',
			]
		)->create();

		// Create venue with minimal data
		$minimal_venue = tribe_venues()->set_args(
			[
				'title' => 'Simple Venue',
				'status' => 'publish',
			]
		)->create();

		// Create private venue
		$private_venue = tribe_venues()->set_args(
			[
				'title' => 'Private Golf Club',
				'status' => 'private',
				'address' => '789 Country Club Rd',
				'city' => 'Augusta',
				'state_province' => 'GA',
				'zip' => '30901',
				'country' => 'United States',
			]
		)->create();

		// Create draft venue
		$draft_venue = tribe_venues()->set_args(
			[
				'title' => 'Planned Concert Hall',
				'status' => 'draft',
				'address' => '321 Music Way',
				'city' => 'Nashville',
				'state_province' => 'TN',
				'zip' => '37201',
				'country' => 'United States',
			]
		)->create();

		// Create international venue
		$intl_venue = tribe_venues()->set_args(
			[
				'title' => 'London Exhibition Centre',
				'status' => 'publish',
				'address' => '10 Downing Street',
				'city' => 'London',
				'state_province' => 'England',
				'zip' => 'SW1A 2AA',
				'country' => 'United Kingdom',
				'phone' => '+44 20 7930 4433',
				'website' => 'https://londonexhibition.co.uk',
			]
		)->create();

		// Create venue with special characters
		$special_venue = tribe_venues()->set_args(
			[
				'title' => 'Café & Gallery "L\'Art Moderne"',
				'status' => 'publish',
				'address' => '15 Rue de la Paix',
				'city' => 'Paris',
				'state_province' => 'Île-de-France',
				'zip' => '75002',
				'country' => 'France',
				'description' => 'Un lieu unique pour l\'art et la culture.',
			]
		)->create();

		// Create venue with HTML in description
		$html_venue = tribe_venues()->set_args(
			[
				'title' => 'Tech Conference Center',
				'status' => 'publish',
				'address' => '999 Innovation Dr',
				'city' => 'San Jose',
				'state_province' => 'CA',
				'zip' => '95110',
				'country' => 'United States',
				'description' => '<h3>State-of-the-art Facilities</h3><p>Our venue features:</p><ul><li>High-speed WiFi</li><li>4K projectors</li><li>Live streaming capabilities</li></ul><p><strong>Perfect for tech events!</strong></p>',
			]
		)->create();

		// Create password-protected venue
		$password_venue = tribe_venues()->set_args(
			[
				'title' => 'Password Members Only Club',
				'status' => 'publish',
				'post_password' => 'password123',
				'address' => '555 Exclusive Ave',
				'city' => 'Beverly Hills',
				'state_province' => 'CA',
				'zip' => '90210',
				'country' => 'United States',
				'description' => 'An exclusive venue for private members.',
			]
		)->create();

		// Create venues with events
		$venue_with_events = tribe_venues()->set_args(
			[
				'title' => 'Popular Event Space',
				'status' => 'publish',
				'address' => '777 Event Plaza',
				'city' => 'Chicago',
				'state_province' => 'IL',
				'zip' => '60601',
				'country' => 'United States',
			]
		)->create();

		// Create some events at this venue
		tribe_events()->set_args( [
			'title'      => 'Test Event at Venue',
			'start_date' => date( 'Y-m-d H:i:s', strtotime( '+1 week' ) ),
			'end_date'   => date( 'Y-m-d H:i:s', strtotime( '+1 week +2 hours' ) ),
			'status'     => 'publish',
			'venue'      => $venue_with_events->ID,
			'organizer'  => [ $organizer_1->ID ],
		] )->create();

		tribe_events()->set_args( [
			'title'      => 'Another Event at Venue',
			'start_date' => date( 'Y-m-d H:i:s', strtotime( '+2 weeks' ) ),
			'end_date'   => date( 'Y-m-d H:i:s', strtotime( '+2 weeks +3 hours' ) ),
			'status'     => 'publish',
			'venue'      => $venue_with_events->ID,
		] )->create();

		wp_set_current_user( 0 );

		return [
			[ $organizer_1->ID ],
			[
				$venue_1->ID,
				$venue_2->ID,
				$minimal_venue->ID,
				$private_venue->ID,
				$draft_venue->ID,
				$intl_venue->ID,
				$special_venue->ID,
				$html_venue->ID,
				$password_venue->ID,
				$venue_with_events->ID,
			]
		];
	}

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

	public function test_instance_of_orm() {
		$this->assertInstanceOf( Venue_Repository::class, $this->endpoint->get_orm() );
	}

	public function test_get_model_class() {
		$this->assertSame( Venue_Model::class, $this->endpoint->get_model_class() );
	}

	/**
	 * @dataProvider different_user_roles_provider
	 */
	public function test_read_responses( Closure $fixture ) {
		if ( ! $this->is_readable() ) {
			return;
		}

		[ $organizers, $venues ] = $this->create_test_data();
		$fixture();

		$responses = [];
		foreach ( $venues as $venue_id ) {
			$responses[] = $this->assert_endpoint( '/venues/' . $venue_id );
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
		if ( ! $this->is_readable() ) {
			return;
		}

		[ $organizers, $venues ] = $this->create_test_data();
		$fixture();

		$responses = [];
		foreach ( $venues as $venue_id ) {
			$responses[] = $this->assert_endpoint( '/venues/' . $venue_id, 'GET', 200, [ 'password' => 'password123' ] );
		}

		$json = wp_json_encode( $responses, JSON_SNAPSHOT_OPTIONS );

		$json = str_replace( $organizers, '{ORGANIZER_ID}', $json );
		$json = str_replace( $venues, '{VENUE_ID}', $json );

		$this->assertMatchesJsonSnapshot( $json );
	}
}
