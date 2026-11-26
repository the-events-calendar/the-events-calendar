<?php

namespace TEC\Events\Integrations\Plugins\Elementor\Widgets;

use Codeception\TestCase\WPTestCase;
use Generator;
use Tribe\Tests\Traits\With_Uopz;
use WP_Post;

/**
 * The Elementor Venue and Organizer widgets print a linked post's details only when the current user
 * may see that post.
 */
class Linked_Post_Visibility_Test extends WPTestCase {
	use With_Uopz;

	/**
	 * The Event the widgets render for.
	 *
	 * @var int
	 */
	private int $event_id = 0;

	/**
	 * @before
	 */
	public function set_up_widgets(): void {
		wp_set_current_user( 0 );

		// Every show toggle falls back to its default when the setting is missing.
		$this->set_class_fn_return( 'Elementor\Controls_Stack', 'get_settings_for_display', [] );

		add_filter( 'tec_events_elementor_widget_event_id', fn() => $this->event_id );
	}

	public function hidden_linked_post_provider(): Generator {
		yield 'password protected' => [ [ 'post_password' => wp_generate_password( 12, false ) ] ];
		yield 'draft' => [ [ 'post_status' => 'draft' ] ];
		yield 'pending' => [ [ 'post_status' => 'pending' ] ];
		yield 'private' => [ [ 'post_status' => 'private' ] ];
	}

	/**
	 * @test
	 * @dataProvider hidden_linked_post_provider
	 */
	public function it_should_not_print_the_details_of_a_hidden_venue( array $venue_overrides ): void {
		$venue          = $this->create_venue();
		$this->event_id = $this->create_event( [ 'venue' => $venue['id'] ] )->ID;
		wp_update_post( array_merge( [ 'ID' => $venue['id'] ], $venue_overrides ) );

		$html = ( new Event_Venue() )->get_output();

		$this->assertStringContainsString( $venue['title'], $html );
		$this->assertStringNotContainsString( $venue['address'], $html );
		$this->assertStringNotContainsString( $venue['city'], $html );
		$this->assertStringNotContainsString( $venue['phone'], $html );
	}

	/**
	 * @test
	 */
	public function it_should_print_the_details_of_a_published_venue(): void {
		$venue          = $this->create_venue();
		$this->event_id = $this->create_event( [ 'venue' => $venue['id'] ] )->ID;

		$html = ( new Event_Venue() )->get_output();

		$this->assertStringContainsString( $venue['address'], $html );
		$this->assertStringContainsString( $venue['city'], $html );
		$this->assertStringContainsString( $venue['phone'], $html );
	}

	/**
	 * @test
	 */
	public function it_should_print_the_details_of_a_private_venue_to_a_user_who_can_read_it(): void {
		$venue          = $this->create_venue();
		$this->event_id = $this->create_event( [ 'venue' => $venue['id'] ] )->ID;
		wp_update_post( [ 'ID' => $venue['id'], 'post_status' => 'private' ] );
		wp_set_current_user( static::factory()->user->create( [ 'role' => 'administrator' ] ) );

		$html = ( new Event_Venue() )->get_output();

		$this->assertStringContainsString( $venue['address'], $html );
	}

	/**
	 * @test
	 * @dataProvider hidden_linked_post_provider
	 */
	public function it_should_not_print_the_details_of_a_hidden_organizer( array $organizer_overrides ): void {
		$organizer      = $this->create_organizer();
		$this->event_id = $this->create_event( [ 'organizer' => $organizer['id'] ] )->ID;
		wp_update_post( array_merge( [ 'ID' => $organizer['id'] ], $organizer_overrides ) );

		$html = html_entity_decode( ( new Event_Organizer() )->get_output() );

		$this->assertStringContainsString( $organizer['title'], $html );
		$this->assertStringNotContainsString( $organizer['phone'], $html );
		$this->assertStringNotContainsString( $organizer['email'], $html );
		$this->assertStringNotContainsString( $organizer['website'], $html );
	}

	/**
	 * @test
	 */
	public function it_should_print_the_details_of_a_published_organizer(): void {
		$organizer      = $this->create_organizer();
		$this->event_id = $this->create_event( [ 'organizer' => $organizer['id'] ] )->ID;

		$html = html_entity_decode( ( new Event_Organizer() )->get_output() );

		$this->assertStringContainsString( $organizer['phone'], $html );
		$this->assertStringContainsString( $organizer['email'], $html );
		$this->assertStringContainsString( $organizer['website'], $html );
	}

	/**
	 * Creates a published Venue with contact details.
	 *
	 * @return array{id: int, title: string, address: string, city: string, phone: string} The Venue ID and details.
	 */
	private function create_venue(): array {
		$venue = [
			'title'   => 'Evergreen Hall',
			'address' => '742 Evergreen Terrace',
			'city'    => 'Springfield',
			'phone'   => '555-0100',
		];

		$venue['id'] = tribe_venues()->set_args(
			[
				'title'   => $venue['title'],
				'address' => $venue['address'],
				'city'    => $venue['city'],
				'phone'   => $venue['phone'],
				'status'  => 'publish',
			]
		)->create()->ID;

		return $venue;
	}

	/**
	 * Creates a published Organizer with contact details.
	 *
	 * @return array{id: int, title: string, email: string, phone: string, website: string} The Organizer ID and details.
	 */
	private function create_organizer(): array {
		$organizer = [
			'title'   => 'Springfield Events Co',
			'email'   => 'organizer@example.com',
			'phone'   => '555-0101',
			'website' => 'https://organizer.example.com',
		];

		$organizer['id'] = tribe_organizers()->set_args(
			[
				'title'   => $organizer['title'],
				'email'   => $organizer['email'],
				'phone'   => $organizer['phone'],
				'website' => $organizer['website'],
				'status'  => 'publish',
			]
		)->create()->ID;

		return $organizer;
	}

	/**
	 * Creates a published Event linked to the posts.
	 *
	 * @param array<string,int> $linked The linked posts, keyed by `venue` or `organizer`.
	 *
	 * @return WP_Post The Event.
	 */
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
