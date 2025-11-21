<?php
/**
 *
 *
 * @since TBD
 */

declare( strict_types=1 );

namespace TEC\Tests\Events\Classy;

use TEC\Common\StellarWP\DB\DB;
use TEC\Common\Tests\Provider\Controller_Test_Case;
use TEC\Events\Classy\Meta;
use Tribe__Events__Main as TEC;
use WP_REST_Request;

/**
 * Class Meta_Test
 *
 * @since TBD
 */
class Meta_Test extends Controller_Test_Case {
	protected $controller_class = Meta::class;

	protected function create_request( array $data = [] ) {
		$request = new WP_REST_Request();
		if ( ! empty( $data ) ) {
			$request->set_default_params( $data );
		}

		return $request;
	}

	public function test_on_rest_insert_event_does_not_add_meta_to_non_event_post(): void {
		$post = static::factory()->post->create_and_get();
		// Sanity check.
		$this->assertEquals( '', get_post_meta( $post->ID, '_EventStartDateUTC', true ) );
		$this->assertEquals( '', get_post_meta( $post->ID, '_EventEndDateUTC', true ) );

		$this->make_controller()->register();
		apply_filters( 'rest_after_insert_' . TEC::POSTTYPE, $post, $this->create_request() );

		$this->assertEquals( '', get_post_meta( $post->ID, '_EventStartDateUTC', true ) );
		$this->assertEquals( '', get_post_meta( $post->ID, '_EventEndDateUTC', true ) );
	}

	public function test_on_rest_insert_event_adds_meta_to_event_post(): void {
		// Create the post with a post type that will not trigger any event-related filter or action.
		$pseudo_event = static::factory()->post->create_and_get( [ 'post_type' => 'pseudo_event' ] );

		// Update the date post meta as the REST API save would.
		update_post_meta( $pseudo_event->ID, '_EventStartDate', '2020-01-01 10:00:00' );
		update_post_meta( $pseudo_event->ID, '_EventEndDate', '2020-01-01 13:00:00' );
		update_post_meta( $pseudo_event->ID, '_EventTimezone', 'Europe/Paris' );

		// Update the event post type with a direct db call to avoid triggering any event-related filter or action.
		global $wpdb;
		DB::query( DB::prepare( 'UPDATE %i SET post_type = %s WHERE ID = %d', $wpdb->posts, TEC::POSTTYPE, $pseudo_event->ID ) );

		// Re-fetch the event.
		clean_post_cache( $pseudo_event->ID );
		$event = get_post( $pseudo_event->ID );

		// Sanity check.
		$this->assertEquals( '', get_post_meta( $event->ID, '_EventStartDateUTC', true ) );
		$this->assertEquals( '', get_post_meta( $event->ID, '_EventEndDateUTC', true ) );

		$this->make_controller()->register();

		// Trigger the filter that will add the UTC dates.
		apply_filters(
			'rest_after_insert_' . TEC::POSTTYPE,
			$event,
			$this->create_request(
				[
					'meta' => [
						'_EventStartDate' => '2020-01-01 10:00:00',
						'_EventEndDate'   => '2020-01-01 13:00:00',
					],
				]
			)
		);

		// Sanity check.
		$this->assertEquals( '2020-01-01 09:00:00', get_post_meta( $event->ID, '_EventStartDateUTC', true ) );
		$this->assertEquals( '2020-01-01 12:00:00', get_post_meta( $event->ID, '_EventEndDateUTC', true ) );
	}

	public function test_on_rest_insert_events_does_not_change_meta_if_present(): void {
		// Create the post with a post type that will not trigger any event-related filter or action.
		$pseudo_event = static::factory()->post->create_and_get( [ 'post_type' => 'pseudo_event' ] );

		// Update the date post meta as the REST API save would.
		update_post_meta( $pseudo_event->ID, '_EventStartDate', '2020-01-01 10:00:00' );
		update_post_meta( $pseudo_event->ID, '_EventEndDate', '2020-01-01 13:00:00' );
		update_post_meta( $pseudo_event->ID, '_EventTimezone', 'Europe/Paris' );

		// Set the UTC start and end date and time to very distinguishable strings.
		update_post_meta( $pseudo_event->ID, '_EventStartDateUTC', 'You cannot pass!' );
		update_post_meta( $pseudo_event->ID, '_EventEndDateUTC', 'not a UTC time' );

		// Update the event post type with a direct db call to avoid triggering any event-related filter or action.
		global $wpdb;
		DB::query( DB::prepare( 'UPDATE %i SET post_type = %s WHERE ID = %d', $wpdb->posts, TEC::POSTTYPE, $pseudo_event->ID ) );

		// Re-fetch the event.
		clean_post_cache( $pseudo_event->ID );
		$event = get_post( $pseudo_event->ID );

		$this->make_controller()->register();
		apply_filters( 'rest_after_insert_' . TEC::POSTTYPE, $event, $this->create_request() );

		// Sanity check.
		$this->assertEquals( 'You cannot pass!', get_post_meta( $event->ID, '_EventStartDateUTC', true ) );
		$this->assertEquals( 'not a UTC time', get_post_meta( $event->ID, '_EventEndDateUTC', true ) );
	}

	/**
	 * @test
	 * @covers \TEC\Events\Classy\Meta::add_utc_dates
	 */
	public function test_add_utc_dates_should_update_properly_for_named_timezones() {
		$event_id = tribe_events()->set_args(
			[
				'title'      => 'Basic Event',
				'status'     => 'publish',
				'start_date' => '2020-01-01 00:00:00',
				'duration'   => 2 * HOUR_IN_SECONDS,
			]
		)->create()->ID;

		$this->make_controller()->register();

		apply_filters(
			'rest_after_insert_' . TEC::POSTTYPE,
			get_post( $event_id ),
			$this->create_request(
				[
					'meta' => [
						'_EventStartDate' => '2020-01-01 00:00:00',
						'_EventTimezone'  => 'Europe/Paris',
					],
				]
			)
		);

		$this->assertEquals( '2020-01-01 00:00:00', get_post_meta( $event_id, '_EventStartDate', true ) );
		$this->assertEquals( '2020-01-01 02:00:00', get_post_meta( $event_id, '_EventEndDate', true ) );

		// default timezone was UTC which now changed to Europe/Paris which is UTC+1. The new UTC dates should be behind now.
		$this->assertEquals( '2019-12-31 23:00:00', get_post_meta( $event_id, '_EventStartDateUTC', true ) );
		$this->assertEquals( '2020-01-01 01:00:00', get_post_meta( $event_id, '_EventEndDateUTC', true ) );
	}

	/**
	 * @test
	 * @covers \TEC\Events\Classy\Meta::add_utc_dates
	 */
	public function test_add_utc_dates_should_update_properly_for_manual_offset_timezones() {
		$event_id = tribe_events()->set_args(
			[
				'title'      => 'Basic Event',
				'status'     => 'publish',
				'start_date' => '2020-01-01 00:00:00',
				'duration'   => 2 * HOUR_IN_SECONDS,
			]
		)->create()->ID;

		$this->make_controller()->register();

		apply_filters(
			'rest_after_insert_' . TEC::POSTTYPE,
			get_post( $event_id ),
			$this->create_request(
				[
					'meta' => [
						'_EventStartDate' => '2020-01-01 00:00:00',
						'_EventTimezone'  => 'UTC+6',
					],
				]
			)
		);

		$this->assertEquals( '2020-01-01 00:00:00', get_post_meta( $event_id, '_EventStartDate', true ) );
		$this->assertEquals( '2020-01-01 02:00:00', get_post_meta( $event_id, '_EventEndDate', true ) );

		// default timezone was UTC which now changed to UTC+6. The new UTC dates should be behind now.
		$this->assertEquals( '2019-12-31 18:00:00', get_post_meta( $event_id, '_EventStartDateUTC', true ) );
		$this->assertEquals( '2019-12-31 20:00:00', get_post_meta( $event_id, '_EventEndDateUTC', true ) );
	}

	/**
	 * @test
	 * @covers \TEC\Events\Classy\Meta::add_utc_dates
	 */
	public function test_add_utc_dates_should_not_update_for_invalid_timezones() {
		$event_id = tribe_events()->set_args(
			[
				'title'      => 'Basic Event',
				'status'     => 'publish',
				'start_date' => '2020-01-01 00:00:00',
				'duration'   => 2 * HOUR_IN_SECONDS,
			]
		)->create()->ID;

		$this->make_controller()->register();

		apply_filters(
			'rest_after_insert_' . TEC::POSTTYPE,
			get_post( $event_id ),
			$this->create_request(
				[
					'meta' => [
						'_EventStartDate' => '2020-01-01 00:00:00',
						'_EventTimezone'  => 'Invalid/Timezone',
					],
				]
			)
		);

		$this->assertEquals( '2020-01-01 00:00:00', get_post_meta( $event_id, '_EventStartDate', true ) );
		$this->assertEquals( '2020-01-01 02:00:00', get_post_meta( $event_id, '_EventEndDate', true ) );

		// default timezone was UTC which now changed to Invalid/Timezone. The new UTC dates should be the same as the original.
		$this->assertEquals( '2020-01-01 00:00:00', get_post_meta( $event_id, '_EventStartDateUTC', true ) );
		$this->assertEquals( '2020-01-01 02:00:00', get_post_meta( $event_id, '_EventEndDateUTC', true ) );

		// The timezone should be the same as the original.
		$this->assertEquals( 'UTC', get_post_meta( $event_id, '_EventTimezone', true ) );
	}

	/**
	 * @test
	 * @covers \TEC\Events\Classy\Meta_Methods::sanitize_meta_value
	 */
	public function test_sanitize_meta_value_returns_value_unchanged_for_non_post_type(): void {
		$controller = $this->make_controller();

		$value = 'test value';
		$result = $controller->sanitize_meta_value( $value, '_EventStartDate', 'user', 'subscriber' );

		$this->assertEquals( $value, $result );
	}

	/**
	 * @test
	 * @covers \TEC\Events\Classy\Meta_Methods::sanitize_meta_value
	 */
	public function test_sanitize_meta_value_returns_value_unchanged_for_unsupported_post_type(): void {
		$controller = $this->make_controller();

		$value = 'test value';
		$result = $controller->sanitize_meta_value( $value, '_EventStartDate', 'post', 'post' );

		$this->assertEquals( $value, $result );
	}

	/**
	 * @test
	 * @covers \TEC\Events\Classy\Meta_Methods::sanitize_meta_value
	 */
	public function test_sanitize_meta_value_returns_value_unchanged_for_unknown_meta_key(): void {
		$controller = $this->make_controller();

		$value = 'test value';
		$result = $controller->sanitize_meta_value( $value, '_UnknownMetaKey', 'post', TEC::POSTTYPE );

		$this->assertEquals( $value, $result );
	}

	/**
	 * @test
	 * @covers \TEC\Events\Classy\Meta_Methods::sanitize_meta_value
	 */
	public function test_sanitize_meta_value_sanitizes_boolean_fields(): void {
		$controller = $this->make_controller();

		// Test _EventAllDay which is a boolean field.
		$this->assertTrue( $controller->sanitize_meta_value( true, '_EventAllDay', 'post', TEC::POSTTYPE ) );
		$this->assertTrue( $controller->sanitize_meta_value( 'true', '_EventAllDay', 'post', TEC::POSTTYPE ) );
		$this->assertTrue( $controller->sanitize_meta_value( '1', '_EventAllDay', 'post', TEC::POSTTYPE ) );
		$this->assertTrue( $controller->sanitize_meta_value( 1, '_EventAllDay', 'post', TEC::POSTTYPE ) );

		$this->assertFalse( $controller->sanitize_meta_value( false, '_EventAllDay', 'post', TEC::POSTTYPE ) );
		$this->assertFalse( $controller->sanitize_meta_value( 'false', '_EventAllDay', 'post', TEC::POSTTYPE ) );
		$this->assertFalse( $controller->sanitize_meta_value( '0', '_EventAllDay', 'post', TEC::POSTTYPE ) );
		$this->assertFalse( $controller->sanitize_meta_value( 0, '_EventAllDay', 'post', TEC::POSTTYPE ) );
		$this->assertFalse( $controller->sanitize_meta_value( '', '_EventAllDay', 'post', TEC::POSTTYPE ) );
	}

	/**
	 * @test
	 * @covers \TEC\Events\Classy\Meta_Methods::sanitize_meta_value
	 */
	public function test_sanitize_meta_value_sanitizes_integer_fields(): void {
		$controller = $this->make_controller();

		// Test _EventOrganizerID which is an integer field.
		$this->assertEquals( 123, $controller->sanitize_meta_value( 123, '_EventOrganizerID', 'post', TEC::POSTTYPE ) );
		$this->assertEquals( 123, $controller->sanitize_meta_value( '123', '_EventOrganizerID', 'post', TEC::POSTTYPE ) );
		$this->assertEquals( 123, $controller->sanitize_meta_value( -123, '_EventOrganizerID', 'post', TEC::POSTTYPE ) );
		$this->assertEquals( 0, $controller->sanitize_meta_value( 'not-a-number', '_EventOrganizerID', 'post', TEC::POSTTYPE ) );
	}

	/**
	 * @test
	 * @covers \TEC\Events\Classy\Meta_Methods::sanitize_meta_value
	 */
	public function test_sanitize_meta_value_sanitizes_text_fields(): void {
		/** @var Meta $controller */
		$controller = $this->make_controller();

		// Test _EventCost which is a text field (default type).
		$input = '<script>alert("XSS")</script>Some text';
		$expected = 'Some text';
		$result = $controller->sanitize_meta_value( $input, '_EventCost', 'post', TEC::POSTTYPE );

		$this->assertEquals( $expected, $result );

		// Test that regular text is preserved.
		$regular_text = 'Regular event cost: $50';
		$this->assertEquals( $regular_text, $controller->sanitize_meta_value( $regular_text, '_EventCost', 'post', TEC::POSTTYPE ) );
	}

	/**
	 * @test
	 * @covers \TEC\Events\Classy\Meta_Methods::sanitize_meta_value
	 */
	public function test_sanitize_meta_value_sanitizes_url_fields(): void {
		/** @var Meta $controller */
		$controller = $this->make_controller();

		// Test _EventURL which should be treated as URL.
		$valid_url = 'https://example.com/event';
		$this->assertEquals( $valid_url, $controller->sanitize_meta_value( $valid_url, '_EventURL', 'post', TEC::POSTTYPE ) );

		// Test that javascript URLs are sanitized.
		$js_url = '<script>javascript:alert("XSS")</script>';
		$this->assertEquals( '', $controller->sanitize_meta_value( $js_url, '_EventURL', 'post', TEC::POSTTYPE ) );

		// Test URL with special characters.
		$url_with_params = 'https://example.com/event?id=123&name=test';
		$this->assertEquals( $url_with_params, $controller->sanitize_meta_value( $url_with_params, '_EventURL', 'post', TEC::POSTTYPE ) );
	}

	/**
	 * @test
	 * @covers \TEC\Events\Classy\Meta_Methods::sanitize_meta_value
	 */
	public function test_sanitize_meta_value_sanitizes_separator_fields(): void {
		$controller = $this->make_controller();

		// Test _EventDateTimeSeparator which is a separator field.
		$input = '<script>alert("XSS")</script> @ ';
		$result = $controller->sanitize_meta_value( $input, '_EventDateTimeSeparator', 'post', TEC::POSTTYPE );

		// The separator field uses tec_sanitize_string which should escape HTML.
		$this->assertStringNotContainsString( '<script>', $result );
		$this->assertStringContainsString( '@', $result );

		// Test normal separator.
		$normal_separator = ' @ ';
		$this->assertEquals( $normal_separator, $controller->sanitize_meta_value( $normal_separator, '_EventDateTimeSeparator', 'post', TEC::POSTTYPE ) );
	}

	/**
	 * @test
	 * @covers \TEC\Events\Classy\Meta_Methods::user_can_edit_meta
	 */
	public function test_user_can_edit_meta_returns_original_for_unknown_meta_key(): void {
		$controller = $this->make_controller();

		// Create a user with edit capabilities.
		$user_id = static::factory()->user->create( [ 'role' => 'editor' ] );
		$post_id = static::factory()->post->create( [ 'post_type' => TEC::POSTTYPE ] );

		// Test with unknown meta key.
		$allowed = true;
		$result = $controller->user_can_edit_meta( $allowed, '_UnknownMetaKey', $post_id, $user_id );
		$this->assertTrue( $result );

		$allowed = false;
		$result = $controller->user_can_edit_meta( $allowed, '_UnknownMetaKey', $post_id, $user_id );
		$this->assertFalse( $result );
	}

	/**
	 * @test
	 * @covers \TEC\Events\Classy\Meta_Methods::user_can_edit_meta
	 */
	public function test_user_can_edit_meta_returns_false_for_invalid_post_type(): void {
		$controller = $this->make_controller();

		// Create a user with edit capabilities.
		$user_id = static::factory()->user->create( [ 'role' => 'editor' ] );

		// Test with invalid post ID.
		$result = $controller->user_can_edit_meta( false, '_EventStartDate', 999999, $user_id );
		$this->assertFalse( $result );
	}

	/**
	 * @test
	 * @covers \TEC\Events\Classy\Meta_Methods::user_can_edit_meta
	 */
	public function test_user_can_edit_meta_checks_user_capabilities(): void {
		$controller = $this->make_controller();
		$this->make_controller()->register();

		// Create an event.
		$event_id = tribe_events()->set_args(
			[
				'title'      => 'Test Event',
				'status'     => 'publish',
				'start_date' => '2020-01-01 00:00:00',
				'duration'   => 2 * HOUR_IN_SECONDS,
			]
		)->create()->ID;

		// Create users with different roles.
		$admin_id = static::factory()->user->create( [ 'role' => 'administrator' ] );
		$editor_id = static::factory()->user->create( [ 'role' => 'editor' ] );
		$author_id = static::factory()->user->create( [ 'role' => 'author' ] );
		$contributor_id = static::factory()->user->create( [ 'role' => 'contributor' ] );
		$subscriber_id = static::factory()->user->create( [ 'role' => 'subscriber' ] );

		// Test that admin can edit meta.
		$this->assertTrue( $controller->user_can_edit_meta( true, '_EventStartDate', $event_id, $admin_id ) );

		// Test that editor can edit meta.
		$this->assertTrue( $controller->user_can_edit_meta( true, '_EventStartDate', $event_id, $editor_id ) );

		// Test that author can edit their own event.
		wp_update_post( [ 'ID' => $event_id, 'post_author' => $author_id ] );
		$this->assertTrue( $controller->user_can_edit_meta( true, '_EventStartDate', $event_id, $author_id ) );

		// Test that contributor cannot edit published events.
		$this->assertFalse( $controller->user_can_edit_meta( true, '_EventStartDate', $event_id, $contributor_id ) );

		// Test that subscriber cannot edit meta.
		$this->assertFalse( $controller->user_can_edit_meta( true, '_EventStartDate', $event_id, $subscriber_id ) );
	}

	/**
	 * @test
	 * @covers \TEC\Events\Classy\Meta_Methods::user_can_edit_meta
	 */
	public function test_user_can_edit_meta_respects_different_meta_keys(): void {
		$controller = $this->make_controller();
		$this->make_controller()->register();

		// Create an event.
		$event_id = tribe_events()->set_args(
			[
				'title'      => 'Test Event',
				'status'     => 'publish',
				'start_date' => '2020-01-01 00:00:00',
				'duration'   => 2 * HOUR_IN_SECONDS,
			]
		)->create()->ID;

		$editor_id = static::factory()->user->create( [ 'role' => 'editor' ] );

		// Test various meta keys that are in the META constant.
		$meta_keys = [
			'_EventStartDate',
			'_EventEndDate',
			'_EventAllDay',
			'_EventCost',
			'_EventURL',
			'_EventTimezone',
			'_VenueAddress',
			'_OrganizerEmail',
		];

		foreach ( $meta_keys as $meta_key ) {
			$this->assertTrue(
				$controller->user_can_edit_meta( true, $meta_key, $event_id, $editor_id ),
				"Editor should be able to edit meta key: {$meta_key}."
			);
		}
	}

	/**
	 * @test
	 * @covers \TEC\Events\Classy\Meta_Methods::user_can_edit_meta
	 */
	public function test_user_can_edit_meta_with_draft_post(): void {
		$controller = $this->make_controller();
		$this->make_controller()->register();

		// Create a draft event.
		$event_id = tribe_events()->set_args(
			[
				'title'      => 'Draft Event',
				'status'     => 'draft',
				'start_date' => '2020-01-01 00:00:00',
				'duration'   => 2 * HOUR_IN_SECONDS,
			]
		)->create()->ID;

		// Create a contributor user.
		$contributor_id = static::factory()->user->create( [ 'role' => 'contributor' ] );

		// Make the contributor the author of the draft.
		wp_update_post( [ 'ID' => $event_id, 'post_author' => $contributor_id ] );

		// Contributors can edit their own drafts.
		$this->assertTrue( $controller->user_can_edit_meta( true, '_EventStartDate', $event_id, $contributor_id ) );

		// But not someone else's draft.
		$other_contributor_id = static::factory()->user->create( [ 'role' => 'contributor' ] );
		$this->assertFalse( $controller->user_can_edit_meta( true, '_EventStartDate', $event_id, $other_contributor_id ) );
	}
}
