<?php

namespace TEC\Test\functions\template_tags;

use Codeception\TestCase\WPTestCase;
use Tribe\Events\Test\Factories\Event;
use Tribe__Events__Main as TEC;

/**
 * Covers the legacy branches of the recurrence template tags: pre-6.0 child-post
 * recurring Events and the `_EventRecurrence` meta shapes. The Custom Tables are
 * disabled in this suite, so the Occurrence-count branch is exercised in the
 * `ct1_integration` suite instead.
 */
class recurrenceTest extends WPTestCase {
	public function setUp() {
		parent::setUp();
		static::factory()->event = new Event();
	}

	/**
	 * It should not flag invalid or non event posts as recurring
	 *
	 * @test
	 */
	public function should_not_flag_invalid_or_non_event_posts_as_recurring(): void {
		$this->assertFalse( tribe_is_recurring_event( 0 ) );
		$this->assertFalse( tribe_is_recurring_event( PHP_INT_MAX - 1 ) );

		$page = static::factory()->post->create( [ 'post_type' => 'page' ] );
		$this->assertFalse( tribe_is_recurring_event( $page ) );
	}

	/**
	 * It should flag legacy child post instances as recurring
	 *
	 * @test
	 */
	public function should_flag_legacy_child_post_instances_as_recurring(): void {
		$parent = static::factory()->event->create();
		$child  = static::factory()->event->create( [ 'post_parent' => $parent ] );

		// Pre-6.0 recurring Event instances are child posts of the recurring Event.
		$this->assertTrue( tribe_is_recurring_event( $child ) );
		// The parent alone, without recurrence meta, is not recurring.
		$this->assertFalse( tribe_is_recurring_event( $parent ) );
	}

	/**
	 * It should flag events by their recurrence rules meta
	 *
	 * @test
	 */
	public function should_flag_events_by_their_recurrence_rules_meta(): void {
		$event = static::factory()->event->create();

		update_post_meta( $event, '_EventRecurrence', [ 'rules' => [ [ 'type' => 'Custom' ] ] ] );
		$this->assertTrue( tribe_is_recurring_event( $event ) );

		// Rules explicitly typed `None` do not make the Event recurring.
		update_post_meta( $event, '_EventRecurrence', [ 'rules' => [ [ 'type' => 'None' ] ] ] );
		$this->assertFalse( tribe_is_recurring_event( $event ) );
	}

	/**
	 * It should support the pre 3 12 recurrence meta shape
	 *
	 * @test
	 */
	public function should_support_the_pre_3_12_recurrence_meta_shape(): void {
		$event = static::factory()->event->create();

		update_post_meta( $event, '_EventRecurrence', [ 'type' => 'Every Week' ] );
		$this->assertTrue( tribe_is_recurring_event( $event ) );

		update_post_meta( $event, '_EventRecurrence', [ 'type' => 'None' ] );
		$this->assertFalse( tribe_is_recurring_event( $event ) );
	}

	/**
	 * It should allow filtering the recurring flag
	 *
	 * @test
	 */
	public function should_allow_filtering_the_recurring_flag(): void {
		$event = static::factory()->event->create();

		add_filter( 'tribe_is_recurring_event', '__return_true' );
		$this->assertTrue( tribe_is_recurring_event( $event ) );
		remove_filter( 'tribe_is_recurring_event', '__return_true' );

		$this->assertFalse( tribe_is_recurring_event( $event ) );
	}

	/**
	 * It should list the legacy start dates from the parent and children
	 *
	 * @test
	 */
	public function should_list_the_legacy_start_dates_from_the_parent_and_children(): void {
		$parent = static::factory()->event->create( [ 'when' => '2026-11-05 09:00:00' ] );
		static::factory()->event->create(
			[
				'when'        => '2026-11-19 09:00:00',
				'post_parent' => $parent,
			]
		);
		static::factory()->event->create(
			[
				'when'        => '2026-11-12 09:00:00',
				'post_parent' => $parent,
			]
		);

		$expected = [ '2026-11-05 09:00:00', '2026-11-12 09:00:00', '2026-11-19 09:00:00' ];

		// From the parent, in ascending order.
		$this->assertEquals( $expected, tribe_get_recurrence_start_dates( $parent ) );
	}

	/**
	 * It should echo and cache the all occurrences link
	 *
	 * @test
	 */
	public function should_echo_and_cache_the_all_occurrences_link(): void {
		$event = static::factory()->event->create();

		ob_start();
		$returned = tribe_all_occurrences_link( $event );
		$echoed   = ob_get_clean();

		$this->assertNotEmpty( $returned );
		$this->assertEquals( esc_url( $returned ), $echoed );

		// The no-echo signature stays silent.
		ob_start();
		$silent = tribe_all_occurrences_link( $event, false );
		$this->assertSame( '', ob_get_clean() );
		$this->assertEquals( $returned, $silent );

		// The link is cached per request: a late filter does not change it.
		add_filter( 'tribe_all_occurrences_link', '__return_empty_string' );
		$this->assertEquals( $returned, tribe_all_occurrences_link( $event, false ) );
		remove_filter( 'tribe_all_occurrences_link', '__return_empty_string' );
	}
}
