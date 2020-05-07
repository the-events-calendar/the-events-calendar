<?php

namespace TEC\Test\functions\classes;

use Codeception\TestCase\WPTestCase;
use Tribe\Events\Test\Factories\Event;
use Tribe\Test\PHPUnit\Traits\With_Post_Remapping;

class Month_Multiday_ClassesTest extends WPTestCase {

	use With_Post_Remapping;

	public function setUp() {
		parent::setUp();

		static::factory()->event = new Event();
		require_once tribe( 'tec.main' )->plugin_path . 'src/Tribe/Views/V2/functions/classes.php';
	}

	/**
	 * @test
	 * It should return expected classes when just passed an event.
	 */
	public function it_should_return_expected_classes_when_just_passed_an_event() {
		$event    = static::factory()->event->create_and_get();
		if ( empty( $event->displays_on ) ) {
			$event->displays_on = [];
		}

		$classes = \Tribe\Events\Views\V2\month_multiday_classes( $event, null, null, null );
		$expected = tribe_get_post_class( [ 'tribe-events-calendar-month__multiday-event' ], $event->ID );

		$this->assertEquals( $expected, $classes, "Classes don't match expected!" );
	}

	/**
	 * @test
	 * It should return expected classes when passed a featured event.
	 */
	public function it_should_return_expected_classes_when_passed_a_featured_event() {
		$event    = static::factory()->event->create_and_get();
		if ( empty( $event->displays_on ) ) {
			$event->displays_on = [];
		}

		$event->featured = true;

		$classes = \Tribe\Events\Views\V2\month_multiday_classes( $event, null, null, null );
		$expected = tribe_get_post_class( [ 'tribe-events-calendar-month__multiday-event' ], $event->ID );
		$expected[] = 'tribe-events-calendar-month__multiday-event--featured';

		$this->assertContains( 'tribe-events-calendar-month__multiday-event--featured', $classes, "Classes missing '--featured' class!" );

		$this->assertEquals( $expected, $classes, "Classes don't match expected!" );
	}

	/**
	 * @test
	 * It should return expected classes when passed a first-day event.
	 */
	public function it_should_return_expected_classes_when_passed_a_first_day_event() {
		$event    = $this->get_mock_event( 'events/single/1.json' );

		$classes = \Tribe\Events\Views\V2\month_multiday_classes( $event, $event->start_date, true, null );
		$expected = tribe_get_post_class( [ 'tribe-events-calendar-month__multiday-event' ], $event->ID );
		$expected[] = 'tribe-events-calendar-month__multiday-event--width-' . $event->this_week_duration;
		$expected[] = 'tribe-events-calendar-month__multiday-event--display';

		$this->assertEquals( $expected, $classes, "Classes don't match expected!" );
	}

	/**
	 * @test
	 * It should return expected classes when passed a first-day "past" event.
	 */
	public function it_should_return_expected_classes_when_passed_a_first_day_past_event() {
		$event    = $this->get_mock_event( 'events/single/1.json' );

		$classes = \Tribe\Events\Views\V2\month_multiday_classes( $event, $event->start_date, true, $event->start_date );
		$expected = tribe_get_post_class( [ 'tribe-events-calendar-month__multiday-event' ], $event->ID );
		$expected[] = 'tribe-events-calendar-month__multiday-event--width-' . $event->this_week_duration;
		$expected[] = 'tribe-events-calendar-month__multiday-event--display';
		$expected[] = 'tribe-events-calendar-month__multiday-event--past';

		$this->assertEquals( $expected, $classes, "Classes don't match expected!" );
	}

	/**
	 * @test
	 * It should return expected classes when passed a first-day "starts-this-week" event.
	 */
	public function it_should_return_expected_classes_when_passed_a_first_day_starts_this_week_event() {
		$event    = $this->get_mock_event( 'events/single/1.json' );
		$event->starts_this_week = true;

		$classes = \Tribe\Events\Views\V2\month_multiday_classes( $event, $event->start_date, true, null );
		$expected = tribe_get_post_class( [ 'tribe-events-calendar-month__multiday-event' ], $event->ID );
		$expected[] = 'tribe-events-calendar-month__multiday-event--width-' . $event->this_week_duration;
		$expected[] = 'tribe-events-calendar-month__multiday-event--display';
		$expected[] = 'tribe-events-calendar-month__multiday-event--start';

		$this->assertEquals( $expected, $classes, "Classes don't match expected!" );
	}

	/**
	 * @test
	 * It should return expected classes when passed a first-day "ends-this-week" event.
	 */
	public function it_should_return_expected_classes_when_passed_a_first_day_ends_this_week_event() {
		$event    = $this->get_mock_event( 'events/single/1.json' );
		$event->ends_this_week = true;

		$classes = \Tribe\Events\Views\V2\month_multiday_classes( $event, $event->start_date, true, null );
		$expected = tribe_get_post_class( [ 'tribe-events-calendar-month__multiday-event' ], $event->ID );
		$expected[] = 'tribe-events-calendar-month__multiday-event--width-' . $event->this_week_duration;
		$expected[] = 'tribe-events-calendar-month__multiday-event--display';
		$expected[] = 'tribe-events-calendar-month__multiday-event--end';

		$this->assertEquals( $expected, $classes, "Classes don't match expected!" );
	}
}
