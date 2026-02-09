<?php

use Tribe\Tests\Traits\With_Uopz;
use Tribe__Events__Main as TEC;

class Tribe__Events__MainTest extends \Codeception\TestCase\WPTestCase {
	use With_Uopz;

	/**
	 * @after
	 */
	public function reregister_taxonomies(): void {
		TEC::instance()->register_taxonomy();
	}

	public function test_post_class_will_work_when_cat_tax_unregistered(): void {
		global $post;
		$post = tribe_events()->set_args( [
			'title'      => 'Test Event',
			'status'     => 'publish',
			'start_date' => '2018-01-01 08:00:00',
			'end_date'   => '2018-01-01 10:00:00',
		] )->create();
		unregister_taxonomy( TEC::TAXONOMY );

		$main       = TEC::instance();
		$post_class = $main->post_class( [] );

		$this->assertEquals( [], $post_class );
	}

	public function test_post_class_will_work_when_some_terms_are_not_valid(): void {
		global $post;
		$post = tribe_events()->set_args( [
			'title'      => 'Test Event',
			'status'     => 'publish',
			'start_date' => '2018-01-01 08:00:00',
			'end_date'   => '2018-01-01 10:00:00',
		] )->create();
		$this->set_fn_return(
			'get_the_terms',
			[
				static::factory()->term->create_and_get( [
					'taxonomy' => TEC::TAXONOMY,
					'name'     => 'Test Category',
				] ),
				new WP_Error( 'invalid_term', 'Invalid term.' ),
			]
		);

		$main       = TEC::instance();
		$post_class = $main->post_class( [] );

		$this->assertEquals( [ 'cat_test-category' ], $post_class );
	}

	public function getLink_dataProvider(): \Generator {
		yield 'home_url return empty string' => [
			function () {
				$this->set_fn_return( 'home_url', '' );

				return [ 'home', '/events/' ];
			},
		];

		yield 'home_url returns relative path' => [
			function () {
				$this->set_fn_return( 'home_url', '/' );

				return [ 'home', '/events/' ];
			},
		];

		yield 'home_url returns URL w/o trailing slash' => [
			function () {
				$this->set_fn_return( 'home_url', 'http://example.com' );

				return [ 'home', 'http://example.com/events/' ];
			},
		];

		yield 'home_url returns URL w/ trailing slash' => [
			function () {
				$this->set_fn_return( 'home_url', 'http://example.com/' );

				return [ 'home', 'http://example.com/events/' ];
			},
		];
	}

	/**
	 * @dataProvider getLink_dataProvider
	 */
	public function test_getLink( Closure $fixture ): void {
		[ $type, $expected ] = $fixture();
		$actual = TEC::instance()->getLink( $type );
		$this->assertEquals( $expected, $actual );
	}

	public function hidden_boxes_data_provider() {
		return [
			'empty array'  => [ [], true ],
			'int'          => [ 321, false ],
			'null'         => [ null, false ],
			'empty string' => [ '', false ],
			'object'       => [ new stdClass(), false ],
			'true'         => [ true, false ],
			'false'        => [ false, false ]
		];
	}

	/**
	 * @dataProvider hidden_boxes_data_provider
	 * @test
	 */
	public function should_handle_invalid_navmenu_options( $current_hidden_boxes, $should_update ) {
		// Setup our option for testing.
		$user_data = array(
			'user_login' => 'new_user',
			'user_pass'  => 'password123',
			'user_email' => 'new_user@example.com',
			'role'       => 'subscriber' // set the user role
		);

		// Insert the new user into the database
		$user_id = wp_insert_user( $user_data );
		set_current_user( $user_id );
		$user_id = get_current_user_id();
		update_user_option( $user_id, 'metaboxhidden_nav-menus', $current_hidden_boxes, true );
		$current_screen = WP_Screen::get( 'nav-menus' );
		set_current_screen( $current_screen );
		$did_update = false;
		add_filter( "update_user_metadata", function ( $a, $object_id, $meta_key, $meta_value, $prev_value ) use ( &$did_update ) {
			if ( $meta_key === 'metaboxhidden_nav-menus' ) {
				$did_update = true;
			}

			return $a;
		}, 10, 5 );

		// Should gracefully handle invalid options.
		TEC::instance()->setInitialMenuMetaBoxes();
		$this->assertEquals( $should_update, $did_update, "We expected a different outcome from this value being updated." );

		// Clear up state.
		delete_user_option( $user_id, 'metaboxhidden_nav-menus', true );
	}

	/**
	 * Simulates the broken state where WordPress create_initial_taxonomies() has
	 * re-registered post_tag with only 'post' in object_type (e.g. on change_locale).
	 * This was happening when the language was changed to fr_FR in the block editor. [TEC-4996]
	 */
	private function simulate_post_tag_reset_to_post_only(): void {
		global $wp_taxonomies;

		$this->assertArrayHasKey( 'post_tag', $wp_taxonomies, 'post_tag taxonomy should exist to simulate reset.' );
		$wp_taxonomies['post_tag']->object_type = [ 'post' ];
	}

	/**
	 * @test
	 */
	public function test_restore_event_tag_taxonomy_on_locale_change_restores_post_tag_for_tribe_events(): void {
		global $wp_taxonomies;

		$this->assertContains( 'post_tag', get_object_taxonomies( TEC::POSTTYPE ), 'Precondition: tribe_events should have post_tag before simulation.' );

		$this->simulate_post_tag_reset_to_post_only();

		$this->assertNotContains( 'post_tag', get_object_taxonomies( TEC::POSTTYPE ), 'After reset simulation, tribe_events should not have post_tag.' );
		$this->assertEquals( [ 'post' ], $wp_taxonomies['post_tag']->object_type, 'After reset simulation, post_tag object_type should be only post.' );

		TEC::instance()->restore_event_tag_taxonomy_on_locale_change();

		$this->assertContains( 'post_tag', get_object_taxonomies( TEC::POSTTYPE ), 'After restore, tribe_events should have post_tag again.' );
		$this->assertContains( TEC::POSTTYPE, $wp_taxonomies['post_tag']->object_type, 'After restore, post_tag object_type should include tribe_events.' );
	}

	/**
	 * @test
	 */
	public function test_restore_event_tag_taxonomy_on_locale_change_restores_post_tag_for_tec_calendar_embed_when_registered(): void {
		global $wp_taxonomies;

		$this->simulate_post_tag_reset_to_post_only();

		TEC::instance()->restore_event_tag_taxonomy_on_locale_change();

		$this->assertContains( TEC::POSTTYPE, $wp_taxonomies['post_tag']->object_type, 'After restore, post_tag object_type should include tribe_events.' );

		// tec_calendar_embed is only registered when Calendar Embeds is loaded; if it exists, it should be restored.
		if ( post_type_exists( 'tec_calendar_embed' ) ) {
			$this->assertContains( 'tec_calendar_embed', $wp_taxonomies['post_tag']->object_type, 'When tec_calendar_embed post type exists, it should be restored to post_tag object_type.' );
		}
	}

	/**
	 * @test
	 */
	public function test_restore_event_tag_taxonomy_on_locale_change_bails_early_when_post_tag_does_not_exist(): void {
		global $wp_taxonomies;

		$this->simulate_post_tag_reset_to_post_only();

		$saved_post_tag = $wp_taxonomies['post_tag'];
		unset( $wp_taxonomies['post_tag'] );

		$this->assertFalse( taxonomy_exists( 'post_tag' ), 'Precondition: post_tag should appear missing after unset.' );

		// Method should return without error and without modifying anything.
		TEC::instance()->restore_event_tag_taxonomy_on_locale_change();

		// Restore so later tests and teardown are not broken.
		$wp_taxonomies['post_tag'] = $saved_post_tag;

		$this->assertTrue( taxonomy_exists( 'post_tag' ), 'post_tag should exist again after restore in test.' );
	}
}
