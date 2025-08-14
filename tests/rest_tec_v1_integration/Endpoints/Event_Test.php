<?php

namespace TEC\Events\Tests\REST\TEC\V1\Endpoints;

use TEC\Common\Tests\Testcases\REST\TEC\V1\Post_Entity_REST_Test_Case;
use Tribe__Events__Repositories__Event as Event_Repository;
use Tribe\Events\Models\Post_Types\Event as Event_Model;
use TEC\Events\REST\TEC\V1\Endpoints\Event;
use Closure;

class Event_Test extends Post_Entity_REST_Test_Case {
	protected $endpoint_class = Event::class;

	protected function create_test_data(): array {
		wp_set_current_user( 1 );
		$venue_1 = tribe_venues()->set_args(
			[
				'title' => 'Test Venue 1',
				'status' => 'publish',
				'address' => '123 Main St, Anytown, USA',
			]
		)->create();

		$venue_2 = tribe_venues()->set_args(
			[
				'title' => 'Test Venue 2',
				'status' => 'publish',
				'address' => '456 Main St, Anytown, USA',
			]
		)->create();

		$venue_3 = tribe_venues()->set_args(
			[
				'title' => 'Test Venue 3',
				'status' => 'publish',
				'address' => '789 Main St, Anytown, USA',
			]
		)->create();

		$organizer_1 = tribe_organizers()->set_args(
			[
				'title' => 'Test Organizer 1',
				'status' => 'publish',
				'email' => 'test@example.com',
			]
		)->create();

		$organizer_2 = tribe_organizers()->set_args(
			[
				'title' => 'Test Organizer 2',
				'status' => 'publish',
				'email' => 'test2@example.com',
			]
		)->create();

		$organizer_3 = tribe_organizers()->set_args(
			[
				'title' => 'Test Organizer 3',
				'status' => 'publish',
				'email' => 'test3@example.com',
			]
		)->create();

		// Create password-protected venue
		$password_venue = tribe_venues()->set_args(
			[
				'title' => 'Password Private Members Club',
				'status' => 'publish',
				'post_password' => 'password123',
				'address' => '999 Secret Ave, Private City, USA',
			]
		)->create();

		// Create password-protected organizer
		$password_organizer = tribe_organizers()->set_args(
			[
				'title' => 'Password Exclusive Events Co',
				'status' => 'publish',
				'post_password' => 'password123',
				'email' => 'private@exclusive.com',
			]
		)->create();

		// Create past event
		$past_event = tribe_events()->set_args( [
			'title'      => 'Past Conference 2023',
			'start_date' => '2023-01-15 09:00:00',
			'end_date'   => '2023-01-15 17:00:00',
			'status'     => 'publish',
			'venue'      => $venue_1->ID,
			'organizer'  => [ $organizer_1->ID ],
			'cost'       => '99.00',
			'featured'   => false,
			'description' => 'A past conference that already happened.',
		] )->create();

		// Create current/ongoing event
		$current_event = tribe_events()->set_args( [
			'title'      => 'Current Workshop Series',
			'start_date' => date( 'Y-m-d H:i:s', strtotime( '-2 hours' ) ),
			'end_date'   => date( 'Y-m-d H:i:s', strtotime( '+2 hours' ) ),
			'status'     => 'publish',
			'venue'      => $venue_2->ID,
			'organizer'  => [ $organizer_2->ID ],
			'cost'       => 'Free',
			'featured'   => true,
			'description' => 'An ongoing workshop happening right now.',
		] )->create();

		// Create future single-day event
		$future_event = tribe_events()->set_args( [
			'title'      => 'Future Tech Summit',
			'start_date' => date( 'Y-m-d H:i:s', strtotime( '+1 month' ) ),
			'end_date'   => date( 'Y-m-d H:i:s', strtotime( '+1 month +8 hours' ) ),
			'status'     => 'publish',
			'venue'      => $venue_3->ID,
			'organizer'  => [ $organizer_1->ID, $organizer_2->ID ],
			'cost'       => '299.99',
			'featured'   => true,
			'website'    => 'https://example.com/tech-summit',
			'description' => 'The premier technology summit of the year.',
		] )->create();

		// Create multi-day event
		$multiday_event = tribe_events()->set_args( [
			'title'      => 'Music Festival 2024',
			'start_date' => date( 'Y-m-d', strtotime( '+2 months' ) ) . ' 12:00:00',
			'end_date'   => date( 'Y-m-d', strtotime( '+2 months +3 days' ) ) . ' 23:00:00',
			'status'     => 'publish',
			'venue'      => $venue_1->ID,
			'organizer'  => [ $organizer_3->ID ],
			'cost'       => '150-500',
			'featured'   => true,
			'description' => 'A 4-day music festival with multiple stages and artists.',
		] )->create();

		// Create all-day event
		$allday_event = tribe_events()->set_args( [
			'title'      => 'Community Cleanup Day',
			'start_date' => date( 'Y-m-d', strtotime( '+2 weeks' ) ) . ' 00:00:00',
			'end_date'   => date( 'Y-m-d', strtotime( '+2 weeks' ) ) . ' 23:59:59',
			'all_day'    => true,
			'status'     => 'publish',
			'venue'      => $venue_2->ID,
			'organizer'  => [ $organizer_1->ID ],
			'cost'       => '',
			'featured'   => false,
			'description' => 'Join us for a day of community service and environmental cleanup.',
		] )->create();

		// Create private event
		$private_event = tribe_events()->set_args( [
			'title'      => 'VIP Dinner Gala',
			'start_date' => date( 'Y-m-d', strtotime( '+3 weeks' ) ) . ' 18:00:00',
			'end_date'   => date( 'Y-m-d', strtotime( '+3 weeks' ) ) . ' 22:00:00',
			'status'     => 'private',
			'venue'      => $venue_3->ID,
			'organizer'  => [ $organizer_2->ID, $organizer_3->ID ],
			'cost'       => '500',
			'featured'   => false,
			'description' => 'An exclusive dinner event for VIP members only.',
		] )->create();

		// Create draft event
		$draft_event = tribe_events()->set_args( [
			'title'      => 'Planning Stage Event',
			'start_date' => date( 'Y-m-d', strtotime( '+6 months' ) ) . ' 10:00:00',
			'end_date'   => date( 'Y-m-d', strtotime( '+6 months' ) ) . ' 16:00:00',
			'status'     => 'draft',
			'venue'      => $venue_1->ID,
			'organizer'  => [ $organizer_1->ID ],
			'cost'       => 'TBD',
			'featured'   => false,
			'description' => 'This event is still in the planning stages.',
		] )->create();

		// Create event without venue
		$no_venue_event = tribe_events()->set_args( [
			'title'      => 'Virtual Webinar: SEO Best Practices',
			'start_date' => date( 'Y-m-d', strtotime( '+10 days' ) ) . ' 15:00:00',
			'end_date'   => date( 'Y-m-d', strtotime( '+10 days' ) ) . ' 16:30:00',
			'status'     => 'publish',
			'organizer'  => [ $organizer_2->ID ],
			'cost'       => '45',
			'featured'   => false,
			'description' => 'Join us online for this informative webinar about SEO strategies.',
		] )->create();

		// Create event without organizer
		$no_organizer_event = tribe_events()->set_args( [
			'title'      => 'Community Open Mic Night',
			'start_date' => date( 'Y-m-d', strtotime( '+2 weeks' ) ) . ' 19:00:00',
			'end_date'   => date( 'Y-m-d', strtotime( '+2 weeks' ) ) . ' 22:00:00',
			'status'     => 'publish',
			'venue'      => $venue_1->ID,
			'cost'       => 'Free',
			'featured'   => false,
			'description' => 'Share your talents at our community open mic night.',
		] )->create();

		// Create event with special characters in title
		$special_chars_event = tribe_events()->set_args( [
			'title'      => 'Café & Code: Developer\'s Meetup',
			'start_date' => date( 'Y-m-d', strtotime( '+5 days' ) ) . ' 17:30:00',
			'end_date'   => date( 'Y-m-d', strtotime( '+5 days' ) ) . ' 20:00:00',
			'status'     => 'publish',
			'venue'      => $venue_1->ID,
			'organizer'  => [ $organizer_3->ID ],
			'cost'       => 'Free (drinks not included)',
			'featured'   => false,
			'description' => 'Network with fellow developers over coffee & snacks.',
		] )->create();

		// Create event with HTML in description
		$html_desc_event = tribe_events()->set_args( [
			'title'      => 'Web Development Bootcamp',
			'start_date' => date( 'Y-m-d', strtotime( '+1 week' ) ) . ' 09:00:00',
			'end_date'   => date( 'Y-m-d', strtotime( '+1 week +2 days' ) ) . ' 17:00:00',
			'status'     => 'publish',
			'venue'      => $venue_3->ID,
			'organizer'  => [ $organizer_1->ID ],
			'cost'       => '799',
			'featured'   => true,
			'website'    => 'https://bootcamp.example.com',
			'description' => '<h3>Learn Web Development</h3><p>Join our intensive 3-day bootcamp covering:</p><ul><li>HTML5 & CSS3</li><li>JavaScript ES6+</li><li>React.js</li><li>Node.js</li></ul><p><strong>Limited seats available!</strong></p>',
		] )->create();

		// Create password-protected event
		$password_event = tribe_events()->set_args( [
			'title'      => 'Password Secret Society Annual Meeting',
			'start_date' => date( 'Y-m-d', strtotime( '+1 month' ) ) . ' 20:00:00',
			'end_date'   => date( 'Y-m-d', strtotime( '+1 month' ) ) . ' 23:00:00',
			'status'     => 'publish',
			'post_password' => 'password123',
			'venue'      => $password_venue->ID,
			'organizer'  => [ $password_organizer->ID, $organizer_1->ID ],
			'cost'       => 'Members Only',
			'featured'   => false,
			'description' => 'Annual gathering of our exclusive society members.',
		] )->create();

		wp_set_current_user( 0 );

		return [
			[ $venue_1->ID, $venue_2->ID, $venue_3->ID, $password_venue->ID ],
			[ $organizer_1->ID, $organizer_2->ID, $organizer_3->ID, $password_organizer->ID ],
			[
				$past_event->ID,
				$current_event->ID,
				$future_event->ID,
				$multiday_event->ID,
				$allday_event->ID,
				$private_event->ID,
				$draft_event->ID,
				$no_venue_event->ID,
				$no_organizer_event->ID,
				$special_chars_event->ID,
				$html_desc_event->ID,
				$password_event->ID,
			]
		];
	}

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

	public function test_instance_of_orm() {
		$this->assertInstanceOf( Event_Repository::class, $this->endpoint->get_orm() );
	}

	public function test_get_model_class() {
		$this->assertSame( Event_Model::class, $this->endpoint->get_model_class() );
	}

	/**
	 * @dataProvider different_user_roles_provider
	 */
	public function test_read_responses( Closure $fixture ) {
		if ( ! $this->is_readable() ) {
			return;
		}

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
		if ( ! $this->is_readable() ) {
			return;
		}

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
}
