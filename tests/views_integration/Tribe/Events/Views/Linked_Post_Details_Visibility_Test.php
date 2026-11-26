<?php

namespace Tribe\Events\Views;

use ArrayObject;
use Codeception\TestCase\WPTestCase;
use Generator;
use Tribe\Events\Views\V2\Template;
use Tribe\Events\Views\V2\View;
use Tribe\Events\Views\V2\Views\Day_View;
use Tribe\Events\Views\V2\Views\Latest_Past_View;
use Tribe\Events\Views\V2\Views\List_View;
use Tribe__Events__Editor__Blocks__Event_Organizer as Event_Organizer_Block;
use Tribe__Events__Editor__Provider as Editor_Provider;
use WP_Post;

/**
 * The templates that show an Event's Venue and Organizer print their details only when the current
 * user may see the linked post.
 */
class Linked_Post_Details_Visibility_Test extends WPTestCase {
	/**
	 * @before
	 */
	public function log_out(): void {
		wp_set_current_user( 0 );
	}

	/**
	 * @after
	 */
	public function reset_post_data(): void {
		wp_reset_postdata();
	}

	public function hidden_linked_post_provider(): Generator {
		yield 'password protected' => [ [ 'post_password' => wp_generate_password( 12, false ) ] ];
		yield 'draft' => [ [ 'post_status' => 'draft' ] ];
		yield 'pending' => [ [ 'post_status' => 'pending' ] ];
		yield 'private' => [ [ 'post_status' => 'private' ] ];
	}

	public function venue_template_provider(): Generator {
		yield 'single event meta' => [ fn( WP_Post $event ) => $this->render_classic( $event, 'modules/meta/venue' ) ];
		yield 'venue block' => [ fn( WP_Post $event ) => $this->render_venue_block( $event ) ];
		yield 'list view' => [ fn( WP_Post $event ) => $this->render_view( $event, List_View::class, 'list/event/venue' ) ];
		yield 'day view' => [ fn( WP_Post $event ) => $this->render_view( $event, Day_View::class, 'day/event/venue' ) ];
		yield 'latest past view' => [ fn( WP_Post $event ) => $this->render_view( $event, Latest_Past_View::class, 'latest-past/event/venue' ) ];
	}

	public function organizer_template_provider(): Generator {
		yield 'single event meta' => [ fn( WP_Post $event, int $organizer_id ) => $this->render_classic( $event, 'modules/meta/organizer' ) ];
		yield 'organizer block' => [ fn( WP_Post $event, int $organizer_id ) => $this->render_organizer_block( $event, $organizer_id ) ];
		yield 'organizer block, given the post' => [ fn( WP_Post $event, int $organizer_id ) => $this->render_organizer_block( $event, get_post( $organizer_id ) ) ];
		yield 'classic event details block' => [ fn( WP_Post $event, int $organizer_id ) => $this->render_block_part( $event, 'organizer', [ 'attributes' => [] ] ) ];
	}

	public function map_template_provider(): Generator {
		yield 'single event map' => [ fn( WP_Post $event ) => $this->render_classic( $event, 'modules/meta/map' ), 'tribe-events-venue-map' ];
		yield 'venue block map' => [ fn( WP_Post $event ) => $this->render_venue_block_map( $event ), 'tribe-block__venue__map' ];
	}

	public function hidden_venue_provider(): Generator {
		yield from $this->hidden_linked_post_cases( $this->venue_template_provider() );
	}

	public function hidden_organizer_provider(): Generator {
		yield from $this->hidden_linked_post_cases( $this->organizer_template_provider() );
	}

	public function hidden_map_provider(): Generator {
		yield from $this->hidden_linked_post_cases( $this->map_template_provider() );
	}

	/**
	 * @test
	 * @dataProvider hidden_venue_provider
	 */
	public function it_should_not_print_the_details_of_a_hidden_venue( callable $render, array $venue_overrides ): void {
		$venue = $this->create_venue();
		$event = $this->create_event( [ 'venue' => $venue['id'] ] );
		wp_update_post( array_merge( [ 'ID' => $venue['id'] ], $venue_overrides ) );

		$html = $render( $event );

		$this->assertStringNotContainsString( $venue['address'], $html );
		$this->assertStringNotContainsString( $venue['city'], $html );
		$this->assertStringNotContainsString( $venue['phone'], $html );
	}

	/**
	 * @test
	 * @dataProvider venue_template_provider
	 */
	public function it_should_print_the_details_of_a_published_venue( callable $render ): void {
		$venue = $this->create_venue();
		$event = $this->create_event( [ 'venue' => $venue['id'] ] );

		$html = $render( $event );

		$this->assertStringContainsString( $venue['address'], $html );
		$this->assertStringContainsString( $venue['city'], $html );
	}

	/**
	 * @test
	 * @dataProvider venue_template_provider
	 */
	public function it_should_print_the_details_of_a_private_venue_to_a_user_who_can_read_it( callable $render ): void {
		$venue = $this->create_venue();
		$event = $this->create_event( [ 'venue' => $venue['id'] ] );
		wp_update_post( [ 'ID' => $venue['id'], 'post_status' => 'private' ] );
		wp_set_current_user( static::factory()->user->create( [ 'role' => 'administrator' ] ) );

		$html = $render( $event );

		$this->assertStringContainsString( $venue['address'], $html );
		$this->assertStringContainsString( $venue['city'], $html );
	}

	/**
	 * @test
	 * @dataProvider hidden_organizer_provider
	 */
	public function it_should_not_print_the_details_of_a_hidden_organizer( callable $render, array $organizer_overrides ): void {
		$organizer = $this->create_organizer();
		$event     = $this->create_event( [ 'organizer' => $organizer['id'] ] );
		wp_update_post( array_merge( [ 'ID' => $organizer['id'] ], $organizer_overrides ) );

		$html = $render( $event, $organizer['id'] );

		$this->assertStringNotContainsString( $organizer['phone'], $html );
		$this->assertStringNotContainsString( $organizer['email'], html_entity_decode( $html ) );
		$this->assertStringNotContainsString( $organizer['website'], $html );
	}

	/**
	 * @test
	 * @dataProvider organizer_template_provider
	 */
	public function it_should_print_the_details_of_a_published_organizer( callable $render ): void {
		$organizer = $this->create_organizer();
		$event     = $this->create_event( [ 'organizer' => $organizer['id'] ] );

		$html = $render( $event, $organizer['id'] );

		$this->assertStringContainsString( $organizer['phone'], $html );
		$this->assertStringContainsString( $organizer['email'], html_entity_decode( $html ) );
		$this->assertStringContainsString( $organizer['website'], $html );
	}

	/**
	 * @test
	 * @dataProvider organizer_template_provider
	 */
	public function it_should_print_the_details_of_a_private_organizer_to_a_user_who_can_read_it( callable $render ): void {
		$organizer = $this->create_organizer();
		$event     = $this->create_event( [ 'organizer' => $organizer['id'] ] );
		wp_update_post( [ 'ID' => $organizer['id'], 'post_status' => 'private' ] );
		wp_set_current_user( static::factory()->user->create( [ 'role' => 'administrator' ] ) );

		$html = $render( $event, $organizer['id'] );

		$this->assertStringContainsString( $organizer['phone'], $html );
	}

	/**
	 * Building the map hands the Venue address to the map script, so a hidden Venue's map must not be built at all.
	 *
	 * @test
	 * @dataProvider hidden_map_provider
	 */
	public function it_should_not_build_the_map_of_a_hidden_venue( callable $render, string $map_class, array $venue_overrides ): void {
		$venue = $this->create_venue();
		$event = $this->create_event( [ 'venue' => $venue['id'] ] );
		wp_update_post( array_merge( [ 'ID' => $venue['id'] ], $venue_overrides ) );
		$mapped_venues = $this->record_mapped_venues();

		$html = $render( $event );

		$this->assertStringNotContainsString( $map_class, $html );
		$this->assertNotContains( $venue['id'], $mapped_venues->getArrayCopy() );
	}

	/**
	 * @test
	 * @dataProvider map_template_provider
	 */
	public function it_should_print_the_map_of_a_published_venue( callable $render, string $map_class ): void {
		$venue = $this->create_venue();
		$event = $this->create_event( [ 'venue' => $venue['id'] ] );

		$html = $render( $event );

		$this->assertStringContainsString( $map_class, $html );
	}

	/**
	 * @test
	 * @dataProvider map_template_provider
	 */
	public function it_should_print_the_map_of_a_private_venue_to_a_user_who_can_read_it( callable $render, string $map_class ): void {
		$venue = $this->create_venue();
		$event = $this->create_event( [ 'venue' => $venue['id'] ] );
		wp_update_post( [ 'ID' => $venue['id'], 'post_status' => 'private' ] );
		wp_set_current_user( static::factory()->user->create( [ 'role' => 'administrator' ] ) );

		$html = $render( $event );

		$this->assertStringContainsString( $map_class, $html );
	}

	/**
	 * Crosses each template with each way a linked post is hidden from a logged out visitor.
	 *
	 * @param Generator $templates The template cases, each yielding its render callback and any further arguments.
	 *
	 * @return Generator The cases, each yielding the template case's arguments followed by the linked post overrides.
	 */
	private function hidden_linked_post_cases( Generator $templates ): Generator {
		foreach ( $templates as $template => $arguments ) {
			foreach ( $this->hidden_linked_post_provider() as $state => [ $overrides ] ) {
				yield "{$template}, {$state}" => array_merge( $arguments, [ $overrides ] );
			}
		}
	}

	/**
	 * Records the Venue of every map built from here on.
	 *
	 * @return ArrayObject<int,int> The IDs of the Venues whose map was built.
	 */
	private function record_mapped_venues(): ArrayObject {
		$mapped_venues = new ArrayObject();

		add_action(
			'tribe_events_map_embedded',
			static function ( $index, $venue_id ) use ( $mapped_venues ) {
				$mapped_venues[] = $venue_id;
			},
			10,
			2
		);

		return $mapped_venues;
	}

	/**
	 * Renders a classic single Event template part with the Event as the global post.
	 *
	 * @param WP_Post $event    The Event to render the template for.
	 * @param string  $template The template part, relative to the views directory.
	 *
	 * @return string The rendered HTML.
	 */
	private function render_classic( WP_Post $event, string $template ): string {
		$GLOBALS['post'] = $event;
		setup_postdata( $event );

		ob_start();
		tribe_get_template_part( $template );

		return ob_get_clean();
	}

	/**
	 * Renders the Event Venue block's venue part with the Event as the global post.
	 *
	 * @param WP_Post $event The Event to render the block part for.
	 *
	 * @return string The rendered HTML.
	 */
	private function render_venue_block( WP_Post $event ): string {
		return $this->render_block_part(
			$event,
			'venue',
			[
				'venue_id'      => null,
				'show_map_link' => false,
			]
		);
	}

	/**
	 * Renders the Event Venue block's map part with the Event as the global post.
	 *
	 * @param WP_Post $event The Event to render the block part for.
	 *
	 * @return string The rendered HTML.
	 */
	private function render_venue_block_map( WP_Post $event ): string {
		return $this->render_block_part(
			$event,
			'map',
			[
				'venue_id' => null,
				'show_map' => true,
			]
		);
	}

	/**
	 * Renders a block template part with the Event as the global post.
	 *
	 * @param WP_Post             $event The Event to render the block part for.
	 * @param string              $part  The part, relative to the `blocks/parts` directory.
	 * @param array{venue_id?: ?int, show_map_link?: bool, show_map?: bool, attributes?: array<string,string>} $args The variables the part reads.
	 *
	 * @return string The rendered HTML.
	 */
	private function render_block_part( WP_Post $event, string $part, array $args ): string {
		$this->load_blocks();

		$GLOBALS['post'] = $event;
		setup_postdata( $event );

		return tribe( 'events.editor.template' )->template( [ 'blocks', 'parts', $part ], $args, false );
	}

	/**
	 * Renders the Event Organizer block for the Organizer, with the Event as the global post.
	 *
	 * @param WP_Post     $event     The Event the block is in.
	 * @param int|WP_Post $organizer The Organizer the block shows, as the block attribute holds it.
	 *
	 * @return string The rendered HTML.
	 */
	private function render_organizer_block( WP_Post $event, $organizer ): string {
		$this->load_blocks();

		$GLOBALS['post'] = $event;
		setup_postdata( $event );

		return ( new Event_Organizer_Block() )->render( [ 'organizer' => $organizer ] );
	}

	/**
	 * Loads the block editor templates, which the suite does not load by default.
	 *
	 * @return void
	 */
	private function load_blocks(): void {
		add_filter( 'tribe_editor_should_load_blocks', '__return_true', PHP_INT_MAX );
		tribe( Editor_Provider::class )->register();
	}

	/**
	 * Renders a View's Event venue partial for the Event.
	 *
	 * @param WP_Post $event      The Event to render the partial for.
	 * @param string  $view_class The View the partial belongs to.
	 * @param string  $partial    The partial, relative to the V2 views directory.
	 *
	 * @return string The rendered HTML.
	 */
	private function render_view( WP_Post $event, string $view_class, string $partial ): string {
		$template = new Template( View::make( $view_class ) );

		return $template->template(
			$partial,
			[
				'event' => tribe_get_event( $event->ID ),
				'slug'  => $template->get_view()->get_slug(),
			],
			false
		);
	}

	/**
	 * Creates a published Venue with an address and a phone number.
	 *
	 * @return array{id: int, address: string, city: string, phone: string} The Venue ID and details.
	 */
	private function create_venue(): array {
		$venue = [
			'address' => '742 Evergreen Terrace',
			'city'    => 'Springfield',
			'phone'   => '555-0100',
		];

		$venue['id'] = tribe_venues()->set_args(
			[
				'title'   => 'Venue',
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
	 * @return array{id: int, email: string, phone: string, website: string} The Organizer ID and details.
	 */
	private function create_organizer(): array {
		$organizer = [
			'email'   => 'organizer@example.com',
			'phone'   => '555-0101',
			'website' => 'https://organizer.example.com',
		];

		$organizer['id'] = tribe_organizers()->set_args(
			[
				'title'   => 'Organizer',
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
