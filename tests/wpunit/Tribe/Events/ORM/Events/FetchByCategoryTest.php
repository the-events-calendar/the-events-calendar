<?php

namespace Tribe\Events\ORM\Events;

use Tribe\Events\Test\Factories\Event;
use Tribe\Events\Test\Factories\Organizer;
use Tribe\Events\Test\Factories\Venue;
use Tribe__Events__Main as TEC;

class FetchByCategoryTest extends \Codeception\TestCase\WPTestCase {

	public static function wpSetUpBeforeClass() {
		static::factory()->event     = new Event();
		static::factory()->organizer = new Organizer();
		static::factory()->venue     = new Venue();
	}

	/**
	 * It should allow fetching events by category using event_category
	 *
	 * @test
	 */
	public function should_allow_fetching_events_by_category_using_event_category() {
		list( $cat1, $no_cat_events, $cat1_events, $cat2_events, $cat1_and_cat2_events ) = $this->given_some_terms_and_events();

		$this->assertEqualSets(
			array_merge( $cat1_and_cat2_events, $cat1_events, $cat2_events, $no_cat_events ),
			tribe_events()->get_ids()
		);
		$this->assertEqualSets(
			array_merge( $cat1_events, $cat1_and_cat2_events ),
			tribe_events()->where( 'event_category', 'cat1' )->get_ids()
		);
		$this->assertEqualSets(
			array_merge( $cat1_events, $cat1_and_cat2_events ),
			tribe_events()->where( 'event_category', [ 'cat1' ] )->get_ids()
		);
		$this->assertEqualSets(
			array_merge( $cat1_events, $cat1_and_cat2_events ),
			tribe_events()->where( 'event_category', $cat1 )->get_ids()
		);
		$this->assertEqualSets(
			array_merge( $cat1_events, $cat1_and_cat2_events ),
			tribe_events()->where( 'event_category', [ $cat1 ] )->get_ids()
		);
	}

	/**
	 * It should allow fetching events by category useing tribe_events_cat
	 *
	 * @test
	 */
	public function should_allow_fetching_events_by_category_useing_tribe_events_cat() {
		list( $cat1, $no_cat_events, $cat1_events, $cat2_events, $cat1_and_cat2_events ) = $this->given_some_terms_and_events();

		$this->assertEqualSets(
			array_merge( $cat1_and_cat2_events, $cat1_events, $cat2_events, $no_cat_events ),
			tribe_events()->get_ids()
		);
		$this->assertEqualSets(
			array_merge( $cat1_events, $cat1_and_cat2_events ),
			tribe_events()->where( 'tribe_events_cat', 'cat1' )->get_ids()
		);
		$this->assertEqualSets(
			array_merge( $cat1_events, $cat1_and_cat2_events ),
			tribe_events()->where( 'tribe_events_cat', [ 'cat1' ] )->get_ids()
		);
		$this->assertEqualSets(
			array_merge( $cat1_events, $cat1_and_cat2_events ),
			tribe_events()->where( 'tribe_events_cat', $cat1 )->get_ids()
		);
		$this->assertEqualSets(
			array_merge( $cat1_events, $cat1_and_cat2_events ),
			tribe_events()->where( 'tribe_events_cat', [ $cat1 ] )->get_ids()
		);
	}

	/**
	 * It should allow fetching events by category using category
	 *
	 * @test
	 */
	public function should_allow_fetching_events_by_category_using_category() {
		list( $cat1, $no_cat_events, $cat1_events, $cat2_events, $cat1_and_cat2_events ) = $this->given_some_terms_and_events();

		$this->assertEqualSets(
			array_merge( $cat1_and_cat2_events, $cat1_events, $cat2_events, $no_cat_events ),
			tribe_events()->get_ids()
		);
		$this->assertEqualSets(
			array_merge( $cat1_events, $cat1_and_cat2_events ),
			tribe_events()->where( 'category', 'cat1' )->get_ids()
		);
		$this->assertEqualSets(
			array_merge( $cat1_events, $cat1_and_cat2_events ),
			tribe_events()->where( 'category', [ 'cat1' ] )->get_ids()
		);
		$this->assertEqualSets(
			array_merge( $cat1_events, $cat1_and_cat2_events ),
			tribe_events()->where( 'category', $cat1 )->get_ids()
		);
		$this->assertEqualSets(
			array_merge( $cat1_events, $cat1_and_cat2_events ),
			tribe_events()->where( 'category', [ $cat1 ] )->get_ids()
		);
	}

	protected function given_some_terms_and_events(): array {
		wp_set_current_user( static::factory()->user->create( [ 'role' => 'editor' ] ) );
		$cat1                 = static::factory()->term->create( [ 'taxonomy' => TEC::TAXONOMY, 'name' => 'cat1' ] );
		$cat2                 = static::factory()->term->create( [ 'taxonomy' => TEC::TAXONOMY, 'name' => 'cat2' ] );
		$no_cat_events        = static::factory()->event->create_many( 2 );
		$cat1_events          = static::factory()->event->create_many( 2, [ 'tax_input' => [ TEC::TAXONOMY => [ $cat1 ] ] ] );
		$cat2_events          = static::factory()->event->create_many( 2, [ 'tax_input' => [ TEC::TAXONOMY => [ $cat2 ] ] ] );
		$cat1_and_cat2_events = static::factory()->event->create_many( 2, [ 'tax_input' => [ TEC::TAXONOMY => [ $cat1, $cat2 ] ] ] );

		return array( $cat1, $no_cat_events, $cat1_events, $cat2_events, $cat1_and_cat2_events );
	}
}
