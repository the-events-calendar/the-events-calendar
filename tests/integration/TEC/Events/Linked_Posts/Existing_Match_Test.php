<?php
/**
 * Tests how an event save links a typed venue or organizer name to an existing one.
 *
 * @since TBD
 *
 * @package TEC\Events\Linked_Posts
 */

namespace TEC\Events\Linked_Posts;

use Codeception\TestCase\WPTestCase;
use Tribe__Events__API;
use Tribe__Events__Organizer as Organizer;
use Tribe__Events__Venue as Venue;

/**
 * Class Existing_Match_Test
 *
 * @since TBD
 */
class Existing_Match_Test extends WPTestCase {
	/**
	 * Makes each sample venue and organizer distinct within a test.
	 *
	 * @var int
	 */
	private $sequence = 0;

	/**
	 * @before
	 */
	public function set_up_user(): void {
		wp_set_current_user( $this->factory()->user->create( [ 'role' => 'administrator' ] ) );
	}

	/**
	 * @after
	 */
	public function remove_duplicate_venues_filter(): void {
		remove_all_filters( 'tribe_get_option_allow_duplicate_venues' );
	}

	/**
	 * @test
	 */
	public function it_should_link_a_typed_organizer_name_to_the_existing_organizer_without_changing_it(): void {
		$stored       = $this->organizer_details();
		$organizer_id = $this->make_organizer( $stored );

		$event_id = $this->save_event( [ 'organizer' => [ 'Organizer' => $stored['Organizer'], 'Phone' => '', 'Email' => '', 'Website' => '' ] ] );

		$this->assertEquals( [ $organizer_id ], tribe_get_organizer_ids( $event_id ) );
		$this->assertCount( 1, $this->ids_titled( Organizer::POSTTYPE, $stored['Organizer'] ) );
		$this->assert_organizer_details( $organizer_id, $stored );
	}

	/**
	 * @test
	 */
	public function it_should_link_a_typed_venue_name_to_the_existing_venue_without_changing_it(): void {
		$stored   = $this->venue_details();
		$venue_id = $this->make_venue( $stored );

		$blank_address = array_fill_keys( [ 'Address', 'City', 'Province', 'State', 'Zip', 'Country', 'Phone' ], '' );
		$event_id      = $this->save_event( [ 'venue' => [ 'Venue' => $stored['Venue'] ] + $blank_address ] );

		$this->assertEquals( $venue_id, tribe_get_venue_id( $event_id ) );
		$this->assertCount( 1, $this->ids_titled( Venue::POSTTYPE, $stored['Venue'] ) );
		$this->assert_venue_details( $venue_id, $stored );
	}

	/**
	 * @test
	 */
	public function it_should_create_a_new_venue_when_the_typed_address_conflicts(): void {
		$stored   = $this->venue_details();
		$venue_id = $this->make_venue( $stored );

		$submitted         = $stored;
		$submitted['City'] = 'Shelbyville';
		$event_id          = $this->save_event( [ 'venue' => $submitted ] );

		$new_venue_id = tribe_get_venue_id( $event_id );
		$this->assertNotEquals( $venue_id, $new_venue_id );
		$this->assertEquals( $submitted['City'], get_post_meta( $new_venue_id, '_VenueCity', true ) );
		$this->assert_venue_details( $venue_id, $stored );
	}

	/**
	 * @test
	 */
	public function it_should_create_a_new_organizer_when_the_typed_email_conflicts(): void {
		$stored       = $this->organizer_details();
		$organizer_id = $this->make_organizer( $stored );

		$submitted          = $stored;
		$submitted['Email'] = 'someone-else@example.org';
		$event_id           = $this->save_event( [ 'organizer' => $submitted ] );

		$new_organizer_ids = tribe_get_organizer_ids( $event_id );
		$this->assertNotContains( $organizer_id, $new_organizer_ids );
		$this->assertEquals( $submitted['Email'], get_post_meta( $new_organizer_ids[0], '_OrganizerEmail', true ) );
		$this->assert_organizer_details( $organizer_id, $stored );
	}

	/**
	 * @test
	 */
	public function it_should_ignore_case_spaces_and_website_formatting_when_comparing_organizer_details(): void {
		$stored       = $this->organizer_details();
		$organizer_id = $this->make_organizer( $stored );

		$host      = wp_parse_url( $stored['Website'], PHP_URL_HOST );
		$submitted = [
			'Organizer' => $stored['Organizer'],
			'Email'     => '  ' . strtoupper( $stored['Email'] ) . ' ',
			'Website'   => 'http://www.' . preg_replace( '/^www\./', '', $host ) . '/',
		];
		$event_id  = $this->save_event( [ 'organizer' => $submitted ] );

		$this->assertEquals( [ $organizer_id ], tribe_get_organizer_ids( $event_id ) );
		$this->assert_organizer_details( $organizer_id, $stored );
	}

	/**
	 * @test
	 */
	public function it_should_ignore_case_and_extra_spaces_when_comparing_venue_details(): void {
		$stored   = $this->venue_details();
		$venue_id = $this->make_venue( $stored );

		$submitted            = $stored;
		$submitted['Address'] = '  ' . strtoupper( str_replace( ' ', '   ', $stored['Address'] ) );
		$submitted['City']    = strtolower( $stored['City'] ) . ' ';
		$event_id             = $this->save_event( [ 'venue' => $submitted ] );

		$this->assertEquals( $venue_id, tribe_get_venue_id( $event_id ) );
		$this->assert_venue_details( $venue_id, $stored );
	}

	/**
	 * @test
	 */
	public function it_should_not_fill_in_details_the_existing_venue_is_missing(): void {
		$stored          = $this->venue_details();
		$stored['Phone'] = '';
		$stored['Zip']   = '';
		$venue_id        = $this->make_venue( $stored );

		$submitted          = $stored;
		$submitted['Phone'] = '555-0199';
		$submitted['Zip']   = '62702';
		$event_id           = $this->save_event( [ 'venue' => $submitted ] );

		$this->assertEquals( $venue_id, tribe_get_venue_id( $event_id ) );
		$this->assert_venue_details( $venue_id, $stored );
	}

	/**
	 * @test
	 */
	public function it_should_link_the_same_named_venue_whose_address_matches_then_the_oldest(): void {
		$name          = 'Shared Name Hall';
		$oldest        = $this->venue_details( $name );
		$matching      = $this->venue_details( $name );
		$oldest_id     = $this->make_venue( $oldest );
		$matching_id   = $this->make_venue( $matching );

		$with_address = $this->save_event( [ 'venue' => $matching ] );
		$name_only    = $this->save_event( [ 'venue' => [ 'Venue' => $name ] ] );

		$this->assertEquals( $matching_id, tribe_get_venue_id( $with_address ) );
		$this->assertEquals( $oldest_id, tribe_get_venue_id( $name_only ) );
		$this->assertCount( 2, $this->ids_titled( Venue::POSTTYPE, $name ) );
	}

	/**
	 * @test
	 */
	public function it_should_only_link_unpublished_venues_the_submitter_can_edit(): void {
		$author_id = $this->factory()->user->create( [ 'role' => 'author' ] );

		$others_draft    = $this->venue_details();
		$others_draft_id = $this->make_venue( $others_draft, 'draft' );
		$own_draft       = $this->venue_details();
		$own_draft_id    = $this->make_venue( $own_draft, 'draft', $author_id );

		wp_set_current_user( $author_id );
		$others_event = $this->save_event( [ 'venue' => [ 'Venue' => $others_draft['Venue'] ] ], 'draft' );
		$own_event    = $this->save_event( [ 'venue' => [ 'Venue' => $own_draft['Venue'] ] ], 'draft' );

		$this->assertNotEquals( $others_draft_id, tribe_get_venue_id( $others_event ) );
		$this->assertCount( 2, $this->ids_titled( Venue::POSTTYPE, $others_draft['Venue'] ) );
		$this->assert_venue_details( $others_draft_id, $others_draft );
		$this->assertEquals( $own_draft_id, tribe_get_venue_id( $own_event ) );
	}

	/**
	 * @test
	 */
	public function it_should_create_a_new_venue_when_duplicate_venues_are_allowed(): void {
		add_filter( 'tribe_get_option_allow_duplicate_venues', '__return_true' );
		$stored   = $this->venue_details();
		$venue_id = $this->make_venue( $stored );

		$event_id = $this->save_event( [ 'venue' => [ 'Venue' => $stored['Venue'] ] ] );

		$this->assertNotEquals( $venue_id, tribe_get_venue_id( $event_id ) );
		$this->assert_venue_details( $venue_id, $stored );
	}

	/**
	 * Event Aggregator and the CSV importer find or create venues and organizers themselves and save the event
	 * with their IDs only; that path must keep linking exactly those posts.
	 *
	 * @test
	 */
	public function it_should_link_venue_and_organizer_ids_as_given_like_the_importers_do(): void {
		$venue        = $this->venue_details();
		$venue_id     = $this->make_venue( $venue );
		$organizer    = $this->organizer_details();
		$organizer_id = $this->make_organizer( $organizer );
		$venues       = wp_count_posts( Venue::POSTTYPE )->publish;
		$organizers   = wp_count_posts( Organizer::POSTTYPE )->publish;

		$event_id = $this->save_event(
			[
				'venue'     => [ 'VenueID' => $venue_id ],
				'organizer' => [ 'OrganizerID' => [ $organizer_id ] ],
			]
		);

		$this->assertEquals( $venue_id, tribe_get_venue_id( $event_id ) );
		$this->assertEquals( [ $organizer_id ], tribe_get_organizer_ids( $event_id ) );
		$this->assertEquals( $venues, wp_count_posts( Venue::POSTTYPE )->publish );
		$this->assertEquals( $organizers, wp_count_posts( Organizer::POSTTYPE )->publish );
		$this->assert_venue_details( $venue_id, $venue );
		$this->assert_organizer_details( $organizer_id, $organizer );
	}

	/**
	 * @test
	 */
	public function it_should_compare_the_region_the_venue_was_saved_with_when_state_and_province_are_both_sent(): void {
		$stored             = $this->venue_details();
		$stored['Country']  = 'Canada';
		$stored['Province'] = 'Ontario';
		$venue_id           = $this->make_venue( $stored );

		// The classic form keeps the State select's value after the country changes, so both fields arrive.
		$event_id = $this->save_event( [ 'venue' => $stored ] );

		$this->assertEquals( 'Ontario', get_post_meta( $venue_id, '_VenueStateProvince', true ) );
		$this->assertEquals( $venue_id, tribe_get_venue_id( $event_id ) );
		$this->assertCount( 1, $this->ids_titled( Venue::POSTTYPE, $stored['Venue'] ) );
	}

	/**
	 * @test
	 */
	public function it_should_link_a_venue_whose_typed_details_carry_the_slashes_of_a_form_submission(): void {
		$stored            = $this->venue_details( "O'Brien's Pub" );
		$stored['Address'] = "1 King's Road";
		$venue_id          = $this->make_venue( $stored );

		// The classic event form passes the request data on with the slashes WordPress adds to it.
		$event_id = $this->save_event( [ 'venue' => wp_slash( $stored ) ] );

		$this->assertEquals( $venue_id, tribe_get_venue_id( $event_id ) );
		$this->assertCount( 1, $this->ids_titled( Venue::POSTTYPE, $stored['Venue'] ) );
		$this->assert_venue_details( $venue_id, $stored );
	}

	/**
	 * @test
	 */
	public function it_should_link_a_typed_name_with_an_ampersand_to_an_organizer_saved_without_unfiltered_html(): void {
		// An author has no `unfiltered_html`, so kses stores the `&` in the title as `&amp;`.
		wp_set_current_user( $this->factory()->user->create( [ 'role' => 'author' ] ) );
		$stored              = $this->organizer_details();
		$stored['Organizer'] = 'Bar & Grill';
		$organizer_id        = $this->make_organizer( $stored );
		$this->assertEquals( 'Bar &amp; Grill', get_post_field( 'post_title', $organizer_id ) );

		$event_id = $this->save_event( [ 'organizer' => [ 'Organizer' => 'Bar & Grill', 'Phone' => '', 'Email' => '', 'Website' => '' ] ] );

		$this->assertEquals( [ $organizer_id ], tribe_get_organizer_ids( $event_id ) );
		$this->assertCount( 1, $this->ids_titled( Organizer::POSTTYPE, 'Bar &amp; Grill' ) );
		$this->assertCount( 0, $this->ids_titled( Organizer::POSTTYPE, 'Bar & Grill' ) );
	}

	/**
	 * Saves an event through the same API the event forms use.
	 *
	 * @param array{venue?: array<string,mixed>, organizer?: array<string,mixed>} $linked_posts The venue and organizer submission.
	 * @param string                                                             $status       The event status.
	 *
	 * @return int The event ID.
	 */
	private function save_event( array $linked_posts, string $status = 'publish' ): int {
		return Tribe__Events__API::createEvent(
			[
				'post_title'     => 'Event ' . ++$this->sequence,
				'post_status'    => $status,
				'EventStartDate' => '2030-01-01',
				'EventEndDate'   => '2030-01-01',
				'EventStartHour' => '10',
				'EventEndHour'   => '12',
			] + $linked_posts
		);
	}

	/**
	 * @param string|null $name The venue name, or null for a random one.
	 *
	 * @return array{Venue:string,Address:string,City:string,State:string,Zip:string,Country:string,Phone:string}
	 */
	private function venue_details( ?string $name = null ): array {
		$n = ++$this->sequence;

		return [
			'Venue'   => $name ?? "Venue {$n}",
			'Address' => "{$n} Main St",
			'City'    => 'Springfield',
			'State'   => 'IL',
			'Zip'     => '62701',
			'Country' => 'United States',
			'Phone'   => "555-01{$n}0",
		];
	}

	/**
	 * @return array{Organizer:string,Phone:string,Email:string,Website:string}
	 */
	private function organizer_details(): array {
		$n = ++$this->sequence;

		return [
			'Organizer' => "Organizer {$n}",
			'Phone'     => "555-02{$n}0",
			'Email'     => "organizer{$n}@example.com",
			'Website'   => "https://organizer{$n}.example.com",
		];
	}

	/**
	 * @param array<string,string> $details The venue details.
	 * @param string               $status  The venue status.
	 * @param int                  $author  The venue author, or 0 for the current user.
	 *
	 * @return int The venue ID.
	 */
	private function make_venue( array $details, string $status = 'publish', int $author = 0 ): int {
		$venue_id = Venue::instance()->create( $details, $status );

		if ( $author ) {
			wp_update_post( [ 'ID' => $venue_id, 'post_author' => $author ] );
		}

		return $venue_id;
	}

	/**
	 * @param array<string,string> $details The organizer details.
	 *
	 * @return int The organizer ID.
	 */
	private function make_organizer( array $details ): int {
		return Organizer::instance()->create( $details, 'publish' );
	}

	/**
	 * @return int[] The IDs of the posts of the type with the title, in any status but trash.
	 */
	private function ids_titled( string $post_type, string $title ): array {
		return get_posts(
			[
				'post_type'      => $post_type,
				'title'          => $title,
				'post_status'    => 'any',
				'fields'         => 'ids',
				'posts_per_page' => -1,
			]
		);
	}

	/**
	 * @param array<string,string> $expected The venue details as first saved.
	 */
	private function assert_venue_details( int $venue_id, array $expected ): void {
		foreach ( [ 'Address', 'City', 'State', 'Zip', 'Country', 'Phone' ] as $key ) {
			$this->assertEquals( $expected[ $key ], get_post_meta( $venue_id, '_Venue' . $key, true ), "Venue {$key} changed." );
		}
	}

	/**
	 * @param array<string,string> $expected The organizer details as first saved.
	 */
	private function assert_organizer_details( int $organizer_id, array $expected ): void {
		foreach ( [ 'Phone', 'Email', 'Website' ] as $key ) {
			$this->assertEquals( $expected[ $key ], get_post_meta( $organizer_id, '_Organizer' . $key, true ), "Organizer {$key} changed." );
		}
	}
}
