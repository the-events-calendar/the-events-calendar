<?php
namespace Tribe\Events;

use DeepCopy\Reflection\ReflectionHelper;
use Tribe__Events__Rewrite as Rewrite;

/**
 * Class RewriteTest
 *
 * @package Tribe\Events
 */
class RewriteTest extends \Codeception\TestCase\WPTestCase {

	/**
	 * @var \WP_Rewrite
	 */
	protected $wp_rewrite;

	public function setUp() {
		// before
		parent::setUp();

		// your set up methods here
		$this->wp_rewrite = $this->prophesize( \WP_Rewrite::class );
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

		$this->assertInstanceOf( Rewrite::class, $sut );
	}

	/**
	 * @return Rewrite
	 */
	private function make_instance() {
		return new Rewrite( $this->wp_rewrite->reveal() );
	}
}
