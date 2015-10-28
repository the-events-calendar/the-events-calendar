<?php
namespace Tribe\Events\Pro\Recurrence;

use Tribe__Events__Pro__Recurrence__Validator;

class ValidatorTest extends \WP_UnitTestCase {

	protected $backupGlobals = false;

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
	 * it should return false if post is not event
	 */
	public function it_should_return_false_if_post_is_not_event() {
		$post_id = $this->factory->post->create();

		$sut   = new Tribe__Events__Pro__Recurrence__Validator;
		$valid = $sut->is_valid( $post_id, array() );

		$this->assertFalse( $valid );
	}
}