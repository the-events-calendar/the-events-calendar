<?php

namespace Tribe\Events\ORM\Venues;

use Tribe\Events\Test\Factories\Venue;
use Tribe__Promise as Promise;

class DeleteTest extends \Codeception\TestCase\WPTestCase {

	public function setUp(): void {
		parent::setUp();
		$this->factory()->venue = new Venue();
	}

	/**
	 * It should allow deleting venues
	 *
	 * @test
	 */
	public function should_allow_deleting_venues() {
		$venues = $this->factory()->venue->create_many( 3 );

		$deleted = tribe_venues()
			->where( 'post__in', $venues )
			->delete();

		$this->assertEqualSets( $venues, $deleted );
	}

	/**
	 * It should allow deleting venues and getting a promise
	 *
	 * @test
	 */
	public function should_allow_deleting_venues_and_getting_a_promise() {
		$filter_name = tribe_venues()->get_filter_name();
		add_filter( "tribe_repository_{$filter_name}_delete_background_activated", '__return_true' );
		add_filter( "tribe_repository_{$filter_name}_delete_background_threshold", function () {
			return 1;
		} );

		$venues = $this->factory()->venue->create_many( 3 );

		$promise = tribe_venues()
			->where( 'post__in', $venues )
			->delete( true );

		$this->assertInstanceOf( Promise::class, $promise );
	}
}
