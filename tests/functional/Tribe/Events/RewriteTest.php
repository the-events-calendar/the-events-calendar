<?php
namespace Tribe\Events;

use Tribe__Events__Rewrite as Rewrite;

class RewriteTest extends \Codeception\TestCase\WPTestCase {

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

		$this->assertInstanceOf( 'Tribe__Events__Rewrite', $sut );
	}

	private function make_instance() {
		return new Rewrite();
	}

}