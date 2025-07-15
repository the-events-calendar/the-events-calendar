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

	public function possible_cost_values() {
		return [
			''              => [ '' ],
			'100'           => [ '100' ],
			'100.00'        => [ '100.00' ],
			'10.00 - 50.00' => [ '10.00 - 50.00' ],
			'10-30'         => [ '10-30' ],
		];
	}

	/**
	 * @test
	 * @covers \TEC\Events\Classy\Meta::update_cost
	 * @dataProvider possible_cost_values
	 */
	public function test_update_cost_should_update_the_cost_meta( $cost ) {
		$event_id = tribe_events()->set_args(
			[
				'title'      => 'Basic Event',
				'status'     => 'publish',
				'start_date' => '2020-01-01 00:00:00',
				'duration'   => 2 * HOUR_IN_SECONDS,
			]
		)->create()->ID;

		$this->make_controller()->register();

		$post = get_post( $event_id );

		do_action(
			'rest_after_insert_' . TEC::POSTTYPE,
			$post,
			$this->create_request(
				[
					'meta' => [
						'_EventCost' => $cost,
					],
				]
			)
		);

		$this->assertEquals( $cost, get_post_meta( $event_id, '_EventCost', true ) );
	}
}
