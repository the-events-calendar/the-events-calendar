<?php
namespace Tribe\Events;

use Codeception\TestCase\WPTestCase;
use Tribe\Events\Models\Post_Types\Event;
use Tribe__Events__Main as Main;
use Tribe__Date_Utils as Dates;
use Tribe\Test\PHPUnit\Traits\With_Post_Remapping;
use Tribe\Tests\Traits\With_Uopz;

/**
 * Test that Common is being loaded correctly
 *
 * @group   core
 *
 * @package Tribe__Events__Main
 */
class Event_ModelTest extends WPTestCase {
	use With_Post_Remapping, With_Uopz;

	public function is_now_events() {
		return [
			[
				'start_date' => '-1 day',
				'duration'   => 'P2D',
				'expected'   => true,
			],
			[
				'start_date' => '+1 day',
				'duration'   => 'P2D',
				'expected'   => false,
			],
			[
				'start_date' => '-2 day',
				'duration'   => 'P1D',
				'expected'   => false,
			],
			[
				'start_date' => '-1 hour',
				'duration'   => 'PT1H',
				'expected'   => false,
			],
			[
				'start_date' => '+1 hour',
				'duration'   => 'PT1H',
				'expected'   => false,
			]
		];

	}

	/**
	 * @test
	 */
	public function it_should_return_expected_id() {
		$mock  = $this->get_mock_event( 'events/single/1.json' );
		$event = Event::from_post( $mock )->to_post();

		$this->assertEquals( $mock->ID, $event->ID );
	}

	/**
	 * @test
	 */
	public function it_should_return_expected_post_type() {
		$mock  = $this->get_mock_event( 'events/single/1.json' );
		$event = Event::from_post( $mock )->to_post();

		$this->assertEquals( Main::POSTTYPE, $event->post_type );
	}

	/**
	 * @test
	 */
	public function it_should_return_past() {
		$mock  = $this->get_mock_event( 'events/single/1.json' );
		$event = Event::from_post( $mock )->to_post();

		$this->assertTrue( $event->is_past );
	}

	/**
	 * @test
	 *
	 * @dataProvider is_now_events
	 */
	public function test_is_now_property( string $when, string $duration, bool $expected) {
		$start = Dates::build_date_object(  $when );

		$args = [
			'title'      => 'Single Event',
			'start_date' => $start->format( Dates::DBDATETIMEFORMAT ),
			'end_date'   => $start->add( new \DateInterval( $duration ) )->format( Dates::DBDATETIMEFORMAT ),
			'timezone'   => 'Europe/Paris',
		];

		$event = tribe_events()
			->set_args( $args )->create();

		$this->assertEquals( $expected, $event->is_now );
	}

	/**
	 * @test
	 */
	public function it_should_return_featured() {
		$mock  = $this->get_mock_event( 'events/featured/1.json' );
		$event = Event::from_post( $mock )->to_post();

		$this->assertTrue( $event->featured );
	}

	/**
	 * @test
	 */
	public function it_should_return_all_day() {
		$mock  = $this->get_mock_event( 'events/all-day/1.json' );
		$event = Event::from_post( $mock )->to_post();

		$this->assertTrue( $event->all_day );
	}

	/**
	 * @test
	 */
	public function it_should_return_multi_day() {
		$mock  = $this->get_mock_event( 'events/multiday/1.json' );
		$event = Event::from_post( $mock )->to_post();

		$this->assertNotEmpty( $event->multiday );
	}
}
