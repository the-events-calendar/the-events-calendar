<?php
namespace Tribe\Events\Editor;

use Tribe__Events__Editor__Template;


class TemplateTest extends \Codeception\TestCase\WPTestCase {
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
	 * @return Tribe__Events__Editor__Template
	 */
	private function make_instance() {
		return new Tribe__Events__Editor__Template();
	}

	/**
	 * @test
	 * it should be instantiatable
	 */
	public function it_should_be_instantiatable() {
		$sut = $this->make_instance();

		$this->assertInstanceOf( 'Tribe__Events__Editor__Template', $sut );
	}

	/**
	 * @test
	 * it should return default when value not set
	 */
	public function it_should_return_default_when_value_not_set() {
		$sut = $this->make_instance();

		$attr = $sut->attr( 'nonexistent_attribute', null );

		$this->assertNull( $attr, 'Failed passing null as default.' );

		$attr = $sut->attr( 'nonexistent_attribute', 'default' );

		$this->assertEquals( $attr, 'default', 'Failed passing string as default.' );
	}

	/**
	 * @test
	 * it should return set local value with get()
	 *
	 * @return void
	 */
	public function it_should_return_set_local_value_with_get() {
		$sut = $this->make_instance();

		$sut->set( 'test_index', 'fnord' );
		$attr = $sut->get( 'test_index', 'default' );

		$this->assertEquals( $attr, 'fnord', 'Failed getting set local value with get().' );
	}

	/**
	 * @test
	 * it should return set global value with get()
	 *
	 * @return void
	 */
	public function it_should_return_set_global_value_with_get() {
		$sut = $this->make_instance();

		$sut->set( 'test_index', 'fnord', false );
		$attr = $sut->get( 'test_index', 'default', false );

		$this->assertEquals( $attr, 'fnord', 'Failed getting set global value with get().' );
	}

	/**
	 * @test
	 * it should return set attribute with attr()
	 *
	 * @return void
	 */
	public function it_should_return_set_attribute_with_attr() {
		$sut = $this->make_instance();

		// Attributes are set one-deep in the 'attributes' array.
		$sut->set( [ 'attributes', 'test_index' ], 'fnord' );
		$attr = $sut->attr( 'test_index', 'default' );

		$this->assertEquals( $attr, 'fnord', 'Failed getting set value with attr()' );
	}

	/**
	 * @test
	 * it should return default for an unset attribute with attr()
	 *
	 * @return void
	 */
	public function it_should_return_default_for_an_unset_attribute_with_attr() {
		$sut = $this->make_instance();

		// Attributes are set one-deep in the 'attributes' array.
		$attr = $sut->attr( 'test_index', 'default' );

		$this->assertEquals( $attr, 'default', 'Failed getting default value with attr()' );
	}
}