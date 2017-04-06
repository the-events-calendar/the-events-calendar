<?php

/**
 * @group capabilities
 */
class Tribe__Events__Capabilities_Test extends \Codeception\TestCase\WPTestCase {

	public function contributor_or_higher() {
		return array(
			array( 'subscriber', false ),
			array( 'contributor', true ),
			array( 'author', true ),
			array( 'editor', true ),
			array( 'administrator', true ),
		);
	}

	public function author_or_higher() {
		return array(
			array( 'subscriber', false ),
			array( 'contributor', false ),
			array( 'author', true ),
			array( 'editor', true ),
			array( 'administrator', true ),
		);
	}

	public function editor_or_higher() {
		return array(
			array( 'subscriber', false ),
			array( 'contributor', false ),
			array( 'author', false ),
			array( 'editor', true ),
			array( 'administrator', true ),
		);
	}

	/**
	 * @param string $role
	 * @param bool $can
	 *
	 * @dataProvider contributor_or_higher
	 */
	public function test_role_can_create_events( $role, $can ) {
		/** @var WP_User $user */
		$user = $this->factory->user->create_and_get( array(
			'role' => $role,
		) );
		$this->assertEquals( $can, $user->has_cap( 'edit_tribe_events' ) );
	}


	/**
	 * @param string $role
	 * @param bool $can
	 *
	 * @dataProvider contributor_or_higher
	 */
	public function test_role_can_edit_own_draft_events( $role, $can ) {
		/** @var WP_User $user */
		$user = $this->factory->user->create_and_get( array(
			'role' => $role,
		) );
		$event_id = $this->factory->post->create( array(
			'post_type' => Tribe__Events__Main::POSTTYPE,
			'post_status' => 'draft',
			'post_author' => $user->ID,
		) );
		$this->assertEquals( $can, $user->has_cap( 'edit_tribe_event', $event_id ) );
	}

	/**
	 * @param string $role
	 * @param bool $can
	 *
	 * @dataProvider author_or_higher
	 */
	public function test_role_edit_own_published_events( $role, $can ) {
		/** @var WP_User $user */
		$user = $this->factory->user->create_and_get( array(
			'role' => $role,
		) );
		$event_id = $this->factory->post->create( array(
			'post_type' => Tribe__Events__Main::POSTTYPE,
			'post_status' => 'publish',
			'post_author' => $user->ID,
		) );
		$this->assertEquals( $can, $user->has_cap( 'edit_tribe_event', $event_id ) );
	}

	/**
	 * @param string $role
	 * @param bool $can
	 *
	 * @dataProvider editor_or_higher
	 */
	public function test_role_can_edit_others_draft_events( $role, $can ) {
		/** @var WP_User $user */
		$user = $this->factory->user->create_and_get( array(
			'role' => $role,
		) );
		$another_user_id = $this->factory->user->create();
		$event_id = $this->factory->post->create( array(
			'post_type' => Tribe__Events__Main::POSTTYPE,
			'post_status' => 'draft',
			'post_author' => $another_user_id,
		) );
		$this->assertEquals( $can, $user->has_cap( 'edit_tribe_event', $event_id ) );
	}

	/**
	 * @param string $role
	 * @param bool $can
	 *
	 * @dataProvider editor_or_higher
	 */
	public function test_role_can_edit_others_published_events( $role, $can ) {
		/** @var WP_User $user */
		$user = $this->factory->user->create_and_get( array(
			'role' => $role,
		) );
		$another_user_id = $this->factory->user->create();
		$event_id = $this->factory->post->create( array(
			'post_type' => Tribe__Events__Main::POSTTYPE,
			'post_status' => 'publish',
			'post_author' => $another_user_id,
		) );
		$this->assertEquals( $can, $user->has_cap( 'edit_tribe_event', $event_id ) );
	}

	public function test_remove_caps() {
		/** @var WP_User $user */
		$user = $this->factory->user->create_and_get( array(
			'role' => 'editor',
		) );
		$caps = new Tribe__Events__Capabilities();

		$this->assertTrue( $user->has_cap( 'edit_tribe_events' ) ); // baseline

		$caps->remove_post_type_caps( Tribe__Events__Main::POSTTYPE, 'editor' );
		$user = new WP_User( $user ); // to reinit caps
		$this->assertFalse( $user->has_cap( 'edit_tribe_events' ) );

		// now put everything back where we found it
		$caps->register_post_type_caps( Tribe__Events__Main::POSTTYPE, 'editor' );
		$user = new WP_User( $user ); // to reinit caps
		$this->assertTrue( $user->has_cap( 'edit_tribe_events' ) );
	}

	/**
	 * @param string $role
	 * @param bool $can
	 *
	 * @dataProvider contributor_or_higher
	 */
	public function tests_role_can_edit_venues( $role, $can ) {
		$caps = new Tribe__Events__Capabilities();
		$caps->register_post_type_caps( Tribe__Events__Main::VENUE_POST_TYPE, $role );

		/** @var WP_User $user */
		$user = $this->factory->user->create_and_get( array(
			'role' => $role,
		) );

		$this->assertEquals( $can, $user->has_cap( 'edit_tribe_venues' ), "Check if $role can edit venue"  );
	}

	/**
	 * @param string $role
	 * @param bool $can
	 *
	 * @dataProvider contributor_or_higher
	 */
	public function tests_role_can_edit_organizers( $role, $can ) {
		/** @var WP_User $user */
		$user = $this->factory->user->create_and_get( array(
			'role' => $role,
		) );

		$this->assertEquals( $can, $user->has_cap( 'edit_tribe_organizers' ), "Check if $role can edit organizer" );
	}
}