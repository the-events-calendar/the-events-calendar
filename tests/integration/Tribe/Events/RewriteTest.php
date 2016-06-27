<?php
namespace Tribe\Events;

use DeepCopy\Reflection\ReflectionHelper;
use Prophecy\Argument;
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
		$this->wp_rewrite->preg_index( Argument::any() )->will( function ( $args ) {
			return '$matches[' . $args[0] . ']';
		} );
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

	public function add_regex_and_args() {
		return [
			[ [ 'foo' ], [ 'bar' => '%2' ], 'foo/?$', 'index.php?bar=$matches[2]' ],
			[ [ 'foo', 'baz' ], [ 'bar' => '%2' ], 'foo/baz/?$', 'index.php?bar=$matches[2]' ],
			[ [ 'foo', 'baz', 'qux' ], [ 'bar' => '%2' ], 'foo/baz/qux/?$', 'index.php?bar=$matches[2]' ],
			[ [ '(match|me)' ], [ 'bar' => '%2' ], '(match|me)/?$', 'index.php?bar=$matches[2]' ],
			[ [ '(optional-match)*' ], [ 'bar' => '%2' ], '(optional-match)*/?$', 'index.php?bar=$matches[2]' ],
		];
	}

	/**
	 * single method will join regex components with slashes
	 *
	 * @dataProvider add_regex_and_args
	 */
	public function test_add_method_will_join_regex_components_with_slashes( $regex_parts, $args, $expected_regex, $expected_url ) {
		$sut = $this->make_instance();
		$sut->add( $regex_parts, $args );

		$this->assertArrayHasKey( $expected_regex, $sut->rules );
		$this->assertEquals( $expected_url, $sut->rules[ $expected_regex ] );
	}

	public function add_no_prefix_regex_and_args() {
		return [
			[ [ 'foo', Rewrite::$no_slash_regex_prefix . '(/(optional-uri-frag))*' ], [ 'bar' => '%2' ], 'foo(/(optional-uri-frag))*/?$', 'index.php?bar=$matches[2]' ],
		];
	}

	/**
	 * add method will allow to join pieces as they are using regex prefix
	 *
	 * @dataProvider add_no_prefix_regex_and_args
	 */
	public function test_add_method_will_allow_to_join_pieces_as_they_are_using_regex_prefix( $regex_parts, $args, $expected_regex, $expected_url ) {
		$sut = $this->make_instance();
		$sut->add( $regex_parts, $args );

		$this->assertArrayHasKey( $expected_regex, $sut->rules );
		$this->assertEquals( $expected_url, $sut->rules[ $expected_regex ] );
	}

	/**
	 * @return Rewrite
	 */
	private function make_instance() {
		$rewrite = new Rewrite( $this->wp_rewrite->reveal() );
		$rewrite->setup();

		return $rewrite;
	}
}
