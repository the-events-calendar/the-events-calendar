<?php

namespace Tribe\Events;

use Codeception\TestCase\WPTestCase;
use Tribe__Events__API;
use Tribe__Events__Linked_Posts;
use Tribe__Events__Ignored_Events as Ignored_Events;

class Linked_Posts_Access_Test extends WPTestCase {
	private function create_event(): int {
		return tribe_events()->set_args(
			[
				'title'      => 'Carrier',
				'status'     => 'draft',
				'start_date' => '2030-01-01 10:00:00',
				'end_date'   => '2030-01-01 12:00:00',
			]
		)->create()->ID;
	}

	private function create_organizer( string $password = '' ): int {
		return tribe_organizers()->set_args(
			[
				'title'         => 'Organizer',
				'status'        => 'publish',
				'post_password' => $password,
			]
		)->create()->ID;
	}

	/**
	 * @param array<int> $organizer_ids The organizers the save submits.
	 */
	private function save_organizers( int $event_id, array $organizer_ids ): void {
		Tribe__Events__API::saveEventMeta(
			$event_id,
			[
				'EventStartDate'   => '2030-01-01',
				'EventStartHour'   => '10',
				'EventStartMinute' => '00',
				'EventEndDate'     => '2030-01-01',
				'EventEndHour'     => '12',
				'EventEndMinute'   => '00',
				'EventOrganizerID' => $organizer_ids,
			]
		);
	}

	/**
	 * @return array<int>
	 */
	private function linked_organizers( int $event_id ): array {
		return array_map( 'absint', get_post_meta( $event_id, '_EventOrganizerID', false ) );
	}

	/**
	 * @test
	 */
	public function it_should_not_link_a_post_of_another_type_as_organizer() {
		$page_id  = $this->factory()->post->create( [ 'post_type' => 'page' ] );
		$event_id = tribe_events()->set_args(
			[
				'title'      => 'Carrier',
				'status'     => 'draft',
				'start_date' => '2030-01-01 10:00:00',
				'end_date'   => '2030-01-01 12:00:00',
			]
		)->create()->ID;

		$organizer_id = tribe_organizers()->set_args( [ 'title' => 'Organizer', 'status' => 'publish' ] )->create()->ID;

		Tribe__Events__API::saveEventMeta(
			$event_id,
			[
				'EventStartDate'   => '2030-01-01',
				'EventStartHour'   => '10',
				'EventStartMinute' => '00',
				'EventEndDate'     => '2030-01-01',
				'EventEndHour'     => '12',
				'EventEndMinute'   => '00',
				'EventOrganizerID' => [ $organizer_id, $page_id ],
			]
		);

		$linked = array_map( 'absint', get_post_meta( $event_id, '_EventOrganizerID', false ) );

		$this->assertContains( $organizer_id, $linked );
		$this->assertNotContains( $page_id, $linked );
	}

	/**
	 * @test
	 */
	public function it_should_not_restore_an_event_that_is_not_ignored() {
		$event_id = tribe_events()->set_args(
			[
				'title'      => 'Draft',
				'status'     => 'draft',
				'start_date' => '2030-01-01 10:00:00',
				'end_date'   => '2030-01-01 12:00:00',
			]
		)->create()->ID;

		Ignored_Events::instance()->restore_event( $event_id );

		$this->assertSame( 'draft', get_post_status( $event_id ) );
	}

	/**
	 * @test
	 */
	public function it_should_not_link_a_password_protected_organizer_an_author_cannot_read() {
		$event_id     = $this->create_event();
		$organizer_id = $this->create_organizer( 'organizer-password' );

		wp_set_current_user( $this->factory()->user->create( [ 'role' => 'author' ] ) );

		$this->save_organizers( $event_id, [ $organizer_id ] );

		$this->assertNotContains( $organizer_id, $this->linked_organizers( $event_id ) );
	}

	/**
	 * @test
	 */
	public function it_should_link_a_password_protected_organizer_the_user_can_edit() {
		$event_id     = $this->create_event();
		$organizer_id = $this->create_organizer( 'organizer-password' );

		wp_set_current_user( $this->factory()->user->create( [ 'role' => 'editor' ] ) );

		$this->save_organizers( $event_id, [ $organizer_id ] );

		$this->assertContains( $organizer_id, $this->linked_organizers( $event_id ) );
	}

	/**
	 * @test
	 */
	public function it_should_keep_a_password_protected_organizer_the_event_already_links() {
		$event_id     = $this->create_event();
		$organizer_id = $this->create_organizer( 'organizer-password' );
		add_post_meta( $event_id, '_EventOrganizerID', $organizer_id );

		wp_set_current_user( $this->factory()->user->create( [ 'role' => 'author' ] ) );

		$this->save_organizers( $event_id, [ $organizer_id ] );

		$this->assertContains( $organizer_id, $this->linked_organizers( $event_id ) );
	}

	/**
	 * @test
	 */
	public function it_should_not_drop_every_organizer_when_a_stored_value_is_empty() {
		$event_id     = $this->create_event();
		$organizer_id = $this->create_organizer();
		add_post_meta( $event_id, '_EventOrganizerID', '' );
		add_post_meta( $event_id, '_EventOrganizerID', $organizer_id );

		Tribe__Events__Linked_Posts::instance()->prune_invalid_linked_posts( $event_id );

		$this->assertContains( $organizer_id, $this->linked_organizers( $event_id ) );
	}

	/**
	 * @test
	 */
	public function it_should_not_link_a_password_protected_organizer_an_author_sets_through_the_repository() {
		$organizer_id = $this->create_organizer( 'organizer-password' );

		wp_set_current_user( $this->factory()->user->create( [ 'role' => 'author' ] ) );

		$event_id = tribe_events()->set_args(
			[
				'title'      => 'Carrier',
				'status'     => 'draft',
				'start_date' => '2030-01-01 10:00:00',
				'end_date'   => '2030-01-01 12:00:00',
				'organizer'  => $organizer_id,
			]
		)->create()->ID;

		$this->assertNotContains( $organizer_id, $this->linked_organizers( $event_id ) );
	}

	/**
	 * @test
	 */
	public function it_should_link_a_password_protected_organizer_through_the_repository_without_a_user() {
		$organizer_id = $this->create_organizer( 'organizer-password' );

		wp_set_current_user( 0 );

		$event_id = tribe_events()->set_args(
			[
				'title'      => 'Carrier',
				'status'     => 'draft',
				'start_date' => '2030-01-01 10:00:00',
				'end_date'   => '2030-01-01 12:00:00',
				'organizer'  => $organizer_id,
			]
		)->create()->ID;

		$this->assertContains( $organizer_id, $this->linked_organizers( $event_id ) );
	}
}
