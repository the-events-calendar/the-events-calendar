<?php
namespace Tribe\Events\Utils;

use Tribe__Events__Constants as Constants;

class ConstantsTest extends \Codeception\TestCase\WPTestCase {

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
	 * it should assert constant is not defined
	 */
	public function it_should_assert_constant_is_not_defined() {
		$sut = new Constants();
		$this->assertFalse( isset( $sut['FOO'] ) );
	}

	/**
	 * @test
	 * it should return false if constant is false
	 */
	public function it_should_return_false_if_constant_is_false() {
		define( '__FOO__CONSTANT_1', false );
		$sut = new Constants();
		$this->assertFalse( $sut['__FOO__CONSTANT_1'] );
	}

	/**
	 * @test
	 * it should return value if constant set
	 */
	public function it_should_return_value_if_constant_set() {
		define( '__FOO__CONSTANT_2', 'some value' );
		$sut = new Constants();
		$this->assertEquals( 'some value', $sut['__FOO__CONSTANT_2'] );
	}

	/**
	 * @test
	 * it should set an undefined constant
	 */
	public function it_should_set_an_undefined_constant() {
		$sut = new Constants();

		$sut['__FOO__CONSTANT_3'] = 'some value';
		$this->assertEquals( 'some value', $sut['__FOO__CONSTANT_3'] );
		$this->assertEquals( 'some value', __FOO__CONSTANT_3 );
	}

	/**
	 * @test
	 * it should not redefine defined constant
	 */
	public function it_should_not_redefine_defined_constant() {
		define( '__FOO__CONSTANT_4', 'some value' );

		$sut                      = new Constants();
		$sut['__FOO__CONSTANT_4'] = 'another value';

		$this->assertEquals( 'some value', $sut['__FOO__CONSTANT_4'] );
		$this->assertEquals( 'some value', __FOO__CONSTANT_4 );
	}

	/**
	 * @test
	 * it should return false if volatile constant is not set
	 */
	public function it_should_return_false_if_volatile_constant_is_not_set() {
		$sut = new Constants( true );

		$this->assertFalse( isset( $sut['bar'] ) );
	}

	/**
	 * @test
	 * it should return false if volatile constant false
	 */
	public function it_should_return_false_if_volatile_constant_false() {
		$sut                     = new Constants( true );
		$sut['__BAR_CONSTANT_1'] = false;

		$this->assertFalse( $sut['__BAR_CONSTANT_1'] );
	}

	/**
	 * @test
	 * it should return volatile constant value
	 */
	public function it_should_return_volatile_constant_value() {
		$sut                     = new Constants( true );
		$sut['__BAR_CONSTANT_2'] = 'some value';

		$this->assertEquals( 'some value', $sut['__BAR_CONSTANT_2'] );
	}

	/**
	 * @test
	 * it should not allow redefining volatile constant
	 */
	public function it_should_not_allow_redefining_volatile_constant() {
		$sut                     = new Constants( true );
		$sut['__BAR_CONSTANT_3'] = 'some value';
		$sut['__BAR_CONSTANT_3'] = 'another value';

		$this->assertEquals( 'some value', $sut['__BAR_CONSTANT_3'] );
	}

	/**
	 * @test
	 * it should not allow unsetting volatile constant
	 */
	public function it_should_not_allow_unsetting_volatile_constant() {
		$sut                     = new Constants( true );
		$sut['__BAR_CONSTANT_4'] = 'some value';
		unset( $sut['__BAR_CONSTANT_4'] );

		$this->assertEquals( 'some value', $sut['__BAR_CONSTANT_4'] );
	}
}