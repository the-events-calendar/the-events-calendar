<?php

namespace Tribe\Events\Aggregator\Record;

use Tribe__Events__Aggregator__Record__Unsupported as Unsupported;

class UnsupportedTest extends \Codeception\TestCase\WPTestCase {

	public function setUp() {
		// before
		parent::setUp();

		// your set up methods here
	}

	public function tearDown() {
		// your tear down methods here

		// then
		parent::tearDown();
	}

	/**
	 * @test
	 * it should be instantiatable
	 */
	public function it_should_be_instantiatable() {
		$sut = $this->make_instance();

		$this->assertInstanceOf( Unsupported::class, $sut );
	}

	/**
	 * @return Unsupported
	 */
	private function make_instance() {
		return new Unsupported();
	}

	/**
	 * It should remove the unsupported record on shutdown
	 *
	 * @test
	 */
	public function should_remove_the_unsupported_record_on_shutdown() {
		add_filter('tribe_aggregator_clean_unsupported','__return_true');

		$record = $this->make_instance();

		$this->assertTrue( (bool)has_action( 'shutdown', [ $record, 'delete_post' ] ) );
	}

	/**
	 * It should allow preventing deletion with filter
	 *
	 * @test
	 */
	public function should_allow_preventing_deletion_with_filter() {
		add_filter('tribe_aggregator_clean_unsupported','__return_false');

		$record = $this->make_instance();

		$this->assertFalse( has_action( 'shutdown', [ $record, 'delete_post' ] ) );
	}
}