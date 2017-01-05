<?php

namespace Tribe\Events\REST\V1;

use Tribe__Events__REST__V1__Post_Repository as Post_Repository;

class Post_RepositoryTest extends \Codeception\TestCase\WPTestCase {

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

		$this->assertInstanceOf( Post_Repository::class, $sut );
	}

	/**
	 * @return Post_Repository
	 */
	private function make_instance() {
		return new Post_Repository();
	}
}