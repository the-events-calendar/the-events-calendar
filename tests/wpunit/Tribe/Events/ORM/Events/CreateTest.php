<?php

namespace Tribe\Events\ORM\Events;

use Tribe\Events\Test\Factories\Event;
use Tribe\Events\Test\Factories\Organizer;
use Tribe\Events\Test\Factories\Venue;
use Tribe__Events__Main as Main;

class CreateTest extends \Codeception\TestCase\WPTestCase {

	public function setUp() {
		parent::setUp();
		$this->factory()->event     = new Event();
		$this->factory()->venue     = new Venue();
		$this->factory()->organizer = new Organizer();
		// To support taxonomy term creation and assignment.
		wp_set_current_user( $this->factory()->user->create( [ 'role' => 'administrator' ] ) );
	}

	/**
	 * It should allow creating an event providing minimum required fields
	 *
	 * @test
	 */
	public function should_allow_creating_an_event_providing_minimum_required_fields() {
		$args  = [
			'start_date' => '2018-01-01 09:00:00',
			'end_date'   => '2018-01-01 11:00:00',
			'timezone'   => 'Europe/Paris',
			'title'      => 'A test event',
		];
		$event = tribe_events()->set_args( $args )->create();

		$this->assertInstanceOf( \WP_Post::class, $event );
		$this->assertEquals( 'Europe/Paris', get_post_meta( $event->ID, '_EventTimezone', true ) );
		$this->assertEquals( '2018-01-01 09:00:00', get_post_meta( $event->ID, '_EventStartDate', true ) );
		$this->assertEquals( '2018-01-01 11:00:00', get_post_meta( $event->ID, '_EventEndDate', true ) );
		$this->assertEquals( 'A test event', $event->post_title );
		$this->assertEquals( '', $event->post_content );
	}

	/**
	 * It should return false if trying to create event without min requirements
	 *
	 * @test
	 */
	public function should_return_false_if_trying_to_create_event_without_min_requirements() {
		$args  = [
			'title' => 'A test event',
		];
		$event = tribe_events()->set_args( $args )->create();

		$this->assertFalse( $event );
	}

	/**
	 * It should allow seting meta using pretty names
	 *
	 * @test
	 */
	public function should_allow_seting_meta_using_pretty_names() {
		$venue     = $this->factory()->venue->create();
		$organizer = $this->factory()->organizer->create();
		$args      = [
			'start_date' => '2018-01-01 09:00:00',
			'end_date'   => '2018-01-01 11:00:00',
			'timezone'   => 'Europe/Paris',
			'title'      => 'A test event',
			'venue'      => $venue,
			'organizer'  => $organizer,
		];
		$event     = tribe_events()->set_args( $args )->create();

		$this->assertInstanceOf( \WP_Post::class, $event );
		$this->assertEquals( $venue, get_post_meta( $event->ID, '_EventVenueID', true ) );
		$this->assertEquals( [ $organizer ], get_post_meta( $event->ID, '_EventOrganizerID', true ) );
	}

	/**
	 * It should allow assigning an event multiple organizers
	 *
	 * @test
	 */
	public function should_allow_assigning_an_event_multiple_organizers() {
		$organizer_1 = $this->factory()->organizer->create();
		$organizer_2 = $this->factory()->organizer->create();
		$args        = [
			'start_date' => '2018-01-01 09:00:00',
			'end_date'   => '2018-01-01 11:00:00',
			'timezone'   => 'Europe/Paris',
			'title'      => 'A test event',
			'organizer'  => [ $organizer_1, $organizer_2 ],
		];
		$event       = tribe_events()->set_args( $args )->create();

		$this->assertInstanceOf( \WP_Post::class, $event );
		$this->assertEquals( [ $organizer_1, $organizer_2 ], get_post_meta( $event->ID, '_EventOrganizerID', true ) );
	}

	/**
	 * It should allow setting events categories and tags
	 *
	 * @test
	 */
	public function should_allow_setting_events_categories_and_tags() {
		$cat   = $this->factory()->term->create( [ 'taxonomy' => Main::TAXONOMY ] );
		$tag   = $this->factory()->tag->create();
		$args  = [
			'start_date' => '2018-01-01 09:00:00',
			'end_date'   => '2018-01-01 11:00:00',
			'timezone'   => 'Europe/Paris',
			'title'      => 'A test event',
			'category'   => $cat,
			'tag'        => $tag,
		];
		$event = tribe_events()->set_args( $args )->create();

		$this->assertInstanceOf( \WP_Post::class, $event );
		$this->assertEquals( [ $cat ], wp_get_object_terms( $event->ID, Main::TAXONOMY, [ 'fields' => 'ids' ] ) );
		$this->assertEquals( [ $tag ], wp_get_object_terms( $event->ID, 'post_tag', [ 'fields' => 'ids' ] ) );
	}

	/**
	 * It should correctly set an event UTC times
	 *
	 * @test
	 */
	public function should_correctly_set_an_event_utc_times() {
		$args  = [
			'start_date' => '2018-01-01 12:00:00',
			'end_date'   => '2018-01-01 14:00:00',
			'timezone'   => 'America/New_York',
			'title'      => 'A test event',
		];
		$event = tribe_events()->set_args( $args )->create();

		$this->assertEquals( '2018-01-01 17:00:00', get_post_meta( $event->ID, '_EventStartDateUTC', true ) );
		$this->assertEquals( '2018-01-01 19:00:00', get_post_meta( $event->ID, '_EventEndDateUTC', true ) );
		$this->assertEquals( 'EST', get_post_meta( $event->ID, '_EventTimezoneAbbr', true ) );
	}

	/**
	 * It should allow correctly set event all day duration
	 *
	 * @test
	 */
	public function should_allow_correctly_set_event_all_day_duration() {
		$args  = [
			'start_date' => '2018-03-04 09:00:00',
			'end_date'   => '2018-03-06 11:00:00',
			'all_day'    => 'yes',
			'timezone'   => 'Australia/Darwin',
			'title'      => 'A test event',
		];
		$event = tribe_events()->set_args( $args )->create();

		$darwin = new \DateTimeZone( 'Australia/Darwin' );
		$utc    = new \DateTimeZone( 'UTC' );
		$this->assertInstanceOf( \WP_Post::class, $event );
		$this->assertEquals( '2018-03-04 00:00:00', get_post_meta( $event->ID, '_EventStartDate', true ) );
		$this->assertEquals( '2018-03-06 23:59:59', get_post_meta( $event->ID, '_EventEndDate', true ) );
		$expected_utc_start = ( new \DateTime( '2018-03-04 00:00:00', $darwin ) )->setTimezone( $utc )->format( 'Y-m-d H:i:s' );
		$this->assertEquals( $expected_utc_start, get_post_meta( $event->ID, '_EventStartDateUTC', true ) );
		$expected_utc_end = ( new \DateTime( '2018-03-06 23:59:59', $darwin ) )->setTimezone( $utc )->format( 'Y-m-d H:i:s' );
		$this->assertEquals( $expected_utc_end, get_post_meta( $event->ID, '_EventEndDateUTC', true ) );
	}

	/**
	 * It should set the event origin
	 *
	 * @test
	 */
	public function should_set_the_event_origin() {
		$args  = [
			'start_date' => '2018-03-04 09:00:00',
			'end_date'   => '2018-03-06 11:00:00',
			'timezone'   => 'Australia/Darwin',
			'title'      => 'A test event',
		];
		$event = tribe_events()->set_args( $args )->create();

		$this->assertEquals( 'events-calendar', get_post_meta( $event->ID, '_EventOrigin', true ) );
	}

	/**
	 * It should set the default map meta
	 *
	 * @test
	 */
	public function should_set_the_default_map_meta() {
		$args  = [
			'start_date' => '2018-03-04 09:00:00',
			'end_date'   => '2018-03-06 11:00:00',
			'timezone'   => 'Australia/Darwin',
			'title'   => 'A test event',
		];
		$event = tribe_events()->set_args( $args )->create();

		$this->assertEquals( '1', get_post_meta( $event->ID, '_EventShowMap', true ) );
		$this->assertEquals( '1', get_post_meta( $event->ID, '_EventShowMapLink', true ) );
	}
	/**
	 * It should allow setting the the event map meta
	 *
	 * @test
	 */
	public function should_allow_setting_the_the_event_map_meta() {
		$args  = [
			'start_date' => '2018-03-04 09:00:00',
			'end_date'   => '2018-03-06 11:00:00',
			'timezone'   => 'Australia/Darwin',
			'title'   => 'A test event',
			'show_map' => 'yes',
			'show_map_link' => false,
		];
		$event = tribe_events()->set_args( $args )->create();

		$this->assertEquals( '1', get_post_meta( $event->ID, '_EventShowMap', true ) );
		$this->assertEquals( '', get_post_meta( $event->ID, '_EventShowMapLink', true ) );
	}

	/**
	 * It should allow setting the event currency symbol and position
	 *
	 * @test
	 */
	public function should_allow_setting_the_event_currency_symbol_and_position() {
		$args  = [
			'start_date'               => '2018-03-04 09:00:00',
			'end_date'                 => '2018-03-06 11:00:00',
			'timezone'                 => 'Australia/Darwin',
			'title'                    => 'A test event',
			'cost'                     => 23.89,
			'currency_symbol'          => '$',
			'currency_position' => 'postfix',
		];
		$event = tribe_events()->set_args( $args )->create();

		$this->assertEquals( '23.89', get_post_meta( $event->ID, '_EventCost', true ) );
		$this->assertEquals( '$', get_post_meta( $event->ID, '_EventCurrencySymbol', true ) );
		$this->assertEquals( 'postfix', get_post_meta( $event->ID, '_EventCurrencyPosition', true ) );
	}

	/**
	 * It should handle bad currency symbol position cases by setting to prefix
	 *
	 * @test
	 */
	public function should_handle_bad_currency_symbol_position_cases_by_setting_to_prefix() {
		$args  = [
			'start_date' => '2018-03-04 09:00:00',
			'end_date'   => '2018-03-06 11:00:00',
			'timezone'   => 'Australia/Darwin',
			'title'   => 'A test event',
			'currency_symbol' => '$',
			'currency_position' => 'somefix',
		];
		$event = tribe_events()->set_args( $args )->create();

		$this->assertEquals( 'prefix', get_post_meta( $event->ID, '_EventCurrencyPosition', true ) );
	}

	/**
	 * It should allow setting the evet URL
	 *
	 * @test
	 */
	public function should_allow_setting_the_evet_url() {
		$args  = [
			'start_date' => '2018-03-04 09:00:00',
			'end_date'   => '2018-03-06 11:00:00',
			'timezone'   => 'Australia/Darwin',
			'title'      => 'A test event',
			'url'        => 'http://the-event.com'
		];
		$event = tribe_events()->set_args( $args )->create();

		$this->assertEquals( 'http://the-event.com', get_post_meta( $event->ID, '_EventURL', true ) );
	}
}
