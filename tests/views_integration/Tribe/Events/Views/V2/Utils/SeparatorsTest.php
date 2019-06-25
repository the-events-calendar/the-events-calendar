<?php
namespace Tribe\Events\Views\V2\Utils;

use Tribe\Events\Test\Factories\Event;

class SeparatorsTest extends \Codeception\TestCase\WPTestCase {

	public function setUp() {
		parent::setUp();
		static::factory()->event = new Event();
	}

	/**
	 * @test
	 */
	public function it_should_be_instantiatable() {
		$events[] = $should_have_month_one = static::factory()->event->create( [ 'when' => '2018-09-01' ] );
		$events[] = $should_have_month_two = static::factory()->event->create( [ 'when' => '2018-10-15' ] );
		$events[] = $should_not_have_month_one = static::factory()->event->create( [ 'when' => '2018-10-16' ] );
		$events[] = $should_not_have_month_two = static::factory()->event->create( [ 'when' => '2018-10-16' ] );
		$events[] = $should_have_month_three = static::factory()->event->create( [ 'when' => '2018-11-16' ] );
		$events[] = $should_not_have_month_three = static::factory()->event->create( [ 'when' => '2018-11-22' ] );

		$this->assertTrue( Separators::should_have_month( $events, $should_have_month_one ) );
		$this->assertTrue( Separators::should_have_month( $events, $should_have_month_two ) );
		$this->assertFalse( Separators::should_have_month( $events, $should_not_have_month_one ) );
		$this->assertFalse( Separators::should_have_month( $events, $should_not_have_month_two ) );
		$this->assertTrue( Separators::should_have_month( $events, $should_have_month_three ) );
		$this->assertFalse( Separators::should_have_month( $events, $should_not_have_month_three ) );
	}
}
