<?php

namespace Tribe\Events\Views\V2\Views;

use Spatie\Snapshots\MatchesSnapshots;
use Tribe\Events\Views\V2\View;
use Tribe\Test\Products\WPBrowser\Views\V2\ViewTestCase;

class Month_ViewTest extends ViewTestCase {
	use MatchesSnapshots;

	/**
	 * The mock rendering context.
	 *
	 * @var \Tribe__Context|\WP_UnitTest_Factory|null
	 */
	protected $context;

	public function setUp() {
		parent::setUp();

		$now = new \DateTime( $this->mock_date_value );

		$this->context = tribe_context()->alter( [
			'event_date' => $now->format( 'Y-m-d' ),
		] );
	}

	/**
	 * Test render empty
	 */
	public function test_render_empty() {
		$month_view = View::make( Month_View::class, $this->context );

		$this->assertEmpty( $month_view->found_post_ids() );

		// @todo @luca do not skip  this!
		// Currently commented out while working on the day/event format and its use in the template.
		// $this->assertMatchesSnapshot( $month_view->get_html() )
	}

	/**
	 * Test render with events
	 */
	public function test_render_with_events() {
		/** @var Month_View $month_view */
		$month_view      = View::make( Month_View::class );
		$timezone_string = 'Europe/Paris';
		$timezone        = new \DateTimeZone( $timezone_string );
		update_option( 'timezone_string', $timezone_string );

		$now = new \DateTimeImmutable( $this->mock_date_value, $timezone );

		$events    = array_map( static function ( $i ) use ( $now, $timezone ) {
			return tribe_events()->set_args( [
					'start_date' => $now->setTime( 10 + $i, 0 ),
					'timezone'   => $timezone,
					'duration'   => 3 * HOUR_IN_SECONDS,
					'title'      => 'Test Event - ' . $i,
					'status'     => 'publish',
				]
			)->create();
		}, range( 1, 3 ) );
		$event_ids = array_map( static function ( \WP_Post $event ) {
			return $event->ID;
		}, $events );

		// @todo @luca do not skip  this!
		// $month_view->get_html();

		$this->assertEquals( $event_ids, $month_view->found_post_ids() );

		foreach ( $month_view->get_grid_days($now->format('Y-m')) as $date => $found_day_ids ) {
			$day          = new \DateTimeImmutable( $date, $timezone );
			$expected_ids = tribe_events()
				->where(
					'date_overlaps',
					$day->setTime( 0, 0 ),
					$day->setTime( 23, 59, 59 ),
					$timezone
				)->get_ids();

			$this->assertEquals(
				$expected_ids,
				$found_day_ids,
				sprintf(
					'Day %s event IDs mismatch, expected %s, got %s',
					$day->format( 'Y-m-d' ),
					json_encode( $expected_ids ),
					json_encode( $found_day_ids )
				) );
		}

		// $this->assertMatchesSnapshot( $month_view->get_html() );
	}
}
