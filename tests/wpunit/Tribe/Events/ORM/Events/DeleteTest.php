<?php

namespace Tribe\Events\ORM\Events;

use Tribe\Events\Test\Factories\Event;

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
}