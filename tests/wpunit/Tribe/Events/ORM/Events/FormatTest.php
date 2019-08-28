<?php
/**
 *
 *
 * @since   TBD
 * @package Tribe\Events\ORM\Events
 */

namespace Tribe\Events\ORM\Events;

use Tribe\Events\Test\Factories\Event;

class FormatTest extends \Codeception\TestCase\WPTestCase {
	public static function setUpBeforeClass() {
		parent::setUpBeforeClass();
		static::factory()->event = new Event();
	}

	/**
	 * It should format results using tribe_get_event
	 *
	 * @test
	 */
	public function should_format_results_using_tribe_get_event() {
		$event = static::factory()->event->create();

		$fetched_from_repository = tribe_events()->first();

		$this->assertEquals( tribe_get_event( $event ), $fetched_from_repository );
	}
}
