<?php

namespace Tribe\Events\ORM\Events;

use Tribe\Events\Test\Factories\Event;
use Tribe\Events\Test\Factories\Organizer;
use Tribe\Events\Test\Factories\Venue;

class FetchOrderByTest extends \Codeception\TestCase\WPTestCase {

	public function setUp() {
		// before
		parent::setUp();

		// your set up methods here
		$this->factory()->event     = new Event();
		$this->factory()->organizer = new Organizer();
		$this->factory()->venue     = new Venue();
	}

	/**
	 * It should allow sorting by post date.
	 *
	 * @test
	 */
	public function should_allow_sorting_by_post_date() {
		$total_events = 10;
		$post_date    = time();
		$test_ids     = [];

		for ( $x = 0; $x < $total_events; $x++ ) {
			$post_date -= WEEK_IN_SECONDS;

			$event_args = [
				'post_date' => date_i18n( 'Y-m-d H:i:s', $post_date ),
			];

			$test_ids[] = $this->factory()->event->create( $event_args );
		}

		$test_ids_reverse = array_reverse( $test_ids );

		$this->assertEqualSets( $test_ids, tribe_events()->order_by( 'date' )->order( 'ASC' )->get_ids() );
		$this->assertEqualSets( $test_ids_reverse, tribe_events()->order_by( 'date' )->order( 'DESC' )->get_ids() );
		$this->assertEqualSets( $test_ids_reverse, tribe_events()->get_ids() );
		$this->assertCount( $total_events, tribe_events()->get_ids() );
	}

	/**
	 * It should allow sorting by event date.
	 *
	 * @test
	 */
	public function should_allow_sorting_by_event_date() {
		$total_events = 10;
		$post_date    = time();
		$start_date   = $post_date;
		$test_ids     = [];

		for ( $x = 0; $x < $total_events; $x++ ) {
			$post_date  -= WEEK_IN_SECONDS;
			$start_date += WEEK_IN_SECONDS;

			$event_args = [
				'meta_input' => [
					'_EventStartDate' => date_i18n( 'Y-m-d', $start_date ),
				],
				'post_date'  => date_i18n( 'Y-m-d H:i:s', $post_date ),
			];

			$test_ids[] = $this->factory()->event->create( $event_args );
		}

		$test_ids_reverse = array_reverse( $test_ids );

		$this->assertEqualSets( $test_ids, tribe_events()->order_by( 'event_date' )->order( 'ASC' )->get_ids() );
		$this->assertEqualSets( $test_ids_reverse, tribe_events()->order_by( 'event_date' )->order( 'DESC' )->get_ids() );
		$this->assertCount( $total_events, tribe_events()->get_ids() );
	}

	/**
	 * It should allow sorting by organizer name.
	 *
	 * @test
	 */
	public function should_allow_sorting_by_organizer_name() {
		$total_events = 10;
		$post_date    = time();
		$start_date   = $post_date;
		$letters      = range( 'a', 'z' );
		$test_ids     = [];

		for ( $x = 0; $x < $total_events; $x++ ) {
			$post_date  -= WEEK_IN_SECONDS;
			$start_date += WEEK_IN_SECONDS;

			$event_args = [
				'organizer'  => $this->factory()->organizer->create( [
					'post_title' => str_repeat( $letters[ $x ], 5 ) . ' organizer',
				] ),
				'meta_input' => [
					'_EventStartDate' => date_i18n( 'Y-m-d', $start_date ),
				],
				'post_date'  => date_i18n( 'Y-m-d H:i:s', $post_date ),
			];

			$test_ids[] = $this->factory()->event->create( $event_args );
		}

		$test_ids_reverse = array_reverse( $test_ids );

		$this->assertEqualSets( $test_ids, tribe_events()->order_by( 'organizer' )->order( 'ASC' )->get_ids() );
		$this->assertEqualSets( $test_ids_reverse, tribe_events()->order_by( 'organizer' )->order( 'DESC' )->get_ids() );
		$this->assertCount( $total_events, tribe_events()->get_ids() );
	}

	/**
	 * It should allow sorting by venue name.
	 *
	 * @test
	 */
	public function should_allow_sorting_by_venue_name() {
		$total_events = 10;
		$post_date    = time();
		$start_date   = $post_date;
		$letters      = range( 'a', 'z' );
		$test_ids     = [];

		for ( $x = 0; $x < $total_events; $x++ ) {
			$post_date  -= WEEK_IN_SECONDS;
			$start_date += WEEK_IN_SECONDS;

			$event_args = [
				'venue'      => $this->factory()->venue->create( [
					'post_title' => str_repeat( $letters[ $x ], 5 ) . ' venue',
				] ),
				'meta_input' => [
					'_EventStartDate' => date_i18n( 'Y-m-d', $start_date ),
				],
				'post_date'  => date_i18n( 'Y-m-d H:i:s', $post_date ),
			];

			$test_ids[] = $this->factory()->event->create( $event_args );
		}

		$test_ids_reverse = array_reverse( $test_ids );

		$this->assertEqualSets( $test_ids, tribe_events()->order_by( 'venue' )->order( 'ASC' )->get_ids() );
		$this->assertEqualSets( $test_ids_reverse, tribe_events()->order_by( 'venue' )->order( 'DESC' )->get_ids() );
		$this->assertCount( $total_events, tribe_events()->get_ids() );
	}
}
