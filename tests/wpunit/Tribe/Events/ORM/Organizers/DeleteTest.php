<?php

namespace Tribe\Events\ORM\Organizers;

use Tribe\Events\Test\Factories\Organizer;
use Tribe__Promise as Promise;

class DeleteTest extends \Codeception\TestCase\WPTestCase {

	public function setUp() {
		parent::setUp();
		$this->factory()->organizer = new Organizer();
	}

	/**
	 * It should allow deleting organizers
	 *
	 * @test
	 */
	public function should_allow_deleting_organizers() {
		$organizers = $this->factory()->organizer->create_many( 3 );

		$deleted = tribe_organizers()
			->where( 'post__in', $organizers )
			->delete();

		$this->assertEqualSets( $organizers, $deleted );
	}

	/**
	 * It should allow deleting organizers and getting a promise
	 *
	 * @test
	 */
	public function should_allow_deleting_organizers_and_getting_a_promise() {
		$filter_name = tribe_organizers()->get_filter_name();
		add_filter( "tribe_repository_{$filter_name}_delete_background_activated", '__return_true' );
		add_filter( "tribe_repository_{$filter_name}_delete_background_threshold", function () {
			return 1;
		} );

		$organizers = $this->factory()->organizer->create_many( 3 );

		$promise = tribe_organizers()
			->where( 'post__in', $organizers )
			->delete( true );

		$this->assertInstanceOf( Promise::class, $promise );
	}
}