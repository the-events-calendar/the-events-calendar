<?php

namespace Tribe\Events\ORM\Events;

use Tribe\Events\Test\Factories\Event;

class FetchTest extends \Codeception\TestCase\WPTestCase {

	public function setUp() {
		// before
		parent::setUp();

		// your set up methods here
		$this->factory()->event = new Event();
	}

	/**
	 * It should allow getting events by all-day status
	 *
	 * @test
	 */
	public function should_allow_getting_events_by_all_day_status() {
		$all_day     = $this->factory()->event->create_many( 2, [ 'meta_input' => [ '_EventAllDay' => 'yes' ] ] );
		$not_all_day = $this->factory()->event->create_many( 3 );

		$this->assertEqualSets( $all_day, tribe_events()->where( 'all_day', true )->get_ids() );
		$this->assertEqualSets( $not_all_day, tribe_events()->where( 'all_day', false )->get_ids() );
		$this->assertCount( 5, tribe_events()->get_ids() );
	}
}
