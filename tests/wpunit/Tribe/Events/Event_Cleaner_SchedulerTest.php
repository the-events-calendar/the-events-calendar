<?php
namespace Tribe\Events;

use Codeception\TestCase\WPTestCase;
use Tribe\Events\Test\Factories\Event;
use Tribe__Events__Event_Cleaner_Scheduler as Cleaner_Scheduler;

/**
 * Class Event_Cleaner_SchedulerTest
 *
 * @since TBD
 *
 * @package Tribe\Events\Test
 */
class Event_Cleaner_SchedulerTest extends WPTestCase {

	public function setUp() {
		// before
		parent::setUp();

		$this->factory()->event = new Event();
	}

	public function tearDown() {
		// your tear down methods here

		// then
		parent::tearDown();
	}

	/**
	 * @test
	 */
	public function it_should_be_instantiatable() {
		$sut = $this->make_instance();

		$this->assertInstanceOf( Cleaner_Scheduler::class, $sut );
	}

	/**
	 * @return Cleaner_Scheduler
	 */
	protected function make_instance() {
		return new Cleaner_Scheduler();
	}

	/**
	 * @test
	 */
	public function it_should_delete_only_old_events() {
		$to_delete = $this->factory()->event->create( [
			'when' => '-3 months',
		] );

		$not_to_delete = $this->factory()->event->create( [
			'when' => '-15 days',
		] );

		$events_to_purge = $this->make_instance()->select_events_to_purge( 1 );

		$this->assertContains( $to_delete, $events_to_purge );
		$this->assertNotContains( $not_to_delete, $events_to_purge );
	}


	/**
	 * @test
	 */
	public function it_should_not_delete_posts_or_pages() {
		$created_page = $this->factory()->post->create( [
			'post_type' => 'page',
			'meta_input' => [
				'_EventEndDate' => 1, // Any old value that is before this month
			],
		] );

		$created_post = $this->factory()->post->create( [
			'post_type' => 'post',
			'meta_input' => [
				'_EventEndDate' => 1, // Any old value that is before this month
			],
		] );

		$events_to_purge = $this->make_instance()->select_events_to_purge( 1 );

		$this->assertNotContains( $created_page, $events_to_purge );
		$this->assertNotContains( $created_post, $events_to_purge );
	}

	/**
	 * @test
	 */
	public function it_should_not_delete_empty() {
		$created = $this->factory()->event->create( [
			'meta_input' => [
				'_EventEndDate' => '',
			],
		] );

		$events_to_purge = $this->make_instance()->select_events_to_purge( 1 );

		$this->assertNotContains( $created, $events_to_purge );
	}

	/**
	 * @test
	 */
	public function it_should_not_delete_zero() {
		$created = $this->factory()->event->create( [
			'meta_input' => [
				'_EventEndDate' => 0,
			],
		] );

		$events_to_purge = $this->make_instance()->select_events_to_purge( 1 );

		$this->assertNotContains( $created, $events_to_purge );
	}

	/**
	 * @test
	 */
	public function it_should_not_delete_null() {
		$created = $this->factory()->event->create( [
			'meta_input' => [
				'_EventEndDate' => null,
			],
		] );

		$events_to_purge = $this->make_instance()->select_events_to_purge( 1 );

		$this->assertNotContains( $created, $events_to_purge );
	}
}
