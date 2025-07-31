<?php

namespace TEC\Events\Tests\REST\TEC\V1\Endpoints;

use TEC\Common\Tests\Testcases\REST\TEC\V1\Post_Entity_REST_Test_Case;
use Tribe__Events__Repositories__Organizer as Organizer_Repository;
use Tribe\Events\Models\Post_Types\Organizer as Organizer_Model;
use TEC\Events\REST\TEC\V1\Endpoints\Organizer;
use Closure;

class Organizer_Test extends Post_Entity_REST_Test_Case {
	protected $endpoint_class = Organizer::class;

	protected function create_test_data(): array {
		wp_set_current_user( 1 );

		// Create test venues for relationships
		$venue_1 = tribe_venues()->set_args(
			[
				'title' => 'Test Venue for Organizers',
				'status' => 'publish',
				'address' => '123 Event St',
				'city' => 'Event City',
				'state_province' => 'EC',
				'zip' => '12345',
				'country' => 'United States',
			]
		)->create();

		// Create standard organizers
		$organizer_1 = tribe_organizers()->set_args(
			[
				'title' => 'Professional Events Company',
				'status' => 'publish',
				'email' => 'info@proevents.example.com',
				'phone' => '+1-555-123-4567',
				'website' => 'https://proevents.example.com',
				'description' => 'We organize professional conferences and workshops.',
			]
		)->create();

		$organizer_2 = tribe_organizers()->set_args(
			[
				'title' => 'Community Arts Foundation',
				'status' => 'publish',
				'email' => 'contact@artsorg.example.org',
				'phone' => '+1-555-987-6543',
				'website' => 'https://communityarts.example.org',
				'description' => 'Supporting local artists and cultural events.',
			]
		)->create();

		// Create organizer with minimal data
		$minimal_organizer = tribe_organizers()->set_args(
			[
				'title' => 'Simple Organizer',
				'status' => 'publish',
			]
		)->create();

		// Create private organizer
		$private_organizer = tribe_organizers()->set_args(
			[
				'title' => 'Private Event Planning LLC',
				'status' => 'private',
				'email' => 'private@eventplanning.example.com',
				'phone' => '+1-555-PRIVATE',
			]
		)->create();

		// Create draft organizer
		$draft_organizer = tribe_organizers()->set_args(
			[
				'title' => 'New Event Company (Coming Soon)',
				'status' => 'draft',
				'email' => 'info@neweventco.example.com',
				'description' => 'Launching our event services soon!',
			]
		)->create();

		// Create international organizer
		$intl_organizer = tribe_organizers()->set_args(
			[
				'title' => 'Global Events International',
				'status' => 'publish',
				'email' => 'info@globalevents.example.eu',
				'phone' => '+44 20 7946 0958',
				'website' => 'https://globalevents.example.eu',
				'description' => 'Organizing events across Europe and beyond.',
			]
		)->create();

		// Create organizer with special characters
		$special_organizer = tribe_organizers()->set_args(
			[
				'title' => 'Société d\'Événements "Lumière & Son"',
				'status' => 'publish',
				'email' => 'contact@lumiere-son.example.fr',
				'phone' => '+33 1 42 86 82 00',
				'description' => 'Spécialistes en événements audiovisuels.',
			]
		)->create();

		// Create organizer with HTML in description
		$html_organizer = tribe_organizers()->set_args(
			[
				'title' => 'Tech Events & Conferences',
				'status' => 'publish',
				'email' => 'hello@techevents.example.com',
				'website' => 'https://techevents.example.com',
				'description' => '<h3>What We Do</h3><p>We specialize in:</p><ul><li>Developer conferences</li><li>Tech workshops</li><li>Hackathons</li><li>Product launches</li></ul><p><strong>Contact us for your next tech event!</strong></p>',
			]
		)->create();

		// Create password-protected organizer
		$password_organizer = tribe_organizers()->set_args(
			[
				'title' => 'Password Exclusive Events Management',
				'status' => 'publish',
				'post_password' => 'password123',
				'email' => 'exclusive@vipevents.example.com',
				'phone' => '+1-555-VIP-ONLY',
				'description' => 'Managing exclusive events for select clients.',
			]
		)->create();

		// Create organizer with events
		$organizer_with_events = tribe_organizers()->set_args(
			[
				'title' => 'Active Event Productions',
				'status' => 'publish',
				'email' => 'bookings@activeevents.example.com',
				'phone' => '+1-555-BOOK-NOW',
				'website' => 'https://activeproductions.example.com',
			]
		)->create();

		// Create some events by this organizer
		tribe_events()->set_args( [
			'title'      => 'Test Event by Organizer',
			'start_date' => date( 'Y-m-d H:i:s', strtotime( '+1 week' ) ),
			'end_date'   => date( 'Y-m-d H:i:s', strtotime( '+1 week +2 hours' ) ),
			'status'     => 'publish',
			'venue'      => $venue_1->ID,
			'organizer'  => [ $organizer_with_events->ID ],
		] )->create();

		tribe_events()->set_args( [
			'title'      => 'Another Event by Organizer',
			'start_date' => date( 'Y-m-d H:i:s', strtotime( '+2 weeks' ) ),
			'end_date'   => date( 'Y-m-d H:i:s', strtotime( '+2 weeks +3 hours' ) ),
			'status'     => 'publish',
			'organizer'  => [ $organizer_with_events->ID ],
		] )->create();

		// Create organizer with multiple email formats
		$email_formats_organizer = tribe_organizers()->set_args(
			[
				'title' => 'Multi-Contact Organization',
				'status' => 'publish',
				'email' => 'info@multi-contact.example.com, support@multi-contact.example.com',
				'phone' => '+1-555-1234, +1-555-5678',
				'website' => 'https://multicontact.example.com',
			]
		)->create();

		wp_set_current_user( 0 );

		return [
			[ $venue_1->ID ],
			[
				$organizer_1->ID,
				$organizer_2->ID,
				$minimal_organizer->ID,
				$private_organizer->ID,
				$draft_organizer->ID,
				$intl_organizer->ID,
				$special_organizer->ID,
				$html_organizer->ID,
				$password_organizer->ID,
				$organizer_with_events->ID,
				$email_formats_organizer->ID,
			]
		];
	}

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

	public function test_instance_of_orm() {
		$this->assertInstanceOf( Organizer_Repository::class, $this->endpoint->get_orm() );
	}

	public function test_get_model_class() {
		$this->assertSame( Organizer_Model::class, $this->endpoint->get_model_class() );
	}

	/**
	 * @dataProvider different_user_roles_provider
	 */
	public function test_read_responses( Closure $fixture ) {
		if ( ! $this->is_readable() ) {
			return;
		}

		[ $venues, $organizers ] = $this->create_test_data();
		$fixture();

		$responses = [];
		foreach ( $organizers as $organizer_id ) {
			$responses[] = $this->assert_endpoint( '/organizers/' . $organizer_id );
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
		if ( ! $this->is_readable() ) {
			return;
		}

		[ $venues, $organizers ] = $this->create_test_data();
		$fixture();

		$responses = [];
		foreach ( $organizers as $organizer_id ) {
			$responses[] = $this->assert_endpoint( '/organizers/' . $organizer_id, 'GET', 200, [ 'password' => 'password123' ] );
		}

		$json = wp_json_encode( $responses, JSON_SNAPSHOT_OPTIONS );

		$json = str_replace( $venues, '{VENUE_ID}', $json );
		$json = str_replace( $organizers, '{ORGANIZER_ID}', $json );

		$this->assertMatchesJsonSnapshot( $json );
	}
}
