<?php

namespace Tribe\Events\Editor;

use Codeception\TestCase\WPTestCase;
use WP_REST_Request;

class Meta_Linked_Posts_Test extends WPTestCase {
	public function setUp() {
		parent::setUp();

		// The suite bootstrap registers the editor provider after `init`, so the meta registration has not run.
		$meta = tribe( 'events.editor.meta' );
		if ( ! has_action( 'rest_after_insert_' . \Tribe__Events__Main::POSTTYPE, [ $meta, 'remove_invalid_linked_posts' ] ) ) {
			$meta->register();
		}
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
	 * Sends a block editor save of an Event's organizers as a new user with the role.
	 *
	 * @param array<int> $organizer_ids The organizers the save submits.
	 *
	 * @return array<int> The organizers linked after the save.
	 */
	private function save_organizers_as( string $role, array $organizer_ids, array $already_linked = [] ): array {
		$user_id  = $this->factory()->user->create( [ 'role' => $role ] );
		$event_id = $this->factory()->post->create(
			[
				'post_type'   => \Tribe__Events__Main::POSTTYPE,
				'post_status' => 'draft',
				'post_author' => $user_id,
			]
		);

		foreach ( $already_linked as $linked_id ) {
			add_post_meta( $event_id, '_EventOrganizerID', $linked_id );
		}

		wp_set_current_user( $user_id );

		$request = new WP_REST_Request( 'POST', '/wp/v2/' . \Tribe__Events__Main::POSTTYPE . '/' . $event_id );
		$request->set_header( 'Content-Type', 'application/json' );
		$request->set_body( wp_json_encode( [ 'meta' => [ '_EventOrganizerID' => $organizer_ids ] ] ) );
		$response = rest_do_request( $request );

		$this->assertSame( 200, $response->get_status(), wp_json_encode( $response->get_data() ) );

		return array_map( 'absint', get_post_meta( $event_id, '_EventOrganizerID', false ) );
	}

	/**
	 * @test
	 */
	public function it_should_not_link_a_password_protected_organizer_an_author_cannot_read() {
		$protected_id = $this->create_organizer( 'organizer-password' );
		$public_id    = $this->create_organizer();

		$linked = $this->save_organizers_as( 'author', [ $protected_id, $public_id ] );

		$this->assertNotContains( $protected_id, $linked );
		$this->assertContains( $public_id, $linked );
	}

	/**
	 * @test
	 */
	public function it_should_link_a_password_protected_organizer_an_editor_can_edit() {
		$protected_id = $this->create_organizer( 'organizer-password' );

		$this->assertContains( $protected_id, $this->save_organizers_as( 'editor', [ $protected_id ] ) );
	}

	/**
	 * @test
	 */
	public function it_should_keep_a_password_protected_organizer_the_event_already_links() {
		$protected_id = $this->create_organizer( 'organizer-password' );
		$public_id    = $this->create_organizer();

		$linked = $this->save_organizers_as( 'author', [ $protected_id, $public_id ], [ $protected_id ] );

		$this->assertContains( $protected_id, $linked );
		$this->assertContains( $public_id, $linked );
	}
}
