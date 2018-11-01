<?php

namespace Tribe\Events\ORM\Events;

use Tribe\Events\Test\Factories\Event;
use Tribe__Promise as Promise;

class DeleteTest extends \Codeception\TestCase\WPTestCase {

	public function setUp() {
		parent::setUp();
		$this->factory()->event = new Event();
	}

	/**
	 * It should allow deleting events
	 *
	 * @test
	 */
	public function should_allow_deleting_events() {
		$after_today  = array_map( function ( $i ) {
			return $this->factory()->event->create( [ 'when' => "+{$i} weeks" ] );
		}, range( 1, 3 ) );
		$before_today = array_map( function ( $i ) {
			return $this->factory()->event->create( [ 'when' => "-{$i} weeks" ] );
		}, range( 1, 3 ) );

		$deleted = tribe_events()->where( 'starts_after', 'today' )->delete();

		$this->assertEqualSets($after_today, $deleted);
	}

	/**
	 * It should allow deleting events and getting a promise
	 *
	 * @test
	 */
	public function should_allow_deleting_events_and_getting_a_promise() {
		$filter_name = tribe_events()->get_filter_name();
		add_filter( "tribe_repository_{$filter_name}_delete_background_activated", '__return_true' );
		add_filter( "tribe_repository_{$filter_name}_delete_background_threshold", function () {
			return 1;
		} );
		$after_today  = array_map( function ( $i ) {
			return $this->factory()->event->create( [ 'when' => "+{$i} weeks" ] );
		}, range( 1, 3 ) );
		$before_today = array_map( function ( $i ) {
			return $this->factory()->event->create( [ 'when' => "-{$i} weeks" ] );
		}, range( 1, 3 ) );

		$promise = tribe_events()->where( 'starts_after', 'today' )->delete( true );

		$this->assertInstanceOf( Promise::class, $promise );
	}
}